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
  <body class="theme-{{ auth()->user()->theme ?? 'default' }} lang-{{ auth()->user()->language ?? 'en' }}">

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

          @if(in_array('reports', $allowed))
          <li><a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <i class="fas fa-chart-line"></i> Reports</a></li>
          @endif
          @if(in_array('orders', $allowed))
          <li><a href="{{ route('orders.index') }}" class="{{ request()->routeIs('orders.*') ? 'active' : '' }}">
            <i class="fas fa-clipboard-list"></i> Orders</a></li>
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

            {{-- Only show header search on Dashboard --}}
            @if(request()->routeIs('dashboard'))
            <form action="{{ route('search') }}" method="GET" style="display:inline;position:relative;">
              <input type="text" name="q" class="search-bar" placeholder="Search..."
                value="{{ request('q') }}"
                onkeypress="if(event.key==='Enter'){this.form.submit()}"
                style="padding-right:2.5rem;" />
              <button type="submit" style="position:absolute;right:0.6rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#888;">
                <i class="fas fa-search"></i>
              </button>
            </form>
            @endif

            <div class="admin-menu">
              <div class="admin-trigger" id="adminTrigger">
                @if(auth()->user()->profile_picture)
                  <img src="{{ asset('storage/' . auth()->user()->profile_picture) }}" alt="Avatar" style="width:32px;height:32px;border-radius:50%;object-fit:cover;">
                @else
                  <div style="width:32px;height:32px;border-radius:50%;background:#e0e0e0;display:flex;align-items:center;justify-content:center;">
                 <i class="fas fa-user no-rotate" style="color:#aaa;font-size:.9rem;"></i>
                  </div>
                @endif
                <span>{{ auth()->user()->username }}</span>
              <i class="fas fa-chevron-down" id="adminChevron"></i>
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
        <div class="success-message" id="globalSuccess">
          <i class="fas fa-check-circle"></i>
          <div>{{ session('success') }}</div>
        </div>
      @endif
   @if($errors->any())
        <div class="alert-message" id="globalError">
          <i class="fas fa-exclamation-circle"></i>
          <div>{{ $errors->first() }}</div>
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
        <form action="{{ route('logout') }}" method="POST" style="display:inline;" id="logoutForm">
          @csrf
          <button type="submit" class="confirm-btn">Log Out</button>
        </form>
      </div>
    </div>
  </div>

  {{-- ACCOUNT SETTINGS MODAL --}}
  <div id="accountSettingsModal" class="modal">
    <div class="modal-content account-settings-modal" style="max-height:90vh;overflow-y:auto;scrollbar-width:none;-ms-overflow-style:none;">
      <style>.account-settings-modal::-webkit-scrollbar{display:none;}</style>

      <h2><i class="fas fa-user-cog"></i> Account Settings</h2>

      <div class="profile-section">
        <div class="profile-picture-wrapper">
          @if(auth()->user()->profile_picture)
            <img id="profilePreview" src="{{ asset('storage/' . auth()->user()->profile_picture) }}" class="profile-picture">
          @else
            <div id="profilePlaceholder" style="width:100px;height:100px;border-radius:50%;background:#e8e8e8;display:flex;align-items:center;justify-content:center;margin:0 auto;">
              <i class="fas fa-user" style="font-size:2.8rem;color:#bbb;margin-top:.8rem;"></i>
            </div>
            <img id="profilePreview" src="" class="profile-picture" style="display:none;">
          @endif
          <label for="profilePictureInput" class="profile-camera-btn"><i class="fas fa-camera"></i></label>
        </div>
        <h3 class="profile-username">{{ auth()->user()->username }}</h3>
        <p class="profile-role">{{ ucfirst(auth()->user()->role) }}</p>
      </div>

      <form action="{{ route('account.update') }}" method="POST" enctype="multipart/form-data" class="form-grid">
        @csrf
        <input type="file" id="profilePictureInput" name="profile_picture" accept="image/*" class="profile-input-hidden">

        <div class="form-group">
          <label><i class="fas fa-user"></i> Username</label>
          <input type="text" name="username" value="{{ auth()->user()->username }}" required />
        </div>

        <div class="form-group">
          <label><i class="fas fa-envelope"></i> Email Address</label>
          <input type="email" name="email" value="{{ auth()->user()->email }}" />
        </div>

        <div class="form-group">
          <label><i class="fas fa-palette"></i> Theme</label>
          <select name="theme">
            <option value="light"   {{ auth()->user()->theme === 'light'   ? 'selected' : '' }}>Light Mode</option>
            <option value="dark"    {{ auth()->user()->theme === 'dark'    ? 'selected' : '' }}>Dark Mode</option>
          </select>
        </div>

        <div class="form-group">
          <label><i class="fas fa-globe"></i> Language</label>
          <select name="language">
            <option value="en" {{ auth()->user()->language === 'en' ? 'selected' : '' }}>English</option>
            <option value="it" {{ auth()->user()->language === 'it' ? 'selected' : '' }}>Italian</option>
          </select>
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

  {{-- MODALS FROM CHILD VIEWS --}}
  @stack('modals')

  {{-- SCRIPTS --}}
  <script src="{{ asset('assets/js/dashboard.js') }}"></script>

  <script>
  document.getElementById('profilePictureInput')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function(e) {
        const preview = document.getElementById('profilePreview');
        const placeholder = document.getElementById('profilePlaceholder');
        if (preview) { preview.src = e.target.result; preview.style.display = 'block'; }
        if (placeholder) placeholder.style.display = 'none';
      };
      reader.readAsDataURL(file);
    }
  });
  </script>

  <script>
    document.querySelectorAll('.sidebar nav ul li a').forEach(link => {
      link.addEventListener('click', function(e) {
        const href = this.href;
        if (href && !href.includes('#') && href !== window.location.href) {
          e.preventDefault();
          document.body.classList.add('page-transitioning');
          setTimeout(() => { window.location.href = href; }, 200);
        }
      });
    });
  </script>
<script>
    setTimeout(() => document.getElementById('globalSuccess')?.remove(), 4000);
    setTimeout(() => document.getElementById('globalError')?.remove(), 5000);
  </script>
  @stack('scripts')

  </body>
  </html>