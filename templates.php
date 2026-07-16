<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Resume Templates — EasyResume</title>
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

  body{
    margin:0;
    font-family:var(--font-body);
    color:var(--ink);
    background:var(--surface-soft);
    -webkit-font-smoothing:antialiased;
  }

  a{ color:inherit; }
  img{ max-width:100%; display:block; }

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
    gap:2px;
  }
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
  .nav-cta{
    font-family:var(--font-head);
    font-size:0.85rem;
    font-weight:600;
    text-decoration:none;
    color:#fff;
    background:var(--gradient);
    padding:10px 20px;
    border-radius:999px;
    box-shadow:var(--shadow-sm);
    transition:transform .18s ease, box-shadow .18s ease;
  }
  .nav-cta:hover{ transform:translateY(-2px); box-shadow:var(--shadow-md); }

  @media (max-width:760px){
    .nav-links{ display:none; }
    .nav-inner{ justify-content:space-between; }
  }

  /* HERO */
  .hero{
    position:relative;
    overflow:hidden;
    padding:96px 24px 80px;
    text-align:center;
  }
  .hero-blob{
    position:absolute;
    border-radius:50%;
    filter:blur(60px);
    opacity:0.55;
    z-index:0;
  }
  .blob-a{
    width:420px; height:420px;
    background:radial-gradient(circle, #6c63ff, transparent 70%);
    top:-160px; left:-120px;
  }
  .blob-b{
    width:380px; height:380px;
    background:radial-gradient(circle, #3a1c8e, transparent 70%);
    top:-80px; right:-140px;
    opacity:0.35;
  }
  .hero-inner{
    position:relative;
    z-index:1;
    max-width:640px;
    margin:0 auto;
  }
  .eyebrow{
    display:inline-block;
    font-family:var(--font-head);
    font-size:0.74rem;
    font-weight:700;
    letter-spacing:0.1em;
    text-transform:uppercase;
    color:var(--accent-2);
    background:rgba(108,99,255,0.1);
    padding:6px 16px;
    border-radius:999px;
    margin-bottom:1.25rem;
  }
  .hero h1{
    font-family:var(--font-head);
    font-weight:700;
    font-size:clamp(2.1rem, 5vw, 3.1rem);
    line-height:1.15;
    margin:0 0 1rem;
    color:var(--ink);
  }
  .hero p{
    font-size:1.02rem;
    line-height:1.65;
    color:var(--muted);
    margin:0 0 2rem;
  }
  .hero-actions{
    display:flex;
    gap:0.9rem;
    justify-content:center;
    flex-wrap:wrap;
  }

  .btn{
    display:inline-flex;
    align-items:center;
    gap:0.4rem;
    font-family:var(--font-head);
    font-weight:600;
    font-size:0.92rem;
    text-decoration:none;
    padding:13px 28px;
    border-radius:999px;
    border:none;
    cursor:pointer;
    transition:transform .18s ease, box-shadow .18s ease, background .18s ease, color .18s ease;
  }
  .btn-primary{
    background:var(--gradient);
    color:#fff;
    box-shadow:var(--shadow-sm);
  }
  .btn-primary:hover{ transform:translateY(-2px); box-shadow:var(--shadow-md); }
  .btn-ghost{
    background:transparent;
    color:var(--accent-2);
    border:1px solid var(--border-strong);
  }
  .btn-ghost:hover{ background:rgba(108,99,255,0.08); }
  .btn-sm{ padding:9px 18px; font-size:0.82rem; margin-top:0.9rem; }

  /* SHOWCASE / CAROUSEL */
  .showcase{
    padding:20px 24px 100px;
    position:relative;
  }
  .showcase-head{
    max-width:560px;
    margin:0 auto 3rem;
    text-align:center;
  }
  .showcase-head h2{
    font-family:var(--font-head);
    font-weight:700;
    font-size:clamp(1.6rem, 3.2vw, 2.1rem);
    margin:0.6rem 0 0.75rem;
  }
  .showcase-head p{
    color:var(--muted);
    line-height:1.6;
    margin:0;
  }

  .carousel{
    position:relative;
    max-width:1100px;
    margin:0 auto;
    display:flex;
    align-items:center;
    gap:0.75rem;
  }

  .car-viewport{
    flex:1;
    overflow-x:auto;
    overflow-y:hidden;
    scroll-snap-type:x mandatory;
    scrollbar-width:none;
    -ms-overflow-style:none;
  }
  .car-viewport::-webkit-scrollbar{ display:none; }

  .car-track{
    display:flex;
    gap:1.5rem;
    list-style:none;
    margin:0;
    padding:8px clamp(16px, 16vw, 200px) 28px;
  }

  .car-card{
    scroll-snap-align:center;
    flex:0 0 auto;
    width:min(300px, 78vw);
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:20px;
    box-shadow:var(--shadow-sm);
    overflow:hidden;
    transition:transform .35s cubic-bezier(.4,0,.2,1), box-shadow .35s ease, opacity .35s ease;
    opacity:0.55;
    transform:scale(0.93);
  }
  .car-card.is-active{
    opacity:1;
    transform:scale(1);
    box-shadow:var(--shadow-lg);
  }

  .card-thumb{
    position:relative;
    height:200px;
    background:var(--surface-tint);
    display:flex;
    align-items:center;
    justify-content:center;
    overflow:hidden;
  }
  .card-thumb img{ width:100%; height:100%; object-fit:cover; }

  .thumb-blue{ background:linear-gradient(160deg,#6c63ff,#9089ff); }
  .thumb-white{ background:linear-gradient(160deg,#eef0f6,#ffffff); }
  .thumb-black{ background:linear-gradient(160deg,#3a3a45,#1d1c24); }

  .card-body{
    padding:1.25rem 1.4rem 1.5rem;
  }
  .card-tag{
    display:inline-block;
    font-size:0.7rem;
    font-weight:600;
    letter-spacing:0.04em;
    text-transform:uppercase;
    color:var(--accent-2);
    background:rgba(108,99,255,0.1);
    padding:4px 10px;
    border-radius:999px;
    margin-bottom:0.6rem;
  }
  .card-body h3{
    font-family:var(--font-head);
    font-size:1.1rem;
    font-weight:700;
    margin:0 0 0.4rem;
  }
  .card-body p{
    font-size:0.86rem;
    color:var(--muted);
    line-height:1.55;
    margin:0;
  }

  /* Locked teaser card */
  .car-card-locked .card-thumb{
    background:var(--surface-tint);
  }
  .locked-swatch{
    position:absolute;
    width:62%;
    height:78%;
    border-radius:14px;
    filter:blur(7px);
  }
  .swatch-green{
    background:linear-gradient(160deg,#3aa66c,#7be0a4);
    left:8%; top:14%;
    transform:rotate(-8deg);
  }
  .swatch-red{
    background:linear-gradient(160deg,#c4385f,#ec7c98);
    right:6%; top:18%;
    transform:rotate(7deg);
  }
  .locked-overlay{
    position:relative;
    z-index:2;
    display:flex;
    flex-direction:column;
    align-items:center;
    gap:0.4rem;
    background:rgba(255,255,255,0.6);
    backdrop-filter:blur(6px);
    -webkit-backdrop-filter:blur(6px);
    width:100%;
    height:100%;
    justify-content:center;
  }
  .lock-icon{ font-size:1.5rem; }
  .locked-label{
    font-family:var(--font-head);
    font-weight:700;
    font-size:0.88rem;
    color:var(--accent-2);
  }

  .car-arrow{
    flex:0 0 auto;
    width:42px;
    height:42px;
    border-radius:50%;
    border:1px solid var(--border-strong);
    background:var(--surface);
    color:var(--accent-2);
    font-size:1.3rem;
    line-height:1;
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:center;
    transition:background .18s ease, color .18s ease, transform .18s ease;
  }
  .car-arrow:hover{ background:var(--gradient); color:#fff; transform:translateY(-2px); }

  @media (max-width:700px){
    .car-arrow{ display:none; }
    .car-track{ padding-inline:6vw; }
  }

  .car-dots{
    display:flex;
    justify-content:center;
    gap:0.5rem;
    margin-top:1.75rem;
  }
  .car-dot{
    width:8px;
    height:8px;
    border-radius:50%;
    background:var(--border-strong);
    border:none;
    padding:0;
    cursor:pointer;
    transition:background .2s ease, width .2s ease;
  }
  .car-dot.is-active{
    background:var(--gradient);
    width:22px;
    border-radius:999px;
  }

  /* CTA BAND */
  .cta-band{
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
      scroll-behavior:auto !important;
    }
  }
</style>
</head>
<body>

<nav class="nav">
  <div class="nav-inner">
    <a href="home.php" class="brand">✦ Easy<span class="accent-text">Resume</span></a>
    <div class="nav-links">
      <a href="home.php">Home</a>
      <a href="templates.php" class="active">Templates</a>
      <a href="faq.php">FAQ</a>
    </div>
    <a href="template-selection.php" class="nav-cta">Get Started</a>
  </div>
</nav>

<header class="hero">
  <div class="hero-blob blob-a"></div>
  <div class="hero-blob blob-b"></div>
  <div class="hero-inner">
    <span class="eyebrow">✦ Resume Templates</span>
    <h1>Find the design<br>that gets you hired.</h1>
    <p>Every template is ATS-friendly, recruiter-tested, and ready to edit in minutes. Pick the look that matches your next role.</p>
    <div class="hero-actions">
      <a href="template-selection.php" class="btn btn-primary">Browse All Templates</a>
      <a href="#showcase" class="btn btn-ghost">See a Preview ↓</a>
    </div>
  </div>
</header>

<section class="showcase" id="showcase">
  <div class="showcase-head">
    <span class="eyebrow">A Peek Inside</span>
    <h2>Five templates. One perfect match.</h2>
    <p>Swipe through a taste of the collection — create a free account to unlock every design.</p>
  </div>

  <div class="carousel">
    <button class="car-arrow car-prev" id="carPrev" aria-label="Previous template">‹</button>

    <div class="car-viewport" id="carViewport" role="region" aria-label="Template previews">
      <ul class="car-track" id="carTrack">

        <li class="car-card" data-name="Blue">
          <div class="card-thumb thumb-blue">
            <img src="BLUE .png" alt="Blue resume template" onerror="this.style.display='none'">
          </div>
          <div class="card-body">
            <span class="card-tag">For corporate roles</span>
            <h3>Blue</h3>
            <p>Clean and minimal, with a confident touch of color.</p>
          </div>
        </li>

        <li class="car-card" data-name="White">
          <div class="card-thumb thumb-white">
            <img src="WHITE.png" alt="White resume template" onerror="this.style.display='none'">
          </div>
          <div class="card-body">
            <span class="card-tag">For any industry</span>
            <h3>White</h3>
            <p>Simple and elegant — built to let your experience speak.</p>
          </div>
        </li>

        <li class="car-card" data-name="Black">
          <div class="card-thumb thumb-black">
            <img src="black.png" alt="Black resume template" onerror="this.style.display='none'">
          </div>
          <div class="card-body">
            <span class="card-tag">For senior roles</span>
            <h3>Black</h3>
            <p>Timeless and sophisticated — a classic that never misses.</p>
          </div>
        </li>

        <li class="car-card car-card-locked" data-name="Locked">
          <div class="card-thumb thumb-locked">
            <div class="locked-swatch swatch-green"></div>
            <div class="locked-swatch swatch-red"></div>
            <div class="locked-overlay">
              <span class="lock-icon">🔒</span>
              <span class="locked-label">+2 More Designs</span>
            </div>
          </div>
          <div class="card-body">
            <span class="card-tag">Green &amp; Red</span>
            <h3>Unlock the Rest</h3>
            <p>Create a free account to see every template in the collection.</p>
            <a href="signup.php" class="btn btn-primary btn-sm">Unlock Full Collection</a>
          </div>
        </li>

      </ul>
    </div>

    <button class="car-arrow car-next" id="carNext" aria-label="Next template">›</button>
  </div>

  <div class="car-dots" id="carDots"></div>
</section>

<section class="cta-band">
  <h2>Your next job starts with the right resume.</h2>
  <a href="signup.php" class="btn-primary-inverse">Create Your Free Account</a>
</section>

<footer class="site-footer">
  <p>&copy; 2026 EasyResume. All rights reserved.</p>
</footer>

<script>
(function(){
  const viewport = document.getElementById('carViewport');
  const track    = document.getElementById('carTrack');
  const cards    = Array.from(track.querySelectorAll('.car-card'));
  const dotsWrap = document.getElementById('carDots');
  const prevBtn  = document.getElementById('carPrev');
  const nextBtn  = document.getElementById('carNext');

  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  let activeIndex = 0;
  let autoplayTimer = null;

  cards.forEach((_, i) => {
    const dot = document.createElement('button');
    dot.className = 'car-dot';
    dot.setAttribute('aria-label', 'Go to template ' + (i + 1));
    dot.addEventListener('click', () => goTo(i));
    dotsWrap.appendChild(dot);
  });
  const dots = Array.from(dotsWrap.querySelectorAll('.car-dot'));

  function setActive(i){
    activeIndex = i;
    cards.forEach((c, idx) => c.classList.toggle('is-active', idx === i));
    dots.forEach((d, idx) => d.classList.toggle('is-active', idx === i));
  }

  function goTo(i){
    const clamped = Math.max(0, Math.min(i, cards.length - 1));
    const card = cards[clamped];
    const target = card.offsetLeft - (viewport.clientWidth - card.clientWidth) / 2;
    viewport.scrollTo({ left: target, behavior: reduceMotion ? 'auto' : 'smooth' });
    setActive(clamped);
  }

  prevBtn.addEventListener('click', () => goTo(activeIndex - 1));
  nextBtn.addEventListener('click', () => goTo((activeIndex + 1) % cards.length));

  let scrollDebounce;
  viewport.addEventListener('scroll', () => {
    clearTimeout(scrollDebounce);
    scrollDebounce = setTimeout(() => {
      const center = viewport.scrollLeft + viewport.clientWidth / 2;
      let closest = 0, closestDist = Infinity;
      cards.forEach((c, idx) => {
        const cardCenter = c.offsetLeft + c.clientWidth / 2;
        const dist = Math.abs(cardCenter - center);
        if (dist < closestDist) { closestDist = dist; closest = idx; }
      });
      setActive(closest);
    }, 80);
  }, { passive: true });

  function startAutoplay(){
    if (reduceMotion) return;
    stopAutoplay();
    autoplayTimer = setInterval(() => goTo((activeIndex + 1) % cards.length), 4500);
  }
  function stopAutoplay(){
    if (autoplayTimer) clearInterval(autoplayTimer);
  }

  viewport.addEventListener('pointerenter', stopAutoplay);
  viewport.addEventListener('pointerleave', startAutoplay);
  viewport.addEventListener('touchstart', stopAutoplay, { passive: true });

  setActive(0);
  startAutoplay();
})();
</script>

</body>
</html>