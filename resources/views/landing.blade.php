<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>NaturasiMIS - Smart Inventory & Production Monitoring</title>
  <link rel="icon" type="image/png" href="{{ asset('assets/images/naturasi-mis-logo.png') }}" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet" />
  <link rel="stylesheet" href="{{ asset('assets/css/landing.css') }}" />
</head>
<body class="landing-body">

  <header class="top-nav">
    <div class="nav-logo">
      <img src="{{ asset('assets/images/naturasi-mis-logo.png') }}" alt="Naturasi Logo" />
      <h1>Naturasi<span>MIS</span></h1>
    </div>
    <nav>
      <ul class="nav-menu">
        <li><a href="#home" class="nav-link active">Home</a></li>
        <li><a href="#about" class="nav-link">About</a></li>
        <li><a href="#benefits" class="nav-link">Benefits</a></li>
        <li><a href="{{ route('login') }}" class="nav-link btn-nav">Login</a></li>
      </ul>
    </nav>
    <div class="hamburger"><i class="ri-menu-line"></i></div>
  </header>

  <section class="hero" id="home">
    <div class="hero-content">
      <h1 class="hero-title">Smart Production & Inventory Monitoring</h1>
      <p class="hero-subtitle">
        Revolutionize cheese manufacturing efficiency with NaturasiMIS — your digital solution for
        automated tracking, predictive restocking, and real-time production insights.
      </p>
      <div class="hero-cta">
        <a href="{{ route('login') }}" class="btn btn-primary">Get Started</a>
        <a href="#about" class="btn btn-secondary learn-more-btn">Learn More</a>
      </div>
    </div>
  </section>

  <section id="about" class="about container">
    <h2 class="section-title">About NaturasiMIS</h2>
    <p class="section-subtitle">
      NaturasiMIS is a digital management system designed to streamline cheese production operations,
      automate inventory tracking, and enhance efficiency through predictive restocking and batch-level monitoring.
    </p>
    <div class="features-grid">
      <div class="feature-card"><i class="ri-box-3-line"></i><h3>Inventory Tracking</h3><p>Monitor raw materials and finished goods in real time.</p></div>
      <div class="feature-card"><i class="ri-settings-4-line"></i><h3>Production Monitoring</h3><p>Record and track daily outputs and batches efficiently.</p></div>
      <div class="feature-card"><i class="ri-notification-3-line"></i><h3>Predictive Restocking</h3><p>Receive alerts before inventory runs out.</p></div>
      <div class="feature-card"><i class="ri-bar-chart-box-line"></i><h3>Batch Tracking</h3><p>Trace batches for quality assurance.</p></div>
      <div class="feature-card"><i class="ri-user-settings-line"></i><h3>User Management</h3><p>Secure multi-role access for admins, staff, and managers.</p></div>
    </div>
  </section>

  <section id="benefits" class="benefits">
    <div class="container">
      <h2 class="section-title">Why Choose NaturasiMIS?</h2>
      <p class="section-subtitle">Designed to help cheese manufacturers save time, reduce errors, and make data-driven decisions.</p>
      <div class="features-grid">
        <div class="feature-card"><i class="ri-error-warning-line"></i><h3>Reduces Human Error</h3><p>Automated tracking minimizes manual mistakes.</p></div>
        <div class="feature-card"><i class="ri-timer-flash-line"></i><h3>Saves Time</h3><p>Streamlined digital workflows mean faster operations.</p></div>
        <div class="feature-card"><i class="ri-database-2-line"></i><h3>Accurate Records</h3><p>All data stored safely in real-time.</p></div>
        <div class="feature-card"><i class="ri-bar-chart-line"></i><h3>Data-Driven Insights</h3><p>Visualize trends and make smarter decisions.</p></div>
        <div class="feature-card"><i class="ri-shield-check-line"></i><h3>Consistent Quality</h3><p>Batch monitoring ensures every product meets your standard.</p></div>
      </div>
    </div>
  </section>

  <footer class="footer">
    <p>© {{ date('Y') }} NaturasiMIS – Developed for Naturasi Cheese Manufacturing</p>
    <ul class="footer-links">
      <li><a href="#">Privacy Policy</a></li>
      <li><a href="#">Terms of Use</a></li>
    </ul>
  </footer>

  <button class="back-to-top"><i class="ri-arrow-up-s-line"></i></button>
  <script src="{{ asset('assets/js/landing.js') }}"></script>
</body>
</html>
