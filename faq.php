<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FAQ — EasyResume</title>
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

    /* ---------------------------------------------------------------
       PURPLE WASH — turn this single value up or down (0 to ~1.4)
       to control how visible/intense the fading purple gradient
       behind the page feels. 0 = invisible, 1 = bold as written here,
       1.4ish = quite saturated. Everything below reads from it.
       --------------------------------------------------------------- */
    --purple-intensity: 0.85;
  }

  *{ box-sizing:border-box; }

  body{
    margin:0;
    font-family:var(--font-body);
    color:var(--ink);
    background:var(--surface-soft);
    -webkit-font-smoothing:antialiased;
    position:relative;
  }

  a{ color:inherit; }
  img{ max-width:100%; display:block; }
  .container{ max-width:880px; margin:0 auto; padding:0 24px; }

  /* Fading purple wash — sits behind nav + hero + bleeds into content */
  .purple-wash{
    position:absolute;
    top:0; left:0; right:0;
    height:1100px;
    background:linear-gradient(180deg,
      rgba(108,99,255,0.4) 0%,
      rgba(108,99,255,0.16) 38%,
      rgba(108,99,255,0.04) 70%,
      rgba(108,99,255,0) 100%);
    opacity:var(--purple-intensity);
    pointer-events:none;
    z-index:0;
  }

  /* NAVBAR */
  .nav{
    position:sticky;
    top:0;
    z-index:50;
    background:rgba(255,255,255,0.78);
    backdrop-filter:blur(16px);
    -webkit-backdrop-filter:blur(16px);
    border-bottom:1px solid var(--border);
  }
  .nav-inner{
    max-width:1180px;
    margin:0 auto;
    padding:16px 28px;
    display:flex;
    align-items:center;
    gap:2rem;
  }
  .brand{
    font-family:var(--font-head);
    font-weight:700;
    font-size:1.15rem;
    text-decoration:none;
    color:var(--ink);
    display:flex;
    align-items:center;
    gap:8px;
  }
  .brand img{ width:32px; height:32px; }
  .brand .accent-text{
    background:var(--gradient);
    -webkit-background-clip:text;
    background-clip:text;
    color:transparent;
  }
  .nav-links{
    display:flex;
    align-items:center;
    gap:1.75rem;
    margin-left:0.5rem;
    flex:1;
  }
  .nav-links a{
    text-decoration:none;
    font-family:var(--font-head);
    font-size:0.88rem;
    font-weight:500;
    color:var(--muted);
    padding-bottom:4px;
    border-bottom:2px solid transparent;
    transition:color .2s ease, border-color .2s ease;
  }
  .nav-links a:hover, .nav-links a.active{
    color:var(--accent-2);
    border-bottom-color:var(--accent);
  }
  .nav-links a.nav-ai{ color:var(--accent-2); font-weight:700; }
  .nav-auth{ display:flex; align-items:center; gap:0.6rem; }

  @media (max-width:860px){
    .nav-links{ display:none; }
    .nav-inner{ justify-content:space-between; }
  }

  .btn{
    display:inline-flex;
    align-items:center;
    gap:0.4rem;
    font-family:var(--font-head);
    font-weight:600;
    font-size:0.9rem;
    text-decoration:none;
    padding:12px 26px;
    border-radius:999px;
    border:none;
    cursor:pointer;
    transition:transform .18s ease, box-shadow .18s ease, background .18s ease, color .18s ease;
    white-space:nowrap;
  }
  .btn-primary{ background:var(--gradient); color:#fff; box-shadow:var(--shadow-sm); }
  .btn-primary:hover{ transform:translateY(-2px); box-shadow:var(--shadow-md); }
  .btn-ghost{ background:transparent; color:var(--accent-2); border:1px solid var(--border-strong); }
  .btn-ghost:hover{ background:rgba(108,99,255,0.08); }
  .btn-sm{ padding:9px 18px; font-size:0.8rem; }

  /* HERO */
  .faq-header{
    position:relative;
    z-index:1;
    text-align:center;
    padding:90px 24px 50px;
  }
  .faq-header .eyebrow{
    display:inline-block;
    font-family:var(--font-head);
    font-size:0.74rem;
    font-weight:700;
    letter-spacing:0.1em;
    text-transform:uppercase;
    color:var(--accent-2);
    background:rgba(108,99,255,0.14);
    padding:6px 16px;
    border-radius:999px;
    margin-bottom:1.25rem;
  }
  .faq-header h1{
    font-family:var(--font-head);
    font-weight:700;
    font-size:clamp(2rem, 4.5vw, 2.9rem);
    margin:0 0 0.9rem;
  }
  .faq-header p{
    font-size:1.02rem;
    line-height:1.6;
    color:var(--muted);
    max-width:560px;
    margin:0 auto;
  }

  /* FAQ LIST (accordion) */
  .faq-content{
    position:relative;
    z-index:1;
    padding:30px 24px 100px;
  }
  .faq-list{
    display:flex;
    flex-direction:column;
    gap:0.9rem;
  }
  .faq-item{
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:16px;
    box-shadow:var(--shadow-sm);
    overflow:hidden;
    transition:box-shadow .25s ease, border-color .25s ease, opacity .6s ease, transform .6s ease;
    opacity:0;
    transform:translateY(18px);
  }
  .faq-item.in{
    opacity:1;
    transform:translateY(0);
  }
  .faq-item.is-open{
    border-color:var(--border-strong);
    box-shadow:var(--shadow-md);
  }
  .faq-q{
    width:100%;
    display:flex;
    align-items:center;
    gap:1rem;
    background:none;
    border:none;
    cursor:pointer;
    text-align:left;
    padding:1.15rem 1.4rem;
    font-family:var(--font-head);
  }
  .faq-index{
    flex:0 0 auto;
    font-size:0.72rem;
    font-weight:700;
    letter-spacing:0.03em;
    color:var(--accent-2);
    background:rgba(108,99,255,0.1);
    border-radius:8px;
    padding:4px 8px;
  }
  .faq-q-text{
    flex:1;
    font-size:0.98rem;
    font-weight:600;
    color:var(--ink);
  }
  .faq-chevron{
    flex:0 0 auto;
    width:22px; height:22px;
    border-radius:50%;
    background:rgba(108,99,255,0.1);
    color:var(--accent-2);
    display:flex; align-items:center; justify-content:center;
    font-size:0.8rem;
    transition:transform .25s ease, background .25s ease, color .25s ease;
  }
  .faq-item.is-open .faq-chevron{
    transform:rotate(45deg);
    background:var(--gradient);
    color:#fff;
  }
  .faq-a-wrap{
    max-height:0;
    overflow:hidden;
    transition:max-height .3s ease;
  }
  .faq-a{
    padding:0 1.4rem 1.4rem 4.1rem;
    font-size:0.92rem;
    color:var(--muted);
    line-height:1.65;
  }

  @media (max-width:600px){
    .faq-a{ padding-left:1.4rem; }
  }

  /* CTA BAND */
  .cta-band{
    position:relative;
    z-index:1;
    background:var(--gradient);
    color:#fff;
    text-align:center;
    padding:70px 24px;
  }
  .cta-band h2{
    font-family:var(--font-head);
    font-weight:700;
    font-size:clamp(1.5rem, 3vw, 2rem);
    margin:0 0 1.5rem;
  }
  .btn-primary-inverse{
    display:inline-flex;
    font-family:var(--font-head);
    font-weight:600;
    font-size:0.92rem;
    text-decoration:none;
    color:var(--accent-2);
    background:#fff;
    padding:13px 30px;
    border-radius:999px;
    box-shadow:0 10px 24px rgba(0,0,0,0.18);
    transition:transform .18s ease;
  }
  .btn-primary-inverse:hover{ transform:translateY(-2px); }

  /* FOOTER */
  .site-footer{
    background:var(--surface-tint);
    text-align:center;
    padding:28px 24px;
    color:var(--muted);
    font-size:0.85rem;
  }

  @media (prefers-reduced-motion: reduce){
    *, *::before, *::after{
      transition-duration:0.01ms !important;
      animation-duration:0.01ms !important;
    }
  }
</style>
</head>
<body>

<div class="purple-wash"></div>

<nav class="nav">
  <div class="nav-inner">
    <a href="home.php" class="brand">
      <img src="logo.png" alt="EasyResume logo" onerror="this.style.display='none'">
      Easy<span class="accent-text">Resume</span>
    </a>
    <div class="nav-links">
      <a href="home.php">Home</a>
      <a href="templates.php">Templates</a>
      <a href="ai_tools.php" class="nav-ai">✦ AI Tools</a>
      <a href="faq.php" class="active">FAQ</a>
    </div>
    <div class="nav-auth">
<?php if (!empty($_SESSION['user_id'])): ?>

  <a href="template-selection.php" class="btn btn-ghost btn-sm">
    Dashboard
  </a>

  <a href="logout.php" class="btn btn-primary btn-sm">
    Logout
  </a>

<?php else: ?>

  <a href="login.php" class="btn btn-ghost btn-sm">
    Login
  </a>

  <a href="signup.php" class="btn btn-primary btn-sm">
    Sign Up
  </a>

<?php endif; ?>
</div>
  </div>
</nav>

<header class="faq-header">
  <span class="eyebrow">✦ Help Center</span>
  <h1 style="color: #6c63ff;">Frequently Asked Questions</h1>
  <p>We've gathered answers to the most common questions to help you get started with EasyResume.</p>
</header>

<div class="faq-content">
  <div class="container">
    <div class="faq-list" id="faqList">

      <div class="faq-item">
        <button class="faq-q" aria-expanded="false">
          <span class="faq-index">01</span>
          <span class="faq-q-text">Is EasyResume free to use?</span>
          <span class="faq-chevron">+</span>
        </button>
        <div class="faq-a-wrap">
          <p class="faq-a">Yes, EasyResume is completely free to use! Simply sign up, login, and start creating your resume with no hidden fees.</p>
        </div>
      </div>

      <div class="faq-item">
        <button class="faq-q" aria-expanded="false">
          <span class="faq-index">02</span>
          <span class="faq-q-text">How do I create my resume?</span>
          <span class="faq-chevron">+</span>
        </button>
        <div class="faq-a-wrap">
          <p class="faq-a">After signing up and logging in, you'll be taken to the resume creation page where you can fill out your details and customize your resume.</p>
        </div>
      </div>

      <div class="faq-item">
        <button class="faq-q" aria-expanded="false">
          <span class="faq-index">03</span>
          <span class="faq-q-text">Do I need to pay for any features?</span>
          <span class="faq-chevron">+</span>
        </button>
        <div class="faq-a-wrap">
          <p class="faq-a">No, all features are free! You can create, edit, and download your resume without any charges.</p>
        </div>
      </div>

      <div class="faq-item">
        <button class="faq-q" aria-expanded="false">
          <span class="faq-index">04</span>
          <span class="faq-q-text">How do I sign up?</span>
          <span class="faq-chevron">+</span>
        </button>
        <div class="faq-a-wrap">
          <p class="faq-a">Simply click the "Sign Up" button on the homepage, fill in your details, and create an account to get started.</p>
        </div>
      </div>

      <div class="faq-item">
        <button class="faq-q" aria-expanded="false">
          <span class="faq-index">05</span>
          <span class="faq-q-text">Can I edit my resume after saving it?</span>
          <span class="faq-chevron">+</span>
        </button>
        <div class="faq-a-wrap">
          <p class="faq-a">Yes, you can update your details as many times as needed while filling out the form, and all changes will be instantly reflected in your resume.</p>
        </div>
      </div>

      <div class="faq-item">
        <button class="faq-q" aria-expanded="false">
          <span class="faq-index">06</span>
          <span class="faq-q-text">Can I choose a template for my resume?</span>
          <span class="faq-chevron">+</span>
        </button>
        <div class="faq-a-wrap">
          <p class="faq-a">Yes, we offer 5 different template designs. You can select the one that best fits your personal style and profession.</p>
        </div>
      </div>

      <div class="faq-item">
        <button class="faq-q" aria-expanded="false">
          <span class="faq-index">07</span>
          <span class="faq-q-text">Is it possible to download my resume?</span>
          <span class="faq-chevron">+</span>
        </button>
        <div class="faq-a-wrap">
          <p class="faq-a">Yes, once your resume is ready, you can easily download it in PDF format.</p>
        </div>
      </div>

      <div class="faq-item">
        <button class="faq-q" aria-expanded="false">
          <span class="faq-index">08</span>
          <span class="faq-q-text">How do I log in?</span>
          <span class="faq-chevron">+</span>
        </button>
        <div class="faq-a-wrap">
          <p class="faq-a">Click the "Login" button on the homepage and enter your credentials to access your profile and start editing your resume.</p>
        </div>
      </div>

      <div class="faq-item">
        <button class="faq-q" aria-expanded="false">
          <span class="faq-index">09</span>
          <span class="faq-q-text">Can I access my resume from any device?</span>
          <span class="faq-chevron">+</span>
        </button>
        <div class="faq-a-wrap">
          <p class="faq-a">Yes, as long as you have an internet connection, you can access your resume from any device by logging into your account.</p>
        </div>
      </div>

      <div class="faq-item">
        <button class="faq-q" aria-expanded="false">
          <span class="faq-index">10</span>
          <span class="faq-q-text">Does the resume look professional?</span>
          <span class="faq-chevron">+</span>
        </button>
        <div class="faq-a-wrap">
          <p class="faq-a">Absolutely! Our templates are designed to make your resume stand out professionally.</p>
        </div>
      </div>

    </div>
  </div>
</div>

<section class="cta-band">
  <h2>Still have questions? Just dive in and try it.</h2>
  <a href="signup.php" class="btn-primary-inverse">Create Your Free Account</a>
</section>

<footer class="site-footer">
  <p>&copy; Copyright <?php echo date('Y'); ?>. All Rights Reserved - <span>EasyResume</span></p>
</footer>

<script>
/* Accordion logic with auto-close */
document.querySelectorAll('.faq-q').forEach(btn => {
  btn.addEventListener('click', () => {
    const item = btn.closest('.faq-item');
    const wrap = item.querySelector('.faq-a-wrap');
    const isOpen = item.classList.contains('is-open');
    const allItems = document.querySelectorAll('.faq-item');

    /* Close all others */
    allItems.forEach(otherItem => {
      if (otherItem !== item && otherItem.classList.contains('is-open')) {
        otherItem.classList.remove('is-open');
        otherItem.querySelector('.faq-q').setAttribute('aria-expanded', 'false');
        otherItem.querySelector('.faq-a-wrap').style.maxHeight = '0px';
      }
    });

    if (isOpen) {
      wrap.style.maxHeight = '0px';
      item.classList.remove('is-open');
      btn.setAttribute('aria-expanded', 'false');
    } else {
      /* requestAnimationFrame trick for smooth height transition */
      wrap.style.maxHeight = '0px';
      requestAnimationFrame(() => {
        wrap.style.maxHeight = wrap.scrollHeight + 'px';
      });
      item.classList.add('is-open');
      btn.setAttribute('aria-expanded', 'true');
    }
  });
});

/* Stagger entrance */
const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('in');
      observer.unobserve(entry.target);
    }
  });
}, { threshold: 0.1 });

document.querySelectorAll('.faq-item').forEach((el, i) => {
  el.style.transitionDelay = (i * 60) + 'ms';
  observer.observe(el);
});
</script>

</body>
</html>