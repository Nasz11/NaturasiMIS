<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>LactoFlow | Login</title>
  <link rel="icon" type="image/png" href="{{ asset('assets/images/naturasi-mis-logo.png') }}" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet" />
  <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}" />
  <style>
    .demo-accounts {
      margin-top: 1.5rem;
      padding-top: 1.25rem;
      border-top: 1px dashed #e2e8f0;
      text-align: center;
    }
    .demo-accounts p {
      font-size: 0.8rem;
      font-weight: 600;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin-bottom: 0.75rem;
    }
    .demo-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 0.5rem;
    }
    .demo-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.35rem;
      padding: 0.5rem 0.6rem;
      background: #f8fafc;
      border: 1px solid #cbd5e1;
      border-radius: 0.375rem;
      font-size: 0.8rem;
      font-weight: 500;
      color: #334155;
      cursor: pointer;
      transition: all 0.15s ease-in-out;
    }
    .demo-btn:hover {
      background: #eff6ff;
      border-color: #3b82f6;
      color: #1d4ed8;
      transform: translateY(-1px);
    }
  </style>
</head>
<body>
<div class="login-container">

  <!-- LEFT HERO PANEL -->
  <section class="login-hero">
    <div class="overlay"></div>
    <div class="hero-content">
      <img src="{{ asset('assets/images/naturasi-mis-logo.png') }}" alt="LactoFlow" class="hero-logo">
      <h1>Welcome to <span>LactoFlow</span></h1>
      <p>Smart Production & Inventory Monitoring<br>for Cheese Manufacturers</p>
    </div>
  </section>

  <!-- RIGHT LOGIN PANEL -->
  <section class="login-panel">
    <div class="login-card">
      <h2>Sign in to your account</h2>

      <form id="loginForm" action="{{ route('login.post') }}" method="POST">
        @csrf
        <div class="input-group">
          <i class="ri-user-line"></i>
          <input type="text" id="usernameInput" name="username" placeholder="Username"
            value="{{ old('username') }}" required autofocus>
        </div>

        <div class="input-group">
          <i class="ri-lock-2-line"></i>
          <input type="password" name="password" id="passwordInput" placeholder="Password" required>
          <i class="ri-eye-off-line" id="togglePassword" style="cursor:pointer; position:absolute; right:1rem; color:#999;"></i>
        </div>

        <div class="options">
          <label>
            <input type="checkbox" name="remember"> Remember me
          </label>
        </div>

        <button type="submit" class="btn-primary">Log In</button>

        @if($errors->any())
          <div class="feedback error">❌ {{ $errors->first() }}</div>
        @endif

        <!-- QUICK DEMO ACCESS FOR PORTFOLIO REVIEWERS -->
        <div class="demo-accounts">
          <p><i class="ri-flashlight-line"></i> Quick Demo Access (1-Click)</p>
          <div class="demo-grid">
            <button type="button" class="demo-btn" onclick="quickLogin('admin', 'admin123')">
              👑 <span>Admin</span>
            </button>
            <button type="button" class="demo-btn" onclick="quickLogin('production', 'prod123')">
              🧀 <span>Production</span>
            </button>
            <button type="button" class="demo-btn" onclick="quickLogin('inventory', 'inv123')">
              📦 <span>Inventory</span>
            </button>
            <button type="button" class="demo-btn" onclick="quickLogin('manager', 'mgr123')">
              📊 <span>Manager</span>
            </button>
          </div>
        </div>

        <div class="forgot-box">
          <p><i class="ri-information-line"></i> Forgot your password?<br>
          <span>Contact your System Administrator</span></p>
        </div>
      </form>
    </div>
  </section>
</div>
<script>
document.getElementById('togglePassword')?.addEventListener('click', function() {
  const input = document.getElementById('passwordInput');
  if (input.type === 'password') {
    input.type = 'text';
    this.classList.replace('ri-eye-off-line', 'ri-eye-line');
  } else {
    input.type = 'password';
    this.classList.replace('ri-line', 'ri-eye-off-line');
  }
});

function quickLogin(username, password) {
  document.getElementById('usernameInput').value = username;
  document.getElementById('passwordInput').value = password;
  document.getElementById('loginForm').submit();
}
</script>
</body>
</html>
