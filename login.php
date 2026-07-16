<?php
// Secure cookie flags MUST be set before session_start()
$is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'secure'   => $is_https,   // only flagged secure on real HTTPS so localhost dev still works
    'samesite' => 'Lax'
]);

// Start session to handle user sessions after successful login
session_start();

// Enforce HTTPS (disabled for localhost development)
$host = $_SERVER['HTTP_HOST'] ?? '';
if ($host !== 'localhost' && $host !== '127.0.0.1' && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on')) {
    header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
}

// ---- Session timeout: 30 minutes of inactivity ----
$timeout_seconds = 1800;
if (!empty($_SESSION['user_id']) && !empty($_SESSION['last_activity']) &&
    (time() - $_SESSION['last_activity']) > $timeout_seconds) {
    $_SESSION = [];
    session_destroy();
    session_start(); // fresh session so a new CSRF token / form can be issued
}
$_SESSION['last_activity'] = time();

// CSRF token — generated once per session, checked before any DB work runs
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ---- Request size guard (cheap defense against giant/garbage submissions) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content_length = (int)($_SERVER['CONTENT_LENGTH'] ?? 0);
    if ($content_length > 8192) { // a login form has no business being bigger than 8KB
        http_response_code(413);
        die('Request too large.');
    }
}

$login_error = '';

// ---- Login rate limiting config ----
const MAX_LOGIN_ATTEMPTS = 5;
const LOCKOUT_SECONDS     = 900; // 15 minutes

$server      = "localhost";
$db_username = "root";
$db_password = "";
$dbname      = "easy resume";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['password'])) {

    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $login_error = 'Your session expired. Please refresh the page and try again.';
    } else {

        $email            = trim($_POST['email']);
        $entered_password = $_POST['password'];

        if (strlen($email) > 254 || strlen($entered_password) > 1024) {
            $login_error = 'Invalid login credentials.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $login_error = 'Invalid email format.';
        } else {

            $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

            $con = mysqli_connect($server, $db_username, $db_password, $dbname);

            if (!$con) {
                error_log("EasyResume login DB connection failed: " . mysqli_connect_error());
                $login_error = 'Something went wrong on our end. Please try again in a moment.';
            } else {

                // ---- Read existing rate-limit record for this email + IP ----
                $attempt_count = 0;
                $locked_until  = null;

                $stmt_attempt = $con->prepare(
                    "SELECT attempt_count, locked_until
                     FROM login_attempts
                     WHERE email = ? AND ip_address = ?"
                );
                $stmt_attempt->bind_param("ss", $email, $ip_address);
                $stmt_attempt->execute();
                $result_attempt = $stmt_attempt->get_result();

                if ($result_attempt->num_rows > 0) {
                    $attempt_data  = $result_attempt->fetch_assoc();
                    $attempt_count = (int)$attempt_data['attempt_count'];
                    $locked_until  = $attempt_data['locked_until'];
                }
                $stmt_attempt->close();

                // Lockout window has expired -> treat as a clean slate
                if ($locked_until && strtotime($locked_until) <= time()) {
                    $attempt_count = 0;
                    $locked_until  = null;
                }

                if ($locked_until && strtotime($locked_until) > time()) {

                    $wait_minutes = (int)ceil((strtotime($locked_until) - time()) / 60);
                    $login_error  = "Too many failed attempts. Please try again in {$wait_minutes} minute(s).";

                } else {

                    // ---- Verify credentials ----
                    $dummy_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

                    $stmt = $con->prepare("SELECT id, username, password FROM `userinfo` WHERE LOWER(`email`) = LOWER(?)");
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    $login_ok = false;
                    $user     = null;

                    $row            = $result->fetch_assoc(); // always fetch to prevent timing side-channel
                    $hash_to_verify = $row ? $row['password'] : $dummy_hash;

                    if (password_verify($entered_password, $hash_to_verify) && $row !== null) {
                        $login_ok = true;
                        $user     = $row;
                    }

                    $stmt->close();

                    if ($login_ok) {
                        // Clear rate-limit record on success
                        $stmt_clear = $con->prepare(
                            "DELETE FROM login_attempts WHERE email = ? AND ip_address = ?"
                        );
                        $stmt_clear->bind_param("ss", $email, $ip_address);
                        $stmt_clear->execute();
                        $stmt_clear->close();

                        session_regenerate_id(true);
                        $_SESSION['csrf_token']    = bin2hex(random_bytes(32));
                        $_SESSION['user_id']       = $user['id'];
                        $_SESSION['user']          = $user['username'];
                        $_SESSION['last_activity'] = time();
                        mysqli_close($con);
                        header("Location: template-selection.php");
                        exit();

                    } else {
                        $attempt_count++;

                        if ($attempt_count >= MAX_LOGIN_ATTEMPTS) {
                            $locked_until = date('Y-m-d H:i:s', time() + LOCKOUT_SECONDS);
                            $login_error  = 'Too many failed attempts. Please try again in 15 minutes.';
                        } else {
                            $locked_until = null;
                            $login_error  = 'Invalid login credentials.';
                        }

                        $stmt_save = $con->prepare(
                            "INSERT INTO login_attempts (email, ip_address, attempt_count, locked_until)
                             VALUES (?, ?, ?, ?)
                             ON DUPLICATE KEY UPDATE
                                attempt_count = VALUES(attempt_count),
                                locked_until  = VALUES(locked_until)"
                        );
                        $stmt_save->bind_param("ssis", $email, $ip_address, $attempt_count, $locked_until);
                        $stmt_save->execute();
                        $stmt_save->close();
                    }
                }

                mysqli_close($con);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — EasyResume</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root{
    --ink:#2c2a3a;
    --muted:#6f6c85;
    --accent:#6c63ff;
    --accent-2:#3a1c8e;
    --gradient: linear-gradient(135deg, #6c63ff, #3a1c8e);
    --surface:#ffffff;
    --surface-soft:#f9f9fb;
    --surface-tint:#f1f0fb;
    --border: rgba(108,99,255,0.16);
    --border-strong: rgba(108,99,255,0.32);
    --shadow-sm: 0 2px 10px rgba(60,40,130,0.07);
    --shadow-md: 0 10px 28px rgba(60,40,130,0.12);
    --shadow-lg: 0 22px 48px rgba(60,40,130,0.18);
    --danger:#d6336c;
    --danger-bg:rgba(214,51,108,0.08);
    --font-head:'Poppins',sans-serif;
    --font-body:'Roboto',sans-serif;
  }

  *{ box-sizing:border-box; }
  body{ margin:0; font-family:var(--font-body); color:var(--ink); background:var(--surface); -webkit-font-smoothing:antialiased; }
  a{ color:inherit; }
  img{ max-width:100%; display:block; }

  .auth-shell{ min-height:100vh; display:grid; grid-template-columns:1fr 1fr; }

  .auth-brand{
    position:relative;
    overflow:hidden;
    background:var(--gradient);
    color:#fff;
    display:flex;
    flex-direction:column;
    justify-content:space-between;
    padding:2.5rem 3rem;
  }
  .auth-blob{ position:absolute; border-radius:50%; filter:blur(60px); }
  .blob-1{ width:340px; height:340px; background:rgba(255,255,255,0.18); top:-120px; left:-100px; }
  .blob-2{ width:300px; height:300px; background:rgba(255,255,255,0.12); bottom:-100px; right:-80px; }

  .auth-brand-top, .auth-brand-bottom{ position:relative; z-index:1; }
  .brand-logo{ display:flex; align-items:center; gap:8px; font-family:var(--font-head); font-weight:700; font-size:1.15rem; text-decoration:none; color:#fff; }
  .brand-logo img{ width:32px; height:32px; }

  .auth-brand-mid{ position:relative; z-index:1; max-width:380px; }
  .auth-brand-mid h1{ font-family:var(--font-head); font-weight:700; font-size:clamp(1.7rem, 3vw, 2.3rem); line-height:1.25; margin:0 0 0.9rem; }
  .auth-brand-mid p{ font-size:0.95rem; line-height:1.6; color:rgba(255,255,255,0.85); margin:0; }

  .brand-points{ list-style:none; padding:0; margin:1.75rem 0 0; display:flex; flex-direction:column; gap:0.6rem; }
  .brand-points li{ display:flex; align-items:center; gap:0.6rem; font-size:0.88rem; color:rgba(255,255,255,0.92); }
  .brand-points li .pt-dot{ width:18px; height:18px; border-radius:50%; background:rgba(255,255,255,0.2); display:flex; align-items:center; justify-content:center; font-size:0.65rem; flex-shrink:0; }

  .auth-brand-bottom{ font-size:0.78rem; color:rgba(255,255,255,0.7); }

  .auth-form-side{ display:flex; align-items:center; justify-content:center; padding:2.5rem; background:var(--surface-soft); }
  .auth-card{
    width:100%;
    max-width:400px;
  }
  .auth-card h2{ font-family:var(--font-head); font-weight:700; font-size:1.6rem; margin:0 0 0.4rem; }
  .auth-card .sub{ color:var(--muted); font-size:0.9rem; margin:0 0 1.75rem; }

  .field{ margin-bottom:1.1rem; }
  .field label{ display:block; font-family:var(--font-head); font-size:0.8rem; font-weight:500; color:var(--ink); margin-bottom:6px; }
  .field-input-wrap{ position:relative; }
  .field input{
    width:100%;
    font-family:var(--font-body);
    font-size:0.92rem;
    color:var(--ink);
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:10px;
    padding:12px 14px;
    transition:border-color .2s ease, box-shadow .2s ease;
  }
  .field input:focus{ outline:none; border-color:var(--accent); box-shadow:0 0 0 3px rgba(108,99,255,0.15); }
  .field-input-wrap.has-toggle input{ padding-right:42px; }

  .toggle-visibility{
    position:absolute;
    right:10px;
    top:50%;
    transform:translateY(-50%);
    background:none;
    border:none;
    cursor:pointer;
    color:var(--muted);
    font-size:0.95rem;
    padding:4px;
    line-height:1;
  }
  .toggle-visibility:hover{ color:var(--accent-2); }

  .btn-primary{
    width:100%;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    font-family:var(--font-head);
    font-weight:600;
    font-size:0.94rem;
    color:#fff;
    background:var(--gradient);
    border:none;
    border-radius:999px;
    padding:13px 0;
    cursor:pointer;
    box-shadow:var(--shadow-sm);
    transition:transform .18s ease, box-shadow .18s ease;
    margin-top:0.4rem;
  }
  .btn-primary:hover{ transform:translateY(-2px); box-shadow:var(--shadow-md); }

  .alert{
    display:flex;
    align-items:flex-start;
    gap:0.5rem;
    border-radius:10px;
    padding:0.7rem 0.9rem;
    font-size:0.85rem;
    line-height:1.5;
    margin-bottom:1.1rem;
  }
  .alert-error{ background:var(--danger-bg); color:var(--danger); }

  .auth-switch{ text-align:center; font-size:0.88rem; color:var(--muted); margin-top:1.5rem; }
  .auth-switch a{ color:var(--accent-2); font-weight:600; text-decoration:none; }
  .auth-switch a:hover{ text-decoration:underline; }

  .secure-note{ display:flex; align-items:center; gap:0.4rem; justify-content:center; margin-top:1.25rem; font-size:0.76rem; color:var(--muted); }

  /* ── Entrance animations ── */
  @keyframes authSlideInLeft{
    from{ opacity:0; transform:translateX(-40px); }
    to{ opacity:1; transform:translateX(0); }
  }
  @keyframes authSlideInRight{
    from{ opacity:0; transform:translateX(40px); }
    to{ opacity:1; transform:translateX(0); }
  }
  @keyframes authFadeUp{
    from{ opacity:0; transform:translateY(18px); }
    to{ opacity:1; transform:translateY(0); }
  }
  @keyframes alertShake{
    0%,100%{ transform:translateX(0); }
    15%{ transform:translateX(-6px); }
    30%{ transform:translateX(5px); }
    45%{ transform:translateX(-4px); }
    60%{ transform:translateX(3px); }
    75%{ transform:translateX(-2px); }
  }
  @keyframes btnSpin{
    to{ transform:rotate(360deg); }
  }

  .auth-brand{
    animation:authSlideInLeft .7s cubic-bezier(.16,1,.3,1) both;
  }
  .auth-form-side{
    animation:authSlideInRight .7s cubic-bezier(.16,1,.3,1) .12s both;
  }
  .auth-card h2{ animation:authFadeUp .5s ease .25s both; }
  .auth-card .sub{ animation:authFadeUp .5s ease .35s both; }
  .auth-card .field:nth-child(1){ animation:authFadeUp .5s ease .4s both; }
  .auth-card .field:nth-child(2){ animation:authFadeUp .5s ease .48s both; }
  .auth-card .btn-primary{ animation:authFadeUp .5s ease .55s both; }
  .auth-card .auth-switch{ animation:authFadeUp .5s ease .6s both; }

  /* Error shake */
  .alert-error{ animation:alertShake .5s ease, authFadeUp .4s ease both; }

  /* Submit loading state */
  .btn-primary.is-loading{
    pointer-events:none;
    opacity:0.8;
    gap:8px;
  }
  .btn-primary.is-loading .btn-spinner{
    display:inline-block;
    width:16px; height:16px;
    border:2px solid rgba(255,255,255,0.35);
    border-top-color:#fff;
    border-radius:50%;
    animation:btnSpin .6s linear infinite;
  }

  /* Input valid state */
  .field input:valid:not(:placeholder-shown){ border-color:rgba(47,158,99,0.45); }
  .field input:valid:not(:placeholder-shown):focus{ box-shadow:0 0 0 3px rgba(47,158,99,0.12); }

  @media (max-width:880px){
    .auth-shell{ grid-template-columns:1fr; }
    .auth-brand{ display:none; }
    .auth-form-side{ padding:2rem 1.25rem; min-height:100vh; animation-name:authFadeUp; }
  }

  @media (prefers-reduced-motion: reduce){
    *, *::before, *::after{
      animation-duration:0.01ms !important;
      transition-duration:0.01ms !important;
    }
  }
</style>
</head>
<body>

<div class="auth-shell">

  <div class="auth-brand">
    <div class="auth-blob blob-1"></div>
    <div class="auth-blob blob-2"></div>

    <div class="auth-brand-top">
      <a href="home.php" class="brand-logo">
        <img src="logo.png" alt="EasyResume logo" onerror="this.style.display='none'">
        EasyResume
      </a>
    </div>

    <div class="auth-brand-mid">
      <h1>Welcome back. Let's get you to your resume.</h1>
      <p>Pick up right where you left off — your templates, your details, your progress, all saved.</p>
      <ul class="brand-points">
        <li><span class="pt-dot">✓</span> Free, no hidden fees</li>
        <li><span class="pt-dot">✓</span> ATS-optimized templates</li>
        <li><span class="pt-dot">✓</span> Access from any device</li>
      </ul>
    </div>

    <div class="auth-brand-bottom">&copy; <?php echo date('Y'); ?> EasyResume. All rights reserved.</div>
  </div>

  <div class="auth-form-side">
    <div class="auth-card">
      <h2>Login to EasyResume</h2>
      <p class="sub">Enter your details to access your account.</p>

      <?php if ($login_error): ?>
        <div class="alert alert-error">⚠️ <?php echo htmlspecialchars($login_error); ?></div>
      <?php endif; ?>

      <form action="login.php" method="POST" id="loginForm">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

        <div class="field">
          <label for="email">Email</label>
          <div class="field-input-wrap">
            <input type="email" id="email" name="email" placeholder="you@example.com" autocomplete="email" required>
          </div>
        </div>

        <div class="field">
          <label for="password">Password</label>
          <div class="field-input-wrap has-toggle">
            <input type="password" id="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
            <button type="button" class="toggle-visibility" data-target="password" aria-label="Show password">👁</button>
          </div>
        </div>

        <button type="submit" class="btn-primary" id="loginBtn"><span class="btn-label">Login</span><span class="btn-spinner" style="display:none"></span></button>
      </form>

      <p class="auth-switch">Don't have an account? <a href="signup.php">Sign up</a></p>
      <div class="secure-note">🔒 Your connection to this page is secured</div>
    </div>
  </div>

</div>

<script>
document.querySelectorAll('.toggle-visibility').forEach(btn => {
  btn.addEventListener('click', () => {
    const input = document.getElementById(btn.dataset.target);
    const isPw = input.type === 'password';
    input.type = isPw ? 'text' : 'password';
    btn.textContent = isPw ? '🙈' : '👁';
    btn.setAttribute('aria-label', isPw ? 'Hide password' : 'Show password');
  });
});

/* Submit loading state */
document.getElementById('loginForm').addEventListener('submit', function() {
  const btn = document.getElementById('loginBtn');
  btn.classList.add('is-loading');
  btn.querySelector('.btn-label').textContent = 'Signing in…';
  btn.querySelector('.btn-spinner').style.display = '';
});
</script>

</body>
</html>