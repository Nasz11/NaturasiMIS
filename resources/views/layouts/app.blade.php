<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>NaturasiMIS - @yield('title', 'Dashboard')</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <link rel="icon" type="image/png" href="{{ asset('assets/Images/Naturasi-MIS-Logo.png') }}" />
  <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  @stack('styles')
</head>
<body>
<div class="container">

  {{-- SIDEBAR --}}
  <aside class="sidebar">
    <div class="logo">
      <img src="{{ asset('assets/Images/Naturasi-MIS-Logo.png') }}" alt="NaturasiMIS Logo" />
      <h2>NaturasiMIS</h2>
    </div>
    <nav>
      <ul>
        @php $allowed = auth()->user()->allowedPages(); @endphp

        @if(in_array('dashboard', $allowed))
        <li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
          <i class="fas fa-home"></i> Dashboard</a></li>
        @endif

        @if(in_array('inventory', $allowed))
        <li><a href="{{ route('inventory.index') }}" class="{{ request()->routeIs('inventory.*') ? 'active' : '' }}">
          <i class="fas fa-boxes"></i> Inventory</a></li>
        @endif

        @if(in_array('production', $allowed))
        <li><a href="{{ route('production.index') }}" class="{{ request()->routeIs('production.*') ? 'active' : '' }}">
          <i class="fas fa-industry"></i> Production</a></li>
        @endif

        @if(in_array('batches', $allowed))
        <li><a href="{{ route('batches.index') }}" class="{{ request()->routeIs('batches.*') ? 'active' : '' }}">
          <i class="fas fa-clipboard-list"></i> Batches</a></li>
        @endif

        @if(in_array('reports', $allowed))
        <li><a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">
          <i class="fas fa-chart-line"></i> Reports</a></li>
        @endif

        @if(in_array('users', $allowed))
        <li><a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
          <i class="fas fa-users"></i> Manage Users</a></li>
        @endif

        @if(in_array('settings', $allowed))
        <li><a href="{{ route('settings.index') }}" class="{{ request()->routeIs('settings.*') ? 'active' : '' }}">
          <i class="fas fa-cogs"></i> System Settings</a></li>
        @endif

        @if(in_array('logs', $allowed))
        <li><a href="{{ route('logs.index') }}" class="{{ request()->routeIs('logs.*') ? 'active' : '' }}">
          <i class="fas fa-list-alt"></i> Activity Logs</a></li>
        @endif
      </ul>
    </nav>
  </aside>

  {{-- MAIN CONTENT --}}
  <main class="main-content">
    <header class="header">
      <div class="header-left">
        <h1>@yield('page-title', 'Dashboard')</h1>
        <p>@yield('page-subtitle', '')</p>
      </div>
      <div class="header-right">
        <div class="search-profile">
     <form action="{{ route('search') }}" method="GET" style="display:inline;position:relative;">
  <input type="text" name="q" class="search-bar" placeholder="Search..."
    value="{{ request('q') }}"
    onkeypress="if(event.key==='Enter'){this.form.submit()}"
    style="padding-right:2.5rem;" />
  <button type="submit" style="position:absolute;right:0.6rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#888;">
    <i class="fas fa-search"></i>
  </button>
</form>
          <div class="admin-menu">
            <div class="admin-trigger" id="adminTrigger">
              @if(auth()->user()->profile_picture)
                <img src="{{ asset('storage/' . auth()->user()->profile_picture) }}" alt="Avatar">
              @else
                <img src="https://i.pravatar.cc/100?u={{ auth()->user()->id }}" alt="Avatar">
              @endif
              <span>{{ auth()->user()->username }}</span>
              <i class="fas fa-chevron-down"></i>
            </div>
            <ul class="dropdown" id="profileDropdown">
              <li id="accountSettingsBtn"><i class="fas fa-user-cog"></i> Account Settings</li>
              <li id="logoutBtn"><i class="fas fa-sign-out-alt"></i> Logout</li>
            </ul>
          </div>
        </div>
      </div>
    </header>

    {{-- Flash messages --}}
    @if(session('success'))
      <div class="floating-alert show" style="background:#1a6b47;">
        <div style="display:flex;align-items:center;gap:10px;">
          <strong>✔</strong><div>{{ session('success') }}</div>
        </div>
      </div>
    @endif
    @if($errors->any())
      <div class="floating-alert show" style="background:#c62828;">
        <div style="display:flex;align-items:center;gap:10px;">
          <strong>✖</strong>
          <div>{{ $errors->first() }}</div>
        </div>
      </div>
    @endif

    @yield('content')
  </main>
</div>

{{-- LOGOUT MODAL --}}
<div id="logoutModal" class="logout-modal">
  <div class="logout-content">
    <h3>Confirm Logout</h3>
    <p>Are you sure you want to log out of your account?</p>
    <div class="logout-buttons">
      <button id="cancelLogout" class="cancel-btn">Cancel</button>
      <form action="{{ route('logout') }}" method="POST" style="display:inline;">
        @csrf
        <button type="submit" class="confirm-btn">Log Out</button>
      </form>
    </div>
  </div>
</div>

{{-- ACCOUNT SETTINGS MODAL --}}
<div id="accountSettingsModal" class="modal">
  <div class="modal-content account-settings-modal">
    <h2><i class="fas fa-user-cog"></i> Account Settings</h2>
    <div class="profile-section">
      <div class="profile-picture-wrapper">
        @if(auth()->user()->profile_picture)
          <img id="profilePreview" src="{{ asset('storage/' . auth()->user()->profile_picture) }}" class="profile-picture">
        @else
          <img id="profilePreview" src="https://i.pravatar.cc/150?u={{ auth()->user()->id }}" class="profile-picture">
        @endif
        <label for="profilePictureInput" class="profile-camera-btn"><i class="fas fa-camera"></i></label>
        <input type="file" id="profilePictureInput" accept="image/*" class="profile-input-hidden">
      </div>
      <h3 class="profile-username">{{ auth()->user()->username }}</h3>
      <p class="profile-role">{{ ucfirst(auth()->user()->role) }}</p>
    </div>

    <form action="{{ route('account.update') }}" method="POST" enctype="multipart/form-data" class="form-grid">
      @csrf
      <div class="form-group">
        <label><i class="fas fa-user"></i> Username</label>
        <input type="text" name="username" value="{{ auth()->user()->username }}" required />
      </div>
      <div class="form-group">
        <label><i class="fas fa-envelope"></i> Email Address</label>
        <input type="email" name="email" value="{{ auth()->user()->email }}" />
      </div>
      <div class="form-group">
        <label><i class="fas fa-lock"></i> Current Password</label>
        <input type="password" name="current_password" placeholder="Required to change password" />
      </div>
      <div class="form-group">
        <label><i class="fas fa-key"></i> New Password</label>
        <input type="password" name="new_password" placeholder="Leave blank to keep current" />
      </div>
      <div class="form-group">
        <label><i class="fas fa-palette"></i> Theme</label>
        <select name="theme">
          <option value="default" {{ auth()->user()->theme === 'default' ? 'selected' : '' }}>Default (Green)</option>
          <option value="light"   {{ auth()->user()->theme === 'light'   ? 'selected' : '' }}>Light Mode</option>
          <option value="dark"    {{ auth()->user()->theme === 'dark'    ? 'selected' : '' }}>Dark Mode</option>
        </select>
      </div>
      <div class="form-group">
        <label><i class="fas fa-globe"></i> Language</label>
        <select name="language">
          <option value="en" {{ auth()->user()->language === 'en' ? 'selected' : '' }}>English</option>
          <option value="tl" {{ auth()->user()->language === 'tl' ? 'selected' : '' }}>Tagalog</option>
          <option value="es" {{ auth()->user()->language === 'es' ? 'selected' : '' }}>Español</option>
        </select>
      </div>
      <div class="form-group form-group-full">
        <label class="checkbox-label">
          <input type="checkbox" name="notifications_enabled" class="checkbox-input"
            {{ auth()->user()->notifications_enabled ? 'checked' : '' }}>
          <span><i class="fas fa-bell"></i> Enable Email Notifications</span>
        </label>
      </div>
      <div class="form-group form-group-full">
        <label class="checkbox-label">
          <input type="checkbox" name="two_factor_enabled" class="checkbox-input"
            {{ auth()->user()->two_factor_enabled ? 'checked' : '' }}>
          <span><i class="fas fa-shield-alt"></i> Enable Two-Factor Authentication</span>
        </label>
      </div>
      <div class="modal-buttons">
        <button type="button" id="closeAccountSettings" class="btn-cancel">
          <i class="fas fa-times"></i> Cancel
        </button>
        <button type="submit" class="btn-save">
          <i class="fas fa-save"></i> Save Changes
        </button>
      </div>
    </form>
  </div>
</div>

<script src="{{ asset('assets/js/dashboard.js') }}"></script>
@stack('scripts')

<script>
// Profile picture preview
document.getElementById('profilePictureInput')?.addEventListener('change', function(e) {
  const file = e.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = e => document.getElementById('profilePreview').src = e.target.result;
    reader.readAsDataURL(file);
  }
});

// Modal helpers
const openModal  = (m) => { m?.classList.add('active'); document.body.classList.add('modal-open'); };
const closeModal = (m) => { m?.classList.remove('active'); document.body.classList.remove('modal-open'); };

// Dropdown
document.getElementById('adminTrigger')?.addEventListener('click', (e) => {
  e.stopPropagation();
  document.getElementById('profileDropdown')?.classList.toggle('show');
});
document.addEventListener('click', () => document.getElementById('profileDropdown')?.classList.remove('show'));

// Logout modal
document.getElementById('logoutBtn')?.addEventListener('click', (e) => {
  e.stopPropagation();
  openModal(document.getElementById('logoutModal'));
  document.getElementById('profileDropdown')?.classList.remove('show');
});
document.getElementById('cancelLogout')?.addEventListener('click', () =>
  closeModal(document.getElementById('logoutModal')));

// Account Settings modal
document.getElementById('accountSettingsBtn')?.addEventListener('click', () => {
  openModal(document.getElementById('accountSettingsModal'));
  document.getElementById('profileDropdown')?.classList.remove('show');
});
document.getElementById('closeAccountSettings')?.addEventListener('click', () =>
  closeModal(document.getElementById('accountSettingsModal')));

// Auto-hide flash alerts
document.querySelectorAll('.floating-alert.show').forEach(el => {
  setTimeout(() => { el.classList.remove('show'); setTimeout(() => el.remove(), 300); }, 3300);
});
</script>
</body>
</html>
