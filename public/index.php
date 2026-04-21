<?php
require_once __DIR__ . '../../includes/bootstrap.php';

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    header('Location: ' . APP_URL . 'dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= APP_NAME ?> — Secure Authentication</title>
  <link rel="stylesheet" href="../assets/css/main.css">
  <style>
    /* ── Landing-specific styles ─────────────────────────────── */
    .landing {
      position: relative; z-index: 1;
      min-height: 100vh;
      display: flex; flex-direction: column;
    }

    /* Nav */
    nav {
      display: flex; align-items: center; justify-content: space-between;
      padding: 24px 48px;
      border-bottom: 1px solid rgba(255,255,255,0.05);
    }
    .nav-logo { display: flex; align-items: center; gap: 12px; text-decoration: none; }
    .nav-logo-icon {
      width: 38px; height: 38px;
      background: linear-gradient(135deg,#6c63ff,#3ecfcf);
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 18px;
    }
    .nav-logo-text {
      font-family: 'Syne', sans-serif; font-size: 18px; font-weight: 700;
      background: linear-gradient(90deg,#fff,#a0a0d0);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    }
    .nav-links { display: flex; gap: 12px; }
    .nav-btn {
      padding: 10px 22px;
      border-radius: 50px;
      font-family: 'DM Sans', sans-serif; font-size: 14px; font-weight: 500;
      text-decoration: none; transition: all 0.2s;
    }
    .nav-btn-ghost { color: var(--text-muted); border: 1px solid var(--border); }
    .nav-btn-ghost:hover { color: var(--text); border-color: rgba(255,255,255,0.2); }
    .nav-btn-primary { background: var(--primary); color: #fff; }
    .nav-btn-primary:hover { background: #5a52e0; }

    /* Hero */
    .hero {
      flex: 1;
      display: flex; flex-direction: column; align-items: center; justify-content: center;
      text-align: center;
      padding: 80px 24px;
    }
    .hero-badge {
      display: inline-flex; align-items: center; gap: 8px;
      background: rgba(108,99,255,0.12);
      border: 1px solid rgba(108,99,255,0.25);
      border-radius: 50px; padding: 8px 18px;
      font-size: 13px; color: #9b94ff;
      margin-bottom: 36px;
      animation: fadeUp 0.6s ease both 0.1s;
    }
    .hero-badge-dot {
      width: 6px; height: 6px; border-radius: 50%;
      background: var(--primary-2);
      animation: pulse 2s ease infinite;
    }
    @keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:0.5;transform:scale(0.8)} }

    .hero h1 {
      font-family: 'Syne', sans-serif;
      font-size: clamp(44px, 7vw, 80px);
      font-weight: 800; line-height: 1.05;
      color: #fff; max-width: 800px;
      margin-bottom: 24px;
      animation: fadeUp 0.6s ease both 0.2s;
    }
    .hero h1 span {
      background: linear-gradient(135deg, var(--primary), var(--primary-2));
      -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    }
    .hero p {
      font-size: 18px; color: var(--text-muted);
      max-width: 520px; line-height: 1.7;
      margin-bottom: 48px;
      animation: fadeUp 0.6s ease both 0.3s;
    }
    .hero-ctas {
      display: flex; gap: 14px; flex-wrap: wrap; justify-content: center;
      animation: fadeUp 0.6s ease both 0.4s;
    }
    .hero-btn {
      padding: 16px 36px; border-radius: 50px;
      font-family: 'DM Sans', sans-serif; font-size: 16px; font-weight: 600;
      text-decoration: none; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px;
    }
    .hero-btn-primary {
      background: linear-gradient(135deg, var(--primary), #4f46e5);
      color: #fff;
      box-shadow: 0 8px 40px rgba(108,99,255,0.4);
    }
    .hero-btn-primary:hover { box-shadow: 0 12px 50px rgba(108,99,255,0.55); transform: translateY(-2px); }
    .hero-btn-secondary {
      border: 1px solid var(--border); color: var(--text-muted);
    }
    .hero-btn-secondary:hover { border-color: rgba(255,255,255,0.25); color: var(--text); }

    /* Features */
    .features {
      display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 20px; max-width: 900px; margin: 80px auto 0;
      padding: 0 24px;
      animation: fadeUp 0.6s ease both 0.5s;
    }
    .feature-card {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 28px 24px;
      transition: border-color 0.2s, transform 0.2s;
    }
    .feature-card:hover { border-color: rgba(108,99,255,0.3); transform: translateY(-4px); }
    .feature-icon {
      width: 44px; height: 44px; border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      font-size: 20px; margin-bottom: 16px;
    }
    .feature-card h3 { font-family: 'Syne',sans-serif; font-size: 16px; font-weight: 700; color:#fff; margin-bottom: 8px; }
    .feature-card p  { font-size: 14px; color: var(--text-muted); line-height: 1.6; }

    /* Footer */
    footer {
      text-align: center; padding: 40px;
      border-top: 1px solid var(--border);
      margin-top: 80px;
      font-size: 13px; color: var(--text-muted);
    }
    footer a { color: var(--primary); text-decoration: none; }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(20px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 600px) {
      nav { padding: 20px 24px; }
      .nav-logo-text { display: none; }
    }
  </style>
</head>
<body>
  <div class="blob blob-1"></div>
  <div class="blob blob-2"></div>

  <div class="landing">
    <!-- Nav -->
    <nav>
      <a class="nav-logo" href="#">
        <div class="nav-logo-icon">⚡</div>
        <span class="nav-logo-text"><?= APP_NAME ?></span>
      </a>
      <div class="nav-links">
        <a href="login.php" class="nav-btn nav-btn-ghost">Sign In</a>
        <a href="register.php" class="nav-btn nav-btn-primary">Get Started</a>
      </div>
    </nav>

    <!-- Hero -->
    <section class="hero">
      <div class="hero-badge">
        <span class="hero-badge-dot"></span>
        Fully Featured · Production Ready
      </div>

      <h1>Auth done <span>right</span>,<br>from day one.</h1>

      <p>A complete, secure authentication system with registration, email verification, login, and OTP-based password recovery — ready to drop into any PHP project.</p>

      <div class="hero-ctas">
        <a href="pages/register.php" class="hero-btn hero-btn-primary">
          Create Account →
        </a>
        <a href="pages/login.php" class="hero-btn hero-btn-secondary">
          Sign In
        </a>
      </div>

      <!-- Feature cards -->
      <div class="features">
        <div class="feature-card">
          <div class="feature-icon" style="background:rgba(108,99,255,0.15);">🔐</div>
          <h3>Secure by default</h3>
          <p>Bcrypt hashing, CSRF protection, brute-force lockouts and secure sessions.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon" style="background:rgba(62,207,207,0.15);">📧</div>
          <h3>Email verification</h3>
          <p>New accounts receive a signed verification link before access is granted.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon" style="background:rgba(255,92,108,0.15);">🔑</div>
          <h3>OTP password reset</h3>
          <p>6-digit time-limited codes delivered to the user's inbox — no guesswork.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon" style="background:rgba(255,200,60,0.15);">⚡</div>
          <h3>Zero dependencies</h3>
          <p>Pure PHP + PDO. No Composer required. Drop it in, configure, and go.</p>
        </div>
      </div>
    </section>

    <footer>
      Built with care · <?= APP_NAME ?> · <a href="pages/login.php">Sign in</a> · <a href="pages/register.php">Register</a>
    </footer>
  </div>
</body>
</html>