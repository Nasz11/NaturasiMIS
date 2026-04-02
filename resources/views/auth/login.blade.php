<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>NaturasiMIS | Login</title>
  <link rel="icon" type="image/png" href="{{ asset('assets/Images/Naturasi-MIS-Logo.png') }}" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet" />
  <link rel="stylesheet" href="{{ asset('assets/css/login_page.css') }}" />
</head>
<body>
<div class="login-container">

  <!-- LEFT HERO PANEL -->
  <section class="login-hero">
    <div class="overlay"></div>
    <div class="hero-content">
      <img src="{{ asset('assets/Images/Naturasi-MIS-Logo.png') }}" alt="NaturasiMIS" class="hero-logo">
      <h1>Welcome to <span>NaturasiMIS</span></h1>
      <p>Smart Production & Inventory Monitoring<br>for Cheese Manufacturers</p>
    </div>
  </section>

  <!-- RIGHT LOGIN PANEL -->
  <section class="login-panel">
    <div class="login-card">
      <h2>Sign in to your account</h2>

      <form action="{{ route('login.post') }}" method="POST">
        @csrf
        <div class="input-group">
          <i class="ri-user-line"></i>
          <input type="text" name="username" placeholder="Username"
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
    this.classList.replace('ri-eye-line', 'ri-eye-off-line');
  }
});
</script>
</body>

</html>
