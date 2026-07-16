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
<title>EasyResume — Build a Resume That Gets You Hired</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  :root{
    --ink:#1c1a29;
    --muted:#6f6c85;
    --muted-soft:#8c8aa0;
    --accent:#6c63ff;
    --accent-2:#3a1c8e;
    --gradient: linear-gradient(135deg, #6c63ff, #3a1c8e);
    --gradient-soft: linear-gradient(135deg, rgba(108,99,255,0.12), rgba(58,28,142,0.06));
    --surface:#ffffff;
    --surface-soft:#f9f9fc;
    --surface-tint:#f5f3ff;
    --border: rgba(108,99,255,0.14);
    --border-strong: rgba(108,99,255,0.3);
    --shadow-sm: 0 2px 10px rgba(38,20,90,0.06);
    --shadow-md: 0 12px 30px rgba(38,20,90,0.10);
    --shadow-lg: 0 28px 60px rgba(38,20,90,0.16);
    --shadow-glow: 0 0 0 1px rgba(108,99,255,0.08), 0 30px 70px rgba(108,99,255,0.22);
    --success:#1f9d55;
    --font-head:'Poppins',sans-serif;
    --font-body:'Roboto',sans-serif;
  }

  html{ scroll-behavior:smooth; }

  *{ box-sizing:border-box; }

  body{
    margin:0;
    font-family:var(--font-body);
    color:var(--ink);
    background:var(--surface);
    -webkit-font-smoothing:antialiased;
  }

  a{ color:inherit; }
  img{ max-width:100%; display:block; }
  .container{ max-width:1180px; margin:0 auto; padding:0 28px; }

  .reveal{ opacity:0; transform:translateY(18px); transition:opacity .7s ease, transform .7s ease; }
  .reveal.in{ opacity:1; transform:translateY(0); }

  /* TOP ACCENT BAR */
  .top-accent-bar{
    height:5px;
    width:100%;
    background:linear-gradient(90deg, #3a1c8e, #6c63ff 35%, #9d8fff 50%, #6c63ff 65%, #3a1c8e);
  }

  /* NAVBAR */
  .nav{
    position:sticky;
    top:0;
    z-index:50;
    background:linear-gradient(180deg, rgba(245,243,255,0.92), rgba(255,255,255,0.86));
    backdrop-filter:blur(18px);
    -webkit-backdrop-filter:blur(18px);
    border-bottom:1px solid var(--border-strong);
    box-shadow:0 1px 0 rgba(108,99,255,0.06), var(--shadow-sm);
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
    gap:1.85rem;
    margin-left:0.5rem;
    flex:1;
  }
  .nav-links a{
    text-decoration:none;
    font-family:var(--font-head);
    font-size:0.87rem;
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

  /* Hamburger menu */
  .hamburger{
    display:none;
    background:none;
    border:none;
    cursor:pointer;
    padding:6px;
    flex-direction:column;
    gap:5px;
  }
  .hamburger span{
    display:block;
    width:22px; height:2px;
    background:var(--ink);
    border-radius:2px;
    transition:transform .25s ease, opacity .2s ease;
  }
  .hamburger.is-open span:nth-child(1){ transform:translateY(7px) rotate(45deg); }
  .hamburger.is-open span:nth-child(2){ opacity:0; }
  .hamburger.is-open span:nth-child(3){ transform:translateY(-7px) rotate(-45deg); }

  .mobile-nav{
    display:none;
    position:fixed;
    top:0; left:0; right:0; bottom:0;
    z-index:49;
    background:rgba(255,255,255,0.97);
    backdrop-filter:blur(20px);
    -webkit-backdrop-filter:blur(20px);
    flex-direction:column;
    align-items:center;
    justify-content:center;
    gap:1.5rem;
    opacity:0;
    transition:opacity .3s ease;
    pointer-events:none;
  }
  .mobile-nav.is-open{
    display:flex;
    opacity:1;
    pointer-events:all;
  }
  .mobile-nav a{
    font-family:var(--font-head);
    font-size:1.2rem;
    font-weight:600;
    text-decoration:none;
    color:var(--ink);
    padding:8px 24px;
    border-radius:12px;
    transition:background .2s ease, color .2s ease;
  }
  .mobile-nav a:hover{
    background:var(--surface-tint);
    color:var(--accent-2);
  }
  .mobile-nav .mobile-auth{
    display:flex;
    gap:0.7rem;
    margin-top:1rem;
  }

  @media (max-width:860px){
    .hamburger{ display:flex; }
  }

  /* BUTTONS */
  .btn{
    display:inline-flex;
    align-items:center;
    gap:0.4rem;
    font-family:var(--font-head);
    font-weight:600;
    font-size:0.9rem;
    text-decoration:none;
    padding:13px 28px;
    border-radius:999px;
    border:none;
    cursor:pointer;
    transition:transform .18s ease, box-shadow .18s ease, background .18s ease, color .18s ease;
    white-space:nowrap;
  }
  .btn-primary{ background:var(--gradient); color:#fff; box-shadow:0 8px 22px rgba(108,99,255,0.32); }
  .btn-primary:hover{ transform:translateY(-2px); box-shadow:0 14px 30px rgba(108,99,255,0.4); }
  .btn-ghost{ background:transparent; color:var(--accent-2); border:1px solid var(--border-strong); }
  .btn-ghost:hover{ background:rgba(108,99,255,0.08); }
  .btn-sm{ padding:9px 18px; font-size:0.8rem; }

  /* HERO */
  .hero{
    position:relative;
    overflow:hidden;
    padding:130px 24px 90px;
    background:
      radial-gradient(900px 560px at 50% -20%, rgba(58,28,142,0.55), rgba(108,99,255,0.22) 45%, transparent 72%),
      radial-gradient(620px 480px at 95% 10%, rgba(58,28,142,0.16), transparent 70%),
      radial-gradient(560px 420px at 8% 0%, rgba(108,99,255,0.18), transparent 70%),
      var(--surface);
  }
  .hero::before{
    content:'';
    position:absolute;
    top:0; left:0; right:0;
    height:230px;
    background:linear-gradient(180deg, rgba(58,28,142,0.10), rgba(58,28,142,0) 100%);
    z-index:0;
    pointer-events:none;
  }
  .hero-grid{
    position:relative;
    z-index:1;
    max-width:1180px;
    margin:0 auto;
    display:grid;
    grid-template-columns:1.05fr 0.95fr;
    gap:4rem;
    align-items:center;
  }
  .hero-copy .eyebrow{
    display:inline-flex;
    align-items:center;
    gap:6px;
    font-family:var(--font-head);
    font-size:0.74rem;
    font-weight:700;
    letter-spacing:0.08em;
    text-transform:uppercase;
    color:var(--accent-2);
    background:#fff;
    border:1px solid var(--border-strong);
    padding:7px 16px;
    border-radius:999px;
    margin-bottom:1.5rem;
    box-shadow:var(--shadow-sm);
  }
  .hero-copy h1{
    font-family:var(--font-head);
    font-weight:800;
    font-size:clamp(2.3rem, 4.4vw, 3.4rem);
    line-height:1.12;
    letter-spacing:-0.01em;
    margin:0 0 1.3rem;
  }
  .hero-copy h1 .grad{
    background:var(--gradient);
    -webkit-background-clip:text;
    background-clip:text;
    color:transparent;
  }
  .hero-copy p{
    font-size:1.06rem;
    line-height:1.7;
    color:var(--muted);
    margin:0 0 1.75rem;
    max-width:500px;
  }
  .hero-actions{ display:flex; gap:0.9rem; flex-wrap:wrap; margin-bottom:1.75rem; }

  .trust-row{
    display:flex;
    flex-wrap:wrap;
    gap:1.4rem;
  }
  .trust-row span{
    display:flex;
    align-items:center;
    gap:6px;
    font-family:var(--font-head);
    font-size:0.8rem;
    font-weight:600;
    color:var(--muted);
  }
  .trust-row span .tick{
    width:16px; height:16px;
    border-radius:50%;
    background:rgba(31,157,85,0.14);
    color:var(--success);
    display:flex; align-items:center; justify-content:center;
    font-size:0.6rem;
    flex-shrink:0;
  }

  /* HERO VISUAL — floating dashboard */
  .hero-visual{ position:relative; min-height:420px; }
  .dash-glow{
    position:absolute;
    width:420px; height:420px;
    background:radial-gradient(circle, rgba(108,99,255,0.32), transparent 70%);
    top:50%; left:50%;
    transform:translate(-50%,-50%);
    filter:blur(10px);
    z-index:0;
  }
  .dash-main{
    position:relative;
    z-index:2;
    background:rgba(255,255,255,0.85);
    backdrop-filter:blur(14px);
    -webkit-backdrop-filter:blur(14px);
    border:1px solid var(--border);
    border-radius:20px;
    box-shadow:var(--shadow-glow);
    padding:22px;
    max-width:380px;
    margin:0 auto;
  }
  .dash-main .dash-head{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:16px;
  }
  .dash-main .dash-head strong{
    font-family:var(--font-head);
    font-size:0.92rem;
    font-weight:700;
  }
  .dash-main .dash-head .live{
    font-size:0.68rem;
    font-weight:700;
    color:var(--success);
    background:rgba(31,157,85,0.12);
    padding:4px 10px;
    border-radius:999px;
    display:flex; align-items:center; gap:5px;
  }
  .dash-main .dash-head .live .pulse{
    width:6px; height:6px; border-radius:50%; background:var(--success);
    animation:pulse 1.6s infinite;
  }
  @keyframes pulse{ 0%,100%{ opacity:1; } 50%{ opacity:0.3; } }

  .skel-line{
    height:8px;
    border-radius:5px;
    background:linear-gradient(90deg, rgba(108,99,255,0.16), rgba(108,99,255,0.06));
    margin-bottom:8px;
  }
  .skel-line.w-80{ width:80%; }
  .skel-line.w-60{ width:60%; }
  .skel-line.w-95{ width:95%; }
  .skel-line.w-40{ width:40%; }

  .dash-skills{ display:flex; flex-wrap:wrap; gap:6px; margin-top:14px; }
  .dash-skills span{
    font-size:0.68rem;
    font-weight:600;
    font-family:var(--font-head);
    color:var(--accent-2);
    background:var(--surface-tint);
    border:1px solid var(--border);
    padding:5px 11px;
    border-radius:999px;
  }

  .float-card{
    position:absolute;
    background:rgba(255,255,255,0.92);
    backdrop-filter:blur(10px);
    border:1px solid var(--border);
    border-radius:16px;
    box-shadow:var(--shadow-md);
    padding:14px 16px;
    font-family:var(--font-head);
    z-index:3;
  }
  .card-ats{
    top:-6px; right:-10px;
    width:148px;
    animation:floaty 6s ease-in-out infinite;
  }
  .card-ats .ring{
    width:54px; height:54px;
    border-radius:50%;
    background:conic-gradient(var(--accent) 0deg 320deg, rgba(108,99,255,0.12) 320deg 360deg);
    display:flex; align-items:center; justify-content:center;
    margin:0 auto 6px;
  }
  .card-ats .ring span{
    width:42px; height:42px;
    background:#fff;
    border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    font-size:0.82rem;
    font-weight:800;
    color:var(--accent-2);
  }
  .card-ats p{ margin:0; text-align:center; font-size:0.7rem; font-weight:600; color:var(--muted); }

  .card-match{
    bottom:38px; left:-34px;
    display:flex; align-items:center; gap:10px;
    animation:floaty 5.4s ease-in-out infinite reverse;
  }
  .card-match .pct{ font-size:1.05rem; font-weight:800; color:var(--success); }
  .card-match .lbl{ font-size:0.68rem; color:var(--muted); font-weight:600; }

  .card-suggest{
    bottom:-26px; right:14px;
    width:200px;
    animation:floaty 6.6s ease-in-out infinite;
  }
  .card-suggest .tag{
    font-size:0.64rem;
    font-weight:700;
    color:var(--accent-2);
    text-transform:uppercase;
    letter-spacing:0.05em;
    display:block;
    margin-bottom:5px;
  }
  .card-suggest p{ margin:0; font-size:0.74rem; color:var(--ink); font-weight:500; line-height:1.4; }

  @keyframes floaty{ 0%,100%{ transform:translateY(0); } 50%{ transform:translateY(-9px); } }

  @media (max-width:900px){
    .hero-grid{ grid-template-columns:1fr; text-align:center; }
    .hero-copy p{ margin-inline:auto; }
    .hero-actions, .trust-row{ justify-content:center; }
    .hero-visual{ margin-top:2.5rem; min-height:380px; }
    .card-match{ left:0; }
    .card-suggest{ right:0; }
  }

  /* SECTION HEADERS (shared) */
  .sec-head{ max-width:600px; margin:0 auto 3.2rem; text-align:center; }
  .sec-head .eyebrow{
    display:inline-block;
    font-family:var(--font-head);
    font-size:0.74rem;
    font-weight:700;
    letter-spacing:0.08em;
    text-transform:uppercase;
    color:var(--accent-2);
    background:var(--surface-tint);
    padding:6px 16px;
    border-radius:999px;
    margin-bottom:1.1rem;
  }
  .sec-head h2{
    font-family:var(--font-head);
    font-weight:800;
    font-size:clamp(1.7rem, 3.2vw, 2.3rem);
    letter-spacing:-0.01em;
    margin:0.3rem 0 0;
  }
  .sec-head p{ color:var(--muted); margin:0.9rem 0 0; font-size:1rem; line-height:1.6; }

  /* TRUST METRICS */
  .trust-band{ padding:60px 24px; background:var(--surface); border-top:1px solid var(--border); border-bottom:1px solid var(--border); }
  .trust-grid{
    max-width:1000px; margin:0 auto;
    display:grid; grid-template-columns:repeat(3,1fr);
    gap:2rem; text-align:center;
  }
  .trust-grid .stat-num{
    font-family:var(--font-head);
    font-weight:800;
    font-size:clamp(1.9rem, 3.4vw, 2.5rem);
    background:var(--gradient);
    -webkit-background-clip:text;
    background-clip:text;
    color:transparent;
  }
  .trust-grid .stat-lbl{ font-size:0.88rem; color:var(--muted); font-weight:500; margin-top:4px; }
  @media (max-width:700px){ .trust-grid{ grid-template-columns:1fr; gap:2.2rem; } }

  /* FEATURES */
  .features{ background:var(--surface-tint); padding:100px 24px; }
  .feature-grid{
    max-width:1180px; margin:0 auto;
    display:grid; grid-template-columns:repeat(3,1fr);
    gap:1.5rem;
  }
  .feature-card{
    background:rgba(255,255,255,0.85);
    backdrop-filter:blur(8px);
    border:1px solid var(--border);
    border-radius:20px;
    padding:2.1rem 1.85rem;
    box-shadow:var(--shadow-sm);
    transition:transform .28s ease, box-shadow .28s ease, border-color .28s ease;
  }
  .feature-card:hover{ transform:translateY(-6px); box-shadow:var(--shadow-lg); border-color:var(--border-strong); }
  .feature-icon{
    width:50px; height:50px;
    border-radius:14px;
    background:var(--gradient);
    display:flex; align-items:center; justify-content:center;
    margin-bottom:1.3rem;
    font-size:1.3rem;
    color:#fff;
    box-shadow:0 8px 20px rgba(108,99,255,0.32);
  }
  .feature-card h3{ font-family:var(--font-head); font-size:1.08rem; font-weight:700; margin:0 0 0.6rem; }
  .feature-card p{ font-size:0.9rem; color:var(--muted); line-height:1.65; margin:0; }
  @media (max-width:860px){ .feature-grid{ grid-template-columns:1fr; } }

  /* AI INTELLIGENCE SECTION */
  .ai-section{ padding:110px 24px; background:var(--surface); position:relative; overflow:hidden; }
  .ai-glow{ position:absolute; width:600px; height:600px; background:radial-gradient(circle, rgba(108,99,255,0.1), transparent 70%); top:-200px; right:-200px; z-index:0; }
  .ai-grid{
    position:relative; z-index:1;
    max-width:1180px; margin:0 auto;
    display:grid; grid-template-columns:0.9fr 1.1fr;
    gap:4rem; align-items:center;
  }
  .ai-copy .eyebrow{
    display:inline-block; font-family:var(--font-head); font-size:0.74rem; font-weight:700;
    letter-spacing:0.08em; text-transform:uppercase; color:var(--accent-2);
    background:var(--surface-tint); padding:6px 16px; border-radius:999px; margin-bottom:1.1rem;
  }
  .ai-copy h2{ font-family:var(--font-head); font-weight:800; font-size:clamp(1.7rem, 3.2vw, 2.3rem); margin:0 0 1rem; letter-spacing:-0.01em; }
  .ai-copy p{ color:var(--muted); line-height:1.7; margin:0 0 1.75rem; }
  .ai-list{ list-style:none; padding:0; margin:0 0 2rem; display:flex; flex-direction:column; gap:0.85rem; }
  .ai-list li{ display:flex; align-items:flex-start; gap:10px; font-size:0.92rem; color:var(--ink); font-weight:500; }
  .ai-list li .ico{
    width:22px; height:22px; border-radius:7px; flex-shrink:0;
    background:var(--gradient); color:#fff; font-size:0.66rem; font-weight:800;
    display:flex; align-items:center; justify-content:center; margin-top:1px;
  }

  .ai-dashboard{
    background:linear-gradient(160deg, #ffffff, #f7f6ff);
    border:1px solid var(--border);
    border-radius:24px;
    box-shadow:var(--shadow-lg);
    padding:28px;
  }
  .ai-dash-row{ display:grid; grid-template-columns:1fr 1fr; gap:16px; }
  .ai-widget{
    background:#fff;
    border:1px solid var(--border);
    border-radius:16px;
    padding:18px;
    box-shadow:var(--shadow-sm);
  }
  .ai-widget .w-label{ font-family:var(--font-head); font-size:0.72rem; font-weight:700; text-transform:uppercase; letter-spacing:0.04em; color:var(--muted-soft); margin-bottom:10px; }
  .ai-widget .w-big{ font-family:var(--font-head); font-size:1.7rem; font-weight:800; color:var(--accent-2); }
  .ai-widget .w-bar{ height:7px; border-radius:5px; background:var(--surface-tint); overflow:hidden; margin-top:10px; }
  .ai-widget .w-bar i{ display:block; height:100%; background:var(--gradient); border-radius:5px; }
  .ai-widget.full{ grid-column:1 / -1; }
  .ai-widget .kw{ display:inline-flex; align-items:center; gap:5px; font-size:0.72rem; font-weight:600; color:var(--ink); background:var(--surface-tint); padding:5px 10px; border-radius:999px; margin:3px 4px 0 0; }
  .ai-widget .kw.miss{ color:#b54708; background:#fff4e8; }

  @media (max-width:900px){
    .ai-grid{ grid-template-columns:1fr; }
    .ai-dashboard{ margin-top:1rem; }
  }

  /* TEMPLATE SHOWCASE */
  .templates-section{ background:var(--surface-tint); padding:100px 24px; }
  .tmpl-tabs{ display:flex; justify-content:center; gap:0.6rem; flex-wrap:wrap; margin-bottom:2.6rem; }
  .tmpl-tabs span{
    font-family:var(--font-head); font-size:0.8rem; font-weight:600;
    padding:8px 18px; border-radius:999px; border:1px solid var(--border-strong);
    color:var(--accent-2); background:#fff;
  }
  .tmpl-tabs span.is-active{ background:var(--gradient); color:#fff; border-color:transparent; }
  .tmpl-tabs span:hover{ background:var(--gradient); color:#fff; border-color:transparent; }
  .tmpl-grid{
    max-width:1180px; margin:0 auto;
    display:grid; grid-template-columns:repeat(5,1fr);
    gap:1.25rem;
  }
  .tmpl-card{
    background:#fff; border-radius:16px; border:1px solid var(--border);
    box-shadow:var(--shadow-sm); overflow:hidden;
    transition:transform .25s ease, box-shadow .25s ease;
  }
  .tmpl-card:hover{ transform:translateY(-6px); box-shadow:var(--shadow-lg); }
  .tmpl-preview{ aspect-ratio:3/4; padding:14px; display:flex; flex-direction:column; gap:6px; }
  .tmpl-preview .bar{ height:7px; border-radius:4px; background:var(--surface-tint); }
  .tmpl-preview .bar.head{ width:55%; height:10px; }
  .tmpl-preview .bar.w70{ width:70%; }
  .tmpl-preview .bar.w90{ width:90%; }
  .tmpl-preview .bar.w50{ width:50%; }
  .tmpl-preview .accent-block{ height:34px; border-radius:6px; margin-bottom:4px; }
  .tmpl-card.blue .accent-block{ background:linear-gradient(135deg,#3b82f6,#1d4ed8); }
  .tmpl-card.green .accent-block{ background:linear-gradient(135deg,#22c55e,#15803d); }
  .tmpl-card.white .accent-block{ background:#e5e7eb; }
  .tmpl-card.black .accent-block{ background:linear-gradient(135deg,#374151,#111827); }
  .tmpl-card.red .accent-block{ background:linear-gradient(135deg,#f87171,#b91c1c); }
  .tmpl-name{ padding:10px 14px 16px; font-family:var(--font-head); font-size:0.82rem; font-weight:700; text-align:center; }
  @media (max-width:900px){ .tmpl-grid{ grid-template-columns:repeat(2,1fr); } }

  /* CTA BAND */
  .cta-band{
    position:relative;
    overflow:hidden;
    background:linear-gradient(135deg, #6c63ff, #3a1c8e 55%, #2a1366);
    color:#fff;
    text-align:center;
    padding:100px 24px;
  }
  .cta-band::before{
    content:'';
    position:absolute; inset:0;
    background:radial-gradient(420px 320px at 20% 10%, rgba(255,255,255,0.16), transparent 70%);
  }
  .cta-band h2{
    position:relative; z-index:1;
    font-family:var(--font-head); font-weight:800;
    font-size:clamp(1.7rem, 3.4vw, 2.4rem);
    margin:0 0 0.9rem; letter-spacing:-0.01em;
  }
  .cta-band p{ position:relative; z-index:1; color:rgba(255,255,255,0.85); margin:0 0 2rem; }
  .btn-primary-inverse{
    position:relative; z-index:1;
    display:inline-flex;
    font-family:var(--font-head);
    font-weight:700;
    font-size:0.94rem;
    text-decoration:none;
    color:var(--accent-2);
    background:#fff;
    padding:14px 32px;
    border-radius:999px;
    box-shadow:0 14px 30px rgba(0,0,0,0.2);
    transition:transform .18s ease;
  }
  .btn-primary-inverse:hover{ transform:translateY(-2px); }

  /* FOOTER */
  .site-footer{
    position:relative;
    overflow:hidden;
    background:linear-gradient(180deg, #2a1366, #1c0d47);
    text-align:center;
    padding:46px 24px 34px;
    color:rgba(255,255,255,0.65);
    font-size:0.85rem;
  }
  .site-footer::before{
    content:'';
    position:absolute; inset:0;
    background:radial-gradient(480px 260px at 50% 0%, rgba(108,99,255,0.35), transparent 72%);
    pointer-events:none;
  }
  .footer-inner{ position:relative; z-index:1; }
  .footer-brand{
    font-family:var(--font-head);
    font-weight:700;
    font-size:1.05rem;
    color:#fff;
    margin:0 0 6px;
    letter-spacing:-0.01em;
  }
  .site-footer p{ position:relative; z-index:1; margin:0; }

  @media (prefers-reduced-motion: reduce){
    *, *::before, *::after{
      transition-duration:0.01ms !important;
      animation-duration:0.01ms !important;
    }
  }

  /* Stagger cascade */
  .reveal{ opacity:0; transform:translateY(18px); transition:opacity .7s ease, transform .7s ease; }
  .reveal.in{ opacity:1; transform:translateY(0); }
</style>
</head>
<body>

<div class="top-accent-bar"></div>

<nav class="nav">
  <div class="nav-inner">
    <a href="home.php" class="brand">
      <img src="logo.png" alt="EasyResume logo" onerror="this.style.display='none'">
      Easy<span class="accent-text">Resume</span>
    </a>
    <div class="nav-links">
      <a href="home.php" class="active">Home</a>
      <a href="templates.php">Templates</a>
      <a href="ai_tools.php" class="nav-ai">✦ AI Tools</a>
      <a href="faq.php">FAQ</a>
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
    <button class="hamburger" id="hamburgerBtn" aria-label="Open menu">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>

<div class="mobile-nav" id="mobileNav">
  <a href="home.php">Home</a>
  <a href="templates.php">Templates</a>
  <a href="ai_tools.php">✦ AI Tools</a>
  <a href="faq.php">FAQ</a>
  <div class="mobile-auth">
<?php if (!empty($_SESSION['user_id'])): ?>
    <a href="template-selection.php" class="btn btn-ghost btn-sm">Dashboard</a>
    <a href="logout.php" class="btn btn-primary btn-sm">Logout</a>
<?php else: ?>
    <a href="login.php" class="btn btn-ghost btn-sm">Login</a>
    <a href="signup.php" class="btn btn-primary btn-sm">Sign Up</a>
<?php endif; ?>
  </div>
</div>

<!-- HERO -->
<header class="hero">
  <div class="hero-grid">
    <div class="hero-copy reveal in">
      <span class="eyebrow">✦ AI-Powered Resume Platform</span>
      <h1>Build <span class="grad">ATS-Optimized</span> Resumes That Get Interviews</h1>
      <p>One platform for resume building, ATS optimization, AI content improvement, job-match analysis, and cover letter generation — all in one clean workflow.</p>
      <div class="hero-actions">
        <a href="signup.php" class="btn btn-primary">Create Resume Free</a>
        <a href="templates.php" class="btn btn-ghost">Explore Templates</a>
      </div>
      <div class="trust-row">
        <span><span class="tick">✓</span> ATS Friendly</span>
        <span><span class="tick">✓</span> AI Powered</span>
        <span><span class="tick">✓</span> Pro Templates</span>
        <span><span class="tick">✓</span> PDF Export</span>
      </div>
    </div>

    <div class="hero-visual reveal in">
      <div class="dash-glow"></div>

      <div class="dash-main">
        <div class="dash-head">
          <strong>Resume Intelligence</strong>
          <span class="live"><span class="pulse"></span> Live</span>
        </div>
        <div class="skel-line w-80"></div>
        <div class="skel-line w-60"></div>
        <div class="skel-line w-95"></div>
        <div class="skel-line w-40"></div>
        <div class="dash-skills">
          <span>React</span>
          <span>SQL</span>
          <span>Leadership</span>
          <span>Python</span>
        </div>
      </div>

      <div class="float-card card-ats">
        <div class="ring"><span>92</span></div>
        <p>ATS Score</p>
      </div>

      <div class="float-card card-match">
        <div class="pct">87%</div>
        <div class="lbl">Job<br>Match</div>
      </div>

      <div class="float-card card-suggest">
        <span class="tag">✦ AI Suggestion</span>
        <p>Add a quantifiable result to your last role to boost impact.</p>
      </div>
    </div>
  </div>
</header>

<!-- TRUST METRICS -->
<section class="trust-band">
  <div class="trust-grid">
    <div class="reveal">
      <div class="stat-num" data-count-target="50000" data-count-suffix="+">0</div>
      <div class="stat-lbl">Resumes Created</div>
    </div>
    <div class="reveal">
      <div class="stat-num" data-count-target="95" data-count-suffix="%">0</div>
      <div class="stat-lbl">ATS Compatibility</div>
    </div>
    <div class="reveal">
      <div class="stat-num" data-count-target="4.9" data-count-suffix="/5">0</div>
      <div class="stat-lbl">User Satisfaction</div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="features">
  <div class="sec-head reveal">
    <span class="eyebrow">What You Get</span>
    <h2>Everything you need to get hired faster.</h2>
    <p>A complete resume toolkit — not just a template.</p>
  </div>
  <div class="feature-grid">

    <div class="feature-card reveal">
      <div class="feature-icon">✓</div>
      <h3>ATS Resume Checker</h3>
      <p>Scan your resume against real ATS parsing logic and fix what's blocking you before recruiters ever see it.</p>
    </div>

    <div class="feature-card reveal">
      <div class="feature-icon">✦</div>
      <h3>AI Resume Optimization</h3>
      <p>Get line-by-line suggestions that sharpen your wording, tone, and impact — instantly, as you write.</p>
    </div>

    <div class="feature-card reveal">
      <div class="feature-icon">✉</div>
      <h3>Cover Letter Generator</h3>
      <p>Generate a tailored cover letter that matches your resume and the job you're applying for, in seconds.</p>
    </div>

    <div class="feature-card reveal">
      <div class="feature-icon">⌖</div>
      <h3>Job Match Analysis</h3>
      <p>Paste a job description and see exactly how well you match — plus what keywords you're missing.</p>
    </div>

    <div class="feature-card reveal">
      <div class="feature-icon">▦</div>
      <h3>Resume Templates</h3>
      <p>Choose from professional, ATS-friendly templates designed for every industry and experience level.</p>
    </div>

    <div class="feature-card reveal">
      <div class="feature-icon">⇩</div>
      <h3>Instant PDF Export</h3>
      <p>Download a polished, recruiter-ready PDF the moment you're done — no formatting headaches.</p>
    </div>

  </div>
</section>

<!-- AI INTELLIGENCE SECTION -->
<section class="ai-section">
  <div class="ai-glow"></div>
  <div class="ai-grid">
    <div class="ai-copy reveal">
      <span class="eyebrow">AI Resume Intelligence</span>
      <h2>Your resume, scored and improved by AI.</h2>
      <p>EasyResume reads your resume the way an ATS and a recruiter both do — then tells you exactly what to fix.</p>
      <ul class="ai-list">
        <li><span class="ico">✓</span> Full resume analysis in seconds</li>
        <li><span class="ico">✓</span> Keyword matching against real job descriptions</li>
        <li><span class="ico">✓</span> Skill gap detection with specific recommendations</li>
        <li><span class="ico">✓</span> Automatic ATS formatting optimization</li>
        <li><span class="ico">✓</span> Transparent resume scoring you can act on</li>
      </ul>
      <a href="ai_tools.php" class="btn btn-primary">Open AI Tools</a>
    </div>

    <div class="ai-dashboard reveal">
      <div class="ai-dash-row">
        <div class="ai-widget">
          <div class="w-label">ATS Score</div>
          <div class="w-big">92/100</div>
          <div class="w-bar"><i style="width:92%"></i></div>
        </div>
        <div class="ai-widget">
          <div class="w-label">Job Match</div>
          <div class="w-big">87%</div>
          <div class="w-bar"><i style="width:87%"></i></div>
        </div>
        <div class="ai-widget full">
          <div class="w-label">Keyword Matching</div>
          <span class="kw">✓ Project Management</span>
          <span class="kw">✓ SQL</span>
          <span class="kw">✓ Stakeholder Communication</span>
          <span class="kw miss">✗ Agile</span>
          <span class="kw miss">✗ CI/CD</span>
        </div>
        <div class="ai-widget full">
          <div class="w-label">AI Suggestion</div>
          <p style="margin:0;font-size:0.85rem;color:var(--ink);font-weight:500;line-height:1.55;">Quantify your achievements — e.g. "Reduced onboarding time by 30% across 4 teams" instead of "Improved onboarding process."</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- TEMPLATE SHOWCASE -->
<section class="templates-section">
  <div class="sec-head reveal">
    <span class="eyebrow">Templates</span>
    <h2>Templates for every career stage.</h2>
    <p>ATS-friendly designs for engineers, executives, freshers, and creatives alike.</p>
  </div>

  <div class="tmpl-tabs">
    <span>ATS Friendly</span>
    <span>Software Engineer</span>
    <span>Executive</span>
    <span>Fresher</span>
    <span>Creative</span>
  </div>
</section>

<!-- FINAL CTA -->
<section class="cta-band">
  <h2>Stop Guessing. Start Getting Interviews.</h2>
  <p>Join thousands of job seekers using AI to build resumes that actually get read.</p>
  <a href="signup.php" class="btn-primary-inverse">Build My Resume Free</a>
</section>

<footer class="site-footer">
  <div class="footer-inner">
    <p class="footer-brand">EasyResume</p>
    <p>&copy; <?php echo date('Y'); ?> EasyResume. All rights reserved.</p>
  </div>
</footer>

<script>
  /* Hamburger toggle */
  (function() {
    const btn = document.getElementById('hamburgerBtn');
    const nav = document.getElementById('mobileNav');
    if (btn && nav) {
      btn.addEventListener('click', function() {
        btn.classList.toggle('is-open');
        nav.classList.toggle('is-open');
        document.body.style.overflow = nav.classList.contains('is-open') ? 'hidden' : '';
      });
      nav.querySelectorAll('a').forEach(function(a) {
        a.addEventListener('click', function() {
          btn.classList.remove('is-open');
          nav.classList.remove('is-open');
          document.body.style.overflow = '';
        });
      });
    }
  })();

  /* Scroll reveal with stagger */
  const revealEls = document.querySelectorAll('.reveal');
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('in');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.15 });
  revealEls.forEach((el, i) => {
    el.style.transitionDelay = (i * 80) + 'ms';
    observer.observe(el);
  });

  /* Animated stat counters */
  (function() {
    const counters = document.querySelectorAll('[data-count-target]');
    if (!counters.length) return;
    const countObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        const el = entry.target;
        const raw = el.dataset.countTarget;
        const suffix = el.dataset.countSuffix || '';
        const prefix = el.dataset.countPrefix || '';
        const target = parseFloat(raw);
        const isDecimal = raw.includes('.');
        const duration = 1600;
        const start = performance.now();

        function tick(now) {
          const elapsed = now - start;
          const progress = Math.min(elapsed / duration, 1);
          const eased = 1 - Math.pow(1 - progress, 3); // ease-out cubic
          const current = eased * target;
          el.textContent = prefix + (isDecimal ? current.toFixed(1) : Math.round(current).toLocaleString()) + suffix;
          if (progress < 1) requestAnimationFrame(tick);
        }
        requestAnimationFrame(tick);
        countObserver.unobserve(el);
      });
    }, { threshold: 0.5 });
    counters.forEach(el => countObserver.observe(el));
  })();
</script>

</body>
</html>