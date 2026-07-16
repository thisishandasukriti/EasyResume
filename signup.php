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

session_start();

// Enforce HTTPS (disabled for localhost development)
$host = $_SERVER['HTTP_HOST'] ?? '';
if ($host !== 'localhost' && $host !== '127.0.0.1' && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on')) {
    header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
}

// CSRF token — generated once per session, checked before any DB work runs
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ---- Request size guard ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content_length = (int)($_SERVER['CONTENT_LENGTH'] ?? 0);
    if ($content_length > 8192) {
        http_response_code(413);
        die('Request too large.');
    }
}

$signup_error   = '';
$signup_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {

    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $signup_error = 'Your session expired. Please refresh the page and try again.';
    } elseif (empty($_POST['emailUpdates'])) {
        // Server-side enforcement to back up the client-side checkbox check
        $signup_error = 'You must agree to receive email updates before signing up.';
    } else {

        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // ---- Server-side validation ----
        if (strlen($username) < 3 || strlen($username) > 50) {
            $signup_error = 'Username must be between 3 and 50 characters.';
        } elseif (!preg_match('/^[A-Za-z0-9_.\- ]+$/', $username)) {
            $signup_error = 'Username contains invalid characters.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
            $signup_error = 'Please enter a valid email address.';
        } elseif (strlen($password) < 8 || strlen($password) > 128) {
            $signup_error = 'Password must be between 8 and 128 characters.';
        } else {

            // Set connection variables
            $server      = "localhost";
            $db_username = "root";
            $db_password = ""; // MySQL password
            $dbname      = "easy resume"; // database name

            $con = mysqli_connect($server, $db_username, $db_password, $dbname);

            if (!$con) {
                // Log the real reason internally — never show DB details to the visitor
                error_log("EasyResume signup DB connection failed: " . mysqli_connect_error());
                $signup_error = 'Something went wrong on our end. Please try again in a moment.';
            } else {

                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                // Check for duplicate email via prepared statement
                $stmt_check = $con->prepare("SELECT id FROM `userinfo` WHERE LOWER(`email`) = LOWER(?)");
                $stmt_check->bind_param("s", $email);
                $stmt_check->execute();
                $check_result = $stmt_check->get_result();

                if ($check_result->num_rows > 0) {
                    $signup_error = 'Email already exists. Please use a different email.';
                    $stmt_check->close();
                } else {
                    $stmt_check->close();

                    $stmt_insert = $con->prepare(
                        "INSERT INTO `userinfo` (`username`, `email`, `password`, `created_at`)
                         VALUES (?, ?, ?, CURRENT_TIMESTAMP)"
                    );
                    $stmt_insert->bind_param("sss", $username, $email, $hashed_password);

                    if ($stmt_insert->execute()) {
                        $signup_success = true;

                        // Auto-login: regenerate session ID on privilege change, log the new user in
                        $new_user_id = $stmt_insert->insert_id;
                        session_regenerate_id(true);
                        // Old CSRF token belonged to the pre-login session — issue a fresh one
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                        $_SESSION['user_id'] = $new_user_id;
                        $_SESSION['user'] = $username;
                        $_SESSION['last_activity'] = time();
                        $stmt_insert->close();
                        mysqli_close($con);
                        header("Location: template-selection.php");
                        exit();
                    } else {
                        // 1062 = duplicate key — the UNIQUE(email) constraint caught a race condition
                        if ($con->errno === 1062) {
                            $signup_error = 'Email already exists. Please use a different email.';
                        } else {
                            error_log("EasyResume signup insert failed: " . $con->error);
                            $signup_error = 'Something went wrong on our end. Please try again in a moment.';
                        }
                    }
                    $stmt_insert->close();
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
<title>Sign Up — EasyResume</title>
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
    --success:#2f9e63;
    --success-bg:rgba(47,158,99,0.08);
    --font-head:'Poppins',sans-serif;
    --font-body:'Roboto',sans-serif;
  }

  *{ box-sizing:border-box; }
  body{ margin:0; font-family:var(--font-body); color:var(--ink); background:var(--surface); -webkit-font-smoothing:antialiased; }
  a{ color:inherit; }
  img{ max-width:100%; display:block; }

  .auth-shell{ min-height:100vh; display:grid; grid-template-columns:1fr 1fr; }

  /* LEFT — BRAND PANEL */
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

  /* RIGHT — FORM PANEL */
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
  .field-hint{ font-size:0.75rem; color:var(--muted); margin-top:5px; }

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

  .checkbox-field{ display:flex; align-items:flex-start; gap:0.6rem; margin:1.1rem 0 0.4rem; }
  .checkbox-field input[type="checkbox"]{ width:18px; height:18px; margin-top:2px; accent-color:var(--accent); flex-shrink:0; cursor:pointer; }
  .checkbox-field label{ font-size:0.84rem; color:var(--muted); line-height:1.45; cursor:pointer; }

  .field-error{ color:var(--danger); font-size:0.78rem; margin:4px 0 0; display:none; }

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
    margin-top:1.1rem;
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
  .alert-success{ background:var(--success-bg); color:var(--success); }
  .alert-success a{ color:var(--success); font-weight:600; text-decoration:underline; }

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
  .field input:valid:not(:placeholder-shown):not([type="checkbox"]){ border-color:rgba(47,158,99,0.45); }
  .field input:valid:not(:placeholder-shown):not([type="checkbox"]):focus{ box-shadow:0 0 0 3px rgba(47,158,99,0.12); }

  /* Password strength meter */
  .pw-strength{ margin-top:8px; }
  .pw-strength-bar{
    height:4px;
    border-radius:999px;
    background:rgba(108,99,255,0.1);
    overflow:hidden;
  }
  .pw-strength-fill{
    height:100%;
    width:0%;
    border-radius:999px;
    transition:width .35s ease, background .35s ease;
  }
  .pw-strength-label{
    font-size:0.72rem;
    font-weight:600;
    margin-top:4px;
    transition:color .3s ease;
    color:var(--muted);
  }

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
      <h1>Build a resume that actually gets you noticed.</h1>
      <p>Create your free account and start with ATS-friendly templates, AI suggestions, and a builder that keeps everything saved as you go.</p>
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
      <h2>Create your account</h2>
      <p class="sub">It takes less than a minute.</p>

      <?php if ($signup_error): ?>
        <div class="alert alert-error">⚠️ <?php echo htmlspecialchars($signup_error); ?></div>
      <?php endif; ?>

      <?php if ($signup_success): ?>
        <div class="alert alert-success">✅ Sign up successful! You can now <a href="login.php">login</a>.</div>
      <?php endif; ?>

      <form action="signup.php" method="POST" id="signupForm">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

        <div class="field">
          <label for="username">Username</label>
          <div class="field-input-wrap">
            <input type="text" id="username" name="username" placeholder="Your username" autocomplete="username" minlength="3" maxlength="50" required>
          </div>
        </div>

        <div class="field">
          <label for="email">Email</label>
          <div class="field-input-wrap">
            <input type="email" id="email" name="email" placeholder="you@example.com" autocomplete="email" maxlength="254" required>
          </div>
        </div>

        <div class="field">
          <label for="password">Password</label>
          <div class="field-input-wrap has-toggle">
            <input type="password" id="password" name="password" placeholder="At least 8 characters" autocomplete="new-password" minlength="8" maxlength="128" required>
            <button type="button" class="toggle-visibility" data-target="password" aria-label="Show password">👁</button>
          </div>
          <p class="field-hint">Use 8 or more characters.</p>
          <div class="pw-strength" id="pwStrength">
            <div class="pw-strength-bar"><div class="pw-strength-fill" id="pwStrengthFill"></div></div>
            <div class="pw-strength-label" id="pwStrengthLabel"></div>
          </div>
        </div>

        <div class="checkbox-field">
          <input type="checkbox" id="emailUpdates" name="emailUpdates" required>
          <label for="emailUpdates">I agree to receive recent updates on my email</label>
        </div>
        <p class="field-error" id="checkboxError">You must agree to receive email updates before signing up.</p>

        <button type="submit" class="btn-primary" id="signupBtn"><span class="btn-label">Sign Up</span><span class="btn-spinner" style="display:none"></span></button>
      </form>

      <p class="auth-switch">Already have an account? <a href="login.php">Login</a></p>
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

document.getElementById('signupForm').addEventListener('submit', function (e) {
  const checkbox = document.getElementById('emailUpdates');
  const errorMessage = document.getElementById('checkboxError');

  if (!checkbox.checked) {
    errorMessage.style.display = 'block';
    e.preventDefault();
  } else {
    errorMessage.style.display = 'none';
    /* Submit loading state */
    const btn = document.getElementById('signupBtn');
    btn.classList.add('is-loading');
    btn.querySelector('.btn-label').textContent = 'Creating account…';
    btn.querySelector('.btn-spinner').style.display = '';
  }
});

/* Password strength meter */
(function() {
  const pw = document.getElementById('password');
  const fill = document.getElementById('pwStrengthFill');
  const label = document.getElementById('pwStrengthLabel');
  if (!pw || !fill || !label) return;

  pw.addEventListener('input', function() {
    const v = pw.value;
    let score = 0;
    if (v.length >= 8) score++;
    if (v.length >= 12) score++;
    if (/[A-Z]/.test(v)) score++;
    if (/[0-9]/.test(v)) score++;
    if (/[^A-Za-z0-9]/.test(v)) score++;

    const levels = [
      { pct: '0%', color: 'transparent', text: '' },
      { pct: '20%', color: '#d6336c', text: 'Weak' },
      { pct: '40%', color: '#e8590c', text: 'Fair' },
      { pct: '60%', color: '#f59f00', text: 'Good' },
      { pct: '80%', color: '#51cf66', text: 'Strong' },
      { pct: '100%', color: '#2f9e63', text: 'Very strong' }
    ];
    const lvl = v.length === 0 ? levels[0] : levels[Math.min(score, 5)];
    fill.style.width = lvl.pct;
    fill.style.background = lvl.color;
    label.textContent = lvl.text;
    label.style.color = lvl.color;
  });
})();
</script>

</body>
</html>