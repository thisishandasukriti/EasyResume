<?php
require_once __DIR__ . '/includes/auth.php';

$templates = require __DIR__ . '/templates_registry.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Select Template — EasyResume</title>
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
    --font-head:'Poppins',sans-serif;
    --font-body:'Roboto',sans-serif;
  }

  *{ box-sizing:border-box; }
  body{ margin:0; font-family:var(--font-body); color:var(--ink); background:var(--surface-soft); -webkit-font-smoothing:antialiased; }
  a{ color:inherit; }
  img{ max-width:100%; display:block; }

  .container{ max-width:1200px; margin:0 auto; padding:0 24px; }

  /* NAVBAR */
  .navbar{ background:var(--surface); padding:18px 0; box-shadow:var(--shadow-sm); position:relative; z-index:2; }
  .navbar .container{ display:flex; justify-content:space-between; align-items:center; }
  .brand-logo{ display:flex; align-items:center; gap:10px; font-family:var(--font-head); font-weight:700; font-size:1.25rem; text-decoration:none; color:var(--ink); }
  .brand-logo img{ width:34px; height:34px; }
  .brand-logo span{ color:var(--accent); }

  .logout-btn{
    text-decoration:none;
    font-family:var(--font-head);
    font-weight:600;
    font-size:0.86rem;
    color:#fff;
    background:var(--gradient);
    padding:10px 22px;
    border-radius:999px;
    box-shadow:var(--shadow-sm);
    transition:transform .18s ease, box-shadow .18s ease;
  }
  .logout-btn:hover{ transform:translateY(-2px); box-shadow:var(--shadow-md); }

  /* HEADER */
  .header{
    position:relative;
    overflow:hidden;
    background:var(--gradient);
    color:#fff;
    padding:70px 20px 80px;
    text-align:center;
  }
  .header-blob{ position:absolute; border-radius:50%; filter:blur(60px); }
  .blob-1{ width:340px; height:340px; background:rgba(255,255,255,0.16); top:-140px; left:-100px; }
  .blob-2{ width:300px; height:300px; background:rgba(255,255,255,0.12); bottom:-140px; right:-80px; }
  .header h1{ position:relative; z-index:1; font-family:var(--font-head); font-weight:700; font-size:clamp(1.9rem, 4vw, 2.6rem); margin:0; }
  .header p{ position:relative; z-index:1; font-size:1rem; color:rgba(255,255,255,0.85); margin:12px 0 0; }
  .header .welcome{
    position:relative; z-index:1;
    display:inline-block;
    background:rgba(255,255,255,0.15);
    padding:4px 12px;
    border-radius:999px;
    font-size:0.85rem;
    font-weight:600;
    margin-bottom:12px;
  }

  /* AI TOOLS BANNER */
  .ai-banner{
    max-width:900px;
    margin:-40px auto 50px;
    padding:28px 24px;
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:16px;
    text-align:center;
    box-shadow:var(--shadow-md);
    position:relative;
    z-index:1;
  }
  .ai-banner .ai-tag{
    color:var(--accent-2);
    font-family:var(--font-head);
    font-weight:700;
    font-size:1.02rem;
    display:block;
    margin-bottom:6px;
  }
  .ai-banner .ai-copy{ color:var(--muted); font-size:0.92rem; line-height:1.6; }
  .ai-banner .btn-ai{
    display:inline-block;
    margin-top:18px;
    background:var(--gradient);
    color:#fff;
    padding:11px 26px;
    font-family:var(--font-head);
    font-size:0.84rem;
    font-weight:600;
    border-radius:999px;
    text-decoration:none;
    text-transform:uppercase;
    letter-spacing:0.02em;
    box-shadow:var(--shadow-sm);
    transition:transform .18s ease, box-shadow .18s ease;
  }
  .ai-banner .btn-ai:hover{ transform:translateY(-2px); box-shadow:var(--shadow-md); }

  /* TEMPLATE CARDS */
  .template-cards{
    display:flex;
    justify-content:center;
    flex-wrap:wrap;
    gap:28px;
    padding:10px 20px 70px;
  }
  .template-card{
    background:var(--surface);
    border:1px solid var(--border);
    box-shadow:var(--shadow-sm);
    border-radius:16px;
    padding:26px;
    width:250px;
    text-align:center;
    transition:transform .25s ease, box-shadow .25s ease, border-color .25s ease;
    animation:cardFadeInUp .6s ease both;
  }
  .template-card:hover{
    transform:translateY(-6px);
    box-shadow:var(--shadow-lg), 0 0 0 4px rgba(108,99,255,0.1);
    border-color:var(--accent);
  }

  @keyframes cardFadeInUp{
    from{ opacity:0; transform:translateY(24px); }
    to{ opacity:1; transform:translateY(0); }
  }
  .template-card:nth-child(1){ animation-delay:0.05s; }
  .template-card:nth-child(2){ animation-delay:0.12s; }
  .template-card:nth-child(3){ animation-delay:0.19s; }
  .template-card:nth-child(4){ animation-delay:0.26s; }
  .template-card:nth-child(5){ animation-delay:0.33s; }
  .template-card:nth-child(6){ animation-delay:0.40s; }
  .template-card img{ border-radius:10px; border:1px solid var(--border); }
  .template-card h3{ font-family:var(--font-head); font-weight:600; font-size:1.15rem; margin:16px 0 8px; color:var(--ink); }
  .template-card p{ font-size:0.88rem; color:var(--muted); line-height:1.6; margin:0; min-height:66px; }

  .btn-select{
    display:inline-block;
    margin-top:18px;
    background:var(--gradient);
    color:#fff;
    padding:10px 22px;
    font-family:var(--font-head);
    font-size:0.78rem;
    font-weight:600;
    border-radius:999px;
    text-transform:uppercase;
    letter-spacing:0.02em;
    text-decoration:none;
    box-shadow:var(--shadow-sm);
    transition:transform .18s ease, box-shadow .18s ease;
  }
  .btn-select:hover{ transform:translateY(-2px); box-shadow:var(--shadow-md); }

  /* NEW: live preview iframe (added — reuses existing radius/border/shadow tokens) */
  .tmpl-preview-frame{
    position:relative;
    width:100%;
    aspect-ratio:1 / 1.414; /* A4 ratio */
    border-radius:10px;
    border:1px solid var(--border);
    overflow:hidden;
    background:var(--surface-soft);
  }
  .tmpl-preview-frame iframe{
    position:absolute;
    top:0; left:0;
    width:283%;            /* 1 / 0.3536 — inverse of the 0.3536 scale below */
    height:283%;
    transform:scale(0.3536);
    transform-origin:top left;
    border:none;
    pointer-events:none;   /* card click shouldn't scroll/interact with the embedded preview */
  }

  /* NEW: template metadata row (category / fonts) */
  .tmpl-meta{
    display:flex;
    justify-content:center;
    gap:6px;
    flex-wrap:wrap;
    margin-top:10px;
  }
  .tmpl-meta span{
    font-family:var(--font-head);
    font-size:0.68rem;
    font-weight:600;
    letter-spacing:0.02em;
    text-transform:uppercase;
    color:var(--accent-2);
    background:var(--surface-tint);
    border:1px solid var(--border);
    padding:3px 10px;
    border-radius:999px;
  }

  /* NEW: button row (Preview + Use Template) */
  .tmpl-btn-row{
    display:flex;
    gap:10px;
    margin-top:18px;
  }
  .tmpl-btn-row .btn-select{ margin-top:0; flex:1; }
  .btn-preview{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    flex:1;
    background:transparent;
    color:var(--accent-2);
    border:1px solid var(--border-strong);
    padding:10px 16px;
    font-family:var(--font-head);
    font-size:0.78rem;
    font-weight:600;
    border-radius:999px;
    text-transform:uppercase;
    letter-spacing:0.02em;
    text-decoration:none;
    transition:background .18s ease, transform .18s ease;
  }
  .btn-preview:hover{ background:rgba(108,99,255,0.08); transform:translateY(-2px); }

  /* FOOTER */
  .footer{ background:var(--ink); color:rgba(255,255,255,0.8); padding:22px 0; text-align:center; }
  .footer p{ font-size:0.82rem; margin:0; }
  .footer span{ font-weight:700; color:#fff; }

  @media (max-width:768px){
    .template-cards{ flex-direction:column; align-items:center; }
    .template-card{ width:90%; max-width:320px; }
    .ai-banner{ margin:-24px 16px 40px; }
  }
</style>
</head>
<body>

<nav class="navbar">
  <div class="container">
    <a href="home.php" class="brand-logo">
      <img src="logo.png" alt="EasyResume logo" onerror="this.style.display='none'">
      Easy<span>Resume</span>
    </a>
    <a href="logout.php" class="logout-btn">Logout</a>
  </div>
</nav>

<div class="header">
  <div class="header-blob blob-1"></div>
  <div class="header-blob blob-2"></div>
  <div class="container">
    <?php if (!empty($_SESSION['full_name'])): ?>
      <div class="welcome">Welcome back, <?php echo htmlspecialchars(explode(' ', trim($_SESSION['full_name']))[0]); ?>! 👋</div>
    <?php elseif (!empty($_SESSION['user_name'])): ?>
      <div class="welcome">Welcome back, <?php echo htmlspecialchars(explode(' ', trim($_SESSION['user_name']))[0]); ?>! 👋</div>
    <?php else: ?>
      <div class="welcome">Welcome back! 👋</div>
    <?php endif; ?>
    <h1>Choose Your Resume Template</h1>
    <p>Select a professionally designed template to get started. You can change it anytime.</p>
  </div>
</div>

<div class="ai-banner">
  <span class="ai-tag">✦ New AI Features</span>
  <span class="ai-copy">Generate ATS-friendly resumes, analyze job matches, improve content with AI, and create cover letters.</span>
  <div>
    <a href="ai_tools.php" class="btn-ai">✦ Open AI Tools Dashboard</a>
  </div>
</div>

<div class="template-cards">
<?php foreach ($templates as $slug => $template): ?>
  <?php
    $slugEsc        = urlencode($slug);
    $name           = htmlspecialchars($template['name'] ?? ucwords(str_replace('-', ' ', $slug)));
    $description    = htmlspecialchars($template['description'] ?? '');
    $category       = htmlspecialchars($template['category'] ?? '');
    $fonts          = $template['fonts'] ?? [];
    if (is_array($fonts)) {
        $fontsLabel = htmlspecialchars(implode(' / ', $fonts));
    } else {
        $fontsLabel = htmlspecialchars((string)$fonts);
    }
  ?>
  <div class="template-card">
    <div class="tmpl-preview-frame">
      <iframe
        src="template-preview.php?template=<?php echo $slugEsc; ?>&mode=sample"
        title="<?php echo $name; ?> preview"
        loading="lazy"
        scrolling="no"
        tabindex="-1"
      ></iframe>
    </div>

    <h3><?php echo $name; ?></h3>
    <p><?php echo $description; ?></p>

    <?php if ($category || $fontsLabel): ?>
      <div class="tmpl-meta">
        <?php if ($category): ?><span><?php echo $category; ?></span><?php endif; ?>
        <?php if ($fontsLabel): ?><span><?php echo $fontsLabel; ?></span><?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="tmpl-btn-row">
      <a href="template-preview.php?template=<?php echo $slugEsc; ?>&mode=sample&toolbar=1"
         class="btn-preview"
         target="_blank"
         rel="noopener">Preview</a>
      <a href="resume-builder.php?template=<?php echo $slugEsc; ?>"
         class="btn-select">Use Template</a>
    </div>
  </div>
<?php endforeach; ?>
</div>

<footer class="footer">
  <p>&copy; Copyright 2026. All Rights Reserved - <span>EasyResume</span></p>
</footer>

</body>
</html>