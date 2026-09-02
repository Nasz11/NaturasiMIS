  @extends('layouts.app')
  @section('title', 'Manage Users')
  @section('page-title', 'Manage Users')
  @section('page-subtitle', 'Create, edit, and manage user accounts and roles.')

  @section('content')
  @section('suppressGlobalErrors', true)
  <section id="usersSection">

  @if($errors->has('delete'))
    <div id="errorAlert" style="background:#fdecea;color:#c62828;padding:12px 16px;border-radius:8px;margin-bottom:1rem;display:flex;align-items:center;gap:8px;">
      <i class="fas fa-exclamation-circle"></i> {{ $errors->first('delete') }}
    </div>
  @endif

    <div class="module-header">
      <h2><i class="fas fa-users"></i> User Management</h2>
      <div class="user-actions">
        <button class="btn-primary" id="openAddUser">
          <i class="fas fa-plus"></i> Add User
        </button>
      </div>
    </div>

    {{-- TABS --}}
    <div style="display:flex; gap:0.5rem; margin-bottom:1.2rem;">
      <button class="btn-tab active" id="tabActive" onclick="switchTab('active')">
        <i class="fas fa-users"></i> Active Users ({{ $users->count() }})
      </button>
      <button class="btn-tab" id="tabArchived" onclick="switchTab('archived')">
        <i class="fas fa-archive"></i> Archived Users ({{ $archivedUsers->count() }})
      </button>
    </div>

    {{-- ACTIVE USERS TABLE --}}
    <div id="activeTab" class="table-container">
      <table id="usersTable">
        <thead>
          <tr><th>Username</th><th>Role</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
          @forelse($users as $user)
          <tr>
            <td>{{ $user->username }}</td>
            <td>
              @php
                $roleStyles = [
                  'admin'      => 'background:#e8f5e9;color:#1a6b47;',
                  'manager'    => 'background:#e3f2fd;color:#1565c0;',
                  'inventory'  => 'background:#fff3e0;color:#e65100;',
                  'production' => 'background:#f3e5f5;color:#6a1b9a;',
                ];
                $style = $roleStyles[$user->role] ?? 'background:#f0f0f0;color:#555;';
                $label = ['admin'=>'Admin','manager'=>'Manager','inventory'=>'Inventory','production'=>'Production'][$user->role] ?? ucfirst($user->role);
              @endphp
              <span style="{{ $style }} padding:3px 10px; border-radius:99px; font-size:0.78rem; font-weight:600;">{{ $label }}</span>
            </td>
            <td><span class="status-tag {{ $user->status === 'Active' ? 'active' : 'inactive' }}">{{ $user->status }}</span></td>
            <td class="actions-col">
              <button class="action-btn edit-btn"
                data-id="{{ $user->id }}"
                data-username="{{ $user->username }}"
                data-role="{{ $user->role }}"
                data-status="{{ $user->status }}">
                <i class="fas fa-pen"></i>
              </button>
              @if($user->id !== auth()->id() && $user->role !== 'admin')
              <form action="{{ route('users.destroy', $user) }}" method="POST" class="delete-form d-inline">
                @csrf @method('DELETE')
                <button type="button" class="action-btn delete-btn" title="Archive User"><i class="fas fa-archive"></i></button>
              </form>
              @elseif($user->role === 'admin')
              <button class="action-btn" title="Cannot archive system administrator" style="color:#ccc;cursor:not-allowed;" disabled>
                <i class="fas fa-archive"></i>
              </button>
              @endif
            </td>
          </tr>
          @empty
          <tr><td colspan="4" style="text-align:center;">No active users found.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- ARCHIVED USERS TABLE --}}
    <div id="archivedTab" class="table-container" style="display:none;">
      <table>
        <thead>
          <tr><th>Username</th><th>Role</th><th>Archived On</th><th>Actions</th></tr>
        </thead>
        <tbody>
          @forelse($archivedUsers as $user)
          <tr>
            <td>{{ $user->username }}</td>
            <td>
              @php
                $roleStyles = [
                  'admin'      => 'background:#e8f5e9;color:#1a6b47;',
                  'manager'    => 'background:#e3f2fd;color:#1565c0;',
                  'inventory'  => 'background:#fff3e0;color:#e65100;',
                  'production' => 'background:#f3e5f5;color:#6a1b9a;',
                ];
                $style = $roleStyles[$user->role] ?? 'background:#f0f0f0;color:#555;';
                $label = ['admin'=>'Admin','manager'=>'Manager','inventory'=>'Inventory','production'=>'Production'][$user->role] ?? ucfirst($user->role);
              @endphp
              <span style="{{ $style }} padding:3px 10px; border-radius:99px; font-size:0.78rem; font-weight:600;">{{ $label }}</span>
            </td>
            <td>{{ $user->deleted_at->format('M d, Y') }}</td>
            <td class="actions-col">
              <button type="button" class="action-btn edit-btn restore-btn"
                title="Restore User"
                style="background:#e8f5e9; color:#1a6b47;"
                data-id="{{ $user->id }}">
                <i class="fas fa-undo"></i>
              </button>
            </td>
          </tr>
          @empty
          <tr><td colspan="4" style="text-align:center;">No archived users.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>
  @endsection

  @push('modals')

  {{-- ═══════════════════════════════════════════ --}}
  {{-- ADD USER MODAL                              --}}
  {{-- ═══════════════════════════════════════════ --}}
  <div id="addUserModal" class="modal">
    <div class="modal-content" style="max-width:580px;">
      <h2><i class="fas fa-user-plus" style="margin-right:0.5rem;"></i>Add New User</h2>

      <form action="{{ route('users.store') }}" method="POST" class="form-grid" id="addUserForm">
        @csrf

        {{-- Username --}}
        <div class="form-group">
          <label>Username <span style="color:#c62828;">*</span></label>
          <input type="text" name="username" id="add_username"
            placeholder="e.g. j_smith" required autocomplete="off"
            style="width:100%;" />
          <small id="add_username_msg" style="display:none; font-size:0.78rem;"></small>
          @if($errors->has('username'))
            <small style="color:#c62828;">{{ $errors->first('username') }}</small>
          @endif
        </div>

        {{-- Role --}}
        <div class="form-group">
          <label>Role <span style="color:#c62828;">*</span></label>
          <select name="role" id="add_role" required>
            <option value="" disabled selected>Select Role</option>
            <option value="admin">Admin</option>
            <option value="inventory">Inventory Staff</option>
            <option value="production">Production Staff</option>
            <option value="manager">Manager</option>
          </select>
          @if($errors->has('role'))
            <small style="color:#c62828;">{{ $errors->first('role') }}</small>
          @endif
        </div>

        {{-- Password --}}
        <div class="form-group" style="grid-column: span 2;">
          <label>Password <span style="color:#c62828;">*</span></label>
          <div style="position:relative;">
            <input type="password" name="password" id="add_password"
              placeholder="Enter password" required
              style="width:100%; padding-right:2.5rem;" />
            <button type="button" onclick="togglePassword('add_password', this)"
              style="position:absolute; right:0.75rem; top:50%; transform:translateY(-50%);
                    background:none; border:none; cursor:pointer; color:#888;">
              <i class="fas fa-eye"></i>
            </button>
          </div>

          {{-- Strength bar --}}
          <div id="add_strength_wrap" style="display:none; margin-top:0.5rem;">
            <div style="display:flex; align-items:center; gap:0.5rem;">
              <div style="flex:1; height:6px; background:#e0e0e0; border-radius:4px; overflow:hidden;">
                <div id="add_strength_bar" style="height:100%; width:0%; border-radius:4px; transition:all 0.3s;"></div>
              </div>
              <span id="add_strength_label" style="font-size:0.75rem; font-weight:600; min-width:48px;"></span>
            </div>
          </div>

          {{-- Rules checklist --}}
          <div id="add_password_hints" style="margin-top:0.5rem; font-size:0.78rem; display:none;">
            <div style="color:#666; font-weight:600; margin-bottom:0.3rem;">Password must include:</div>
            <div id="hint_add_len"   style="color:#c62828; margin-bottom:0.15rem;">✗ At least 8 characters</div>
            <div id="hint_add_lower" style="color:#c62828; margin-bottom:0.15rem;">✗ At least one lowercase letter</div>
            <div id="hint_add_upper" style="color:#c62828; margin-bottom:0.15rem;">✗ At least one uppercase letter</div>
            <div id="hint_add_num"   style="color:#c62828; margin-bottom:0.15rem;">✗ At least one number</div>
            <div id="hint_add_spec"  style="color:#c62828;">✗ At least one special character (@$!%*?&)</div>
          </div>

          @if($errors->has('password'))
            <small style="color:#c62828;">{{ $errors->first('password') }}</small>
          @endif
        </div>

        {{-- Confirm Password --}}
        <div class="form-group" style="grid-column: span 2;">
          <label>Confirm Password <span style="color:#c62828;">*</span></label>
          <div style="position:relative;">
            <input type="password" id="add_confirm_password"
              placeholder="Re-enter password" required
              style="width:100%; padding-right:2.5rem;" />
            <button type="button" onclick="togglePassword('add_confirm_password', this)"
              style="position:absolute; right:0.75rem; top:50%; transform:translateY(-50%);
                    background:none; border:none; cursor:pointer; color:#888;">
              <i class="fas fa-eye"></i>
            </button>
          </div>
          <small id="add_confirm_msg" style="display:none; font-size:0.78rem;"></small>
        </div>

        {{-- Status --}}
        <div class="form-group" style="grid-column: span 2;">
          <label>Status</label>
          <select name="status">
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
          </select>
        </div>

        <div class="modal-buttons" style="grid-column: span 2;">
          <button type="button" id="closeAddUser" class="btn-cancel">Cancel</button>
          <button type="submit" id="add_submit_btn" class="btn-save" disabled
            style="opacity:0.5; cursor:not-allowed;">
            <i class="fas fa-user-plus"></i> Add User
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════ --}}
  {{-- EDIT USER MODAL                             --}}
  {{-- ═══════════════════════════════════════════ --}}
  <div id="editUserModal" class="modal">
    <div class="modal-content" style="max-width:580px;">
      <h2><i class="fas fa-user-edit" style="margin-right:0.5rem;"></i>Edit User</h2>
      <form id="editUserForm" action="" method="POST" class="form-grid">
        @csrf @method('PUT')
        <input type="hidden" id="edit_user_id" name="edit_user_id" value="{{ old('edit_user_id') }}" />

        {{-- Username --}}
        <div class="form-group">
          <label>Username <span style="color:#c62828;">*</span></label>
          <input type="text" name="username" id="edit_username" required autocomplete="off" style="width:100%;" />
          <small id="edit_username_msg" style="display:none; font-size:0.78rem;"></small>
          @if($errors->has('username'))
            <small style="color:#c62828;">{{ $errors->first('username') }}</small>
          @endif
        </div>

        {{-- Role --}}
        <div class="form-group">
          <label>Role</label>
          <select name="role" id="edit_role">
            <option value="admin">Admin</option>
            <option value="inventory">Inventory Staff</option>
            <option value="production">Production Staff</option>
            <option value="manager">Manager</option>
          </select>
        </div>

        {{-- Password --}}
        <div class="form-group" style="grid-column: span 2;">
          <label>Password <small style="color:#666; font-weight:400;">(leave blank to keep current)</small></label>
          <div style="position:relative;">
            <input type="password" name="password" id="edit_password"
              placeholder="New password (optional)"
              style="width:100%; padding-right:2.5rem;" />
            <button type="button" onclick="togglePassword('edit_password', this)"
              style="position:absolute; right:0.75rem; top:50%; transform:translateY(-50%);
                    background:none; border:none; cursor:pointer; color:#888;">
              <i class="fas fa-eye"></i>
            </button>
          </div>

          {{-- Strength bar --}}
          <div id="edit_strength_wrap" style="display:none; margin-top:0.5rem;">
            <div style="display:flex; align-items:center; gap:0.5rem;">
              <div style="flex:1; height:6px; background:#e0e0e0; border-radius:4px; overflow:hidden;">
                <div id="edit_strength_bar" style="height:100%; width:0%; border-radius:4px; transition:all 0.3s;"></div>
              </div>
              <span id="edit_strength_label" style="font-size:0.75rem; font-weight:600; min-width:48px;"></span>
            </div>
          </div>

          {{-- Rules checklist --}}
          <div id="edit_password_hints" style="margin-top:0.5rem; font-size:0.78rem; display:none;">
            <div style="color:#666; font-weight:600; margin-bottom:0.3rem;">Password must include:</div>
            <div id="hint_edit_len"   style="color:#c62828; margin-bottom:0.15rem;">✗ At least 8 characters</div>
            <div id="hint_edit_lower" style="color:#c62828; margin-bottom:0.15rem;">✗ At least one lowercase letter</div>
            <div id="hint_edit_upper" style="color:#c62828; margin-bottom:0.15rem;">✗ At least one uppercase letter</div>
            <div id="hint_edit_num"   style="color:#c62828; margin-bottom:0.15rem;">✗ At least one number</div>
            <div id="hint_edit_spec"  style="color:#c62828;">✗ At least one special character (@$!%*?&)</div>
          </div>

          @if($errors->has('password'))
            <small style="color:#c62828;">{{ $errors->first('password') }}</small>
          @endif
        </div>

        {{-- Confirm Password --}}
        <div class="form-group" style="grid-column: span 2;" id="edit_confirm_wrap" style="display:none;">
          <label>Confirm New Password</label>
          <div style="position:relative;">
            <input type="password" id="edit_confirm_password"
              placeholder="Re-enter new password"
              style="width:100%; padding-right:2.5rem;" />
            <button type="button" onclick="togglePassword('edit_confirm_password', this)"
              style="position:absolute; right:0.75rem; top:50%; transform:translateY(-50%);
                    background:none; border:none; cursor:pointer; color:#888;">
              <i class="fas fa-eye"></i>
            </button>
          </div>
          <small id="edit_confirm_msg" style="display:none; font-size:0.78rem;"></small>
        </div>

        {{-- Status --}}
        <div class="form-group" style="grid-column: span 2;">
          <label>Status</label>
          <select name="status" id="edit_status">
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
          </select>
        </div>

        <div class="modal-buttons" style="grid-column: span 2;">
          <button type="button" id="closeEditUser" class="btn-cancel">Cancel</button>
          <button type="submit" id="edit_submit_btn" class="btn-save">
            <i class="fas fa-save"></i> Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- ARCHIVE CONFIRM MODAL --}}
  <div id="deleteUserModal" class="modal">
    <div class="modal-content small-modal">
      <h2><i class="fas fa-archive"></i> Archive User</h2>
      <p>Are you sure you want to archive this user? They won't be able to log in but their records will be kept. You can restore them anytime.</p>
      <div class="modal-buttons">
        <button class="btn-cancel" id="cancelDeleteUser">Cancel</button>
        <button class="btn-save btn-delete" id="confirmDeleteUser"><i class="fas fa-archive"></i> Archive</button>
      </div>
    </div>
  </div>

  {{-- RESTORE CONFIRM MODAL --}}
  <div id="restoreUserModal" class="modal">
    <div class="modal-content small-modal">
      <h2>Restore User</h2>
      <p>Are you sure you want to restore this user? They will be able to log in again.</p>
      <form id="restoreUserForm" action="" method="POST">
        @csrf
        <div class="modal-buttons">
          <button type="button" class="btn-cancel" id="cancelRestoreUser">Cancel</button>
          <button type="submit" class="btn-save">Restore</button>
        </div>
      </form>
    </div>
  </div>

  @endpush

  @push('scripts')
  <script>
  const openModalLocal  = (m) => { m?.classList.add('active'); document.body.classList.add('modal-open'); };
  const closeModalLocal = (m) => { m?.classList.remove('active'); document.body.classList.remove('modal-open'); };

  // ── Tab switcher ───────────────────────────────────────────────────────────
  function switchTab(tab) {
    document.getElementById('activeTab').style.display   = tab === 'active'   ? '' : 'none';
    document.getElementById('archivedTab').style.display = tab === 'archived' ? '' : 'none';
    document.getElementById('tabActive').classList.toggle('active',   tab === 'active');
    document.getElementById('tabArchived').classList.toggle('active', tab === 'archived');
  }

  // ── Auto-reopen modal on validation error ──────────────────────────────────
  @if(request('edit_id'))
    window.addEventListener('DOMContentLoaded', () => {
      document.getElementById('editUserForm').action = '/users/{{ request('edit_id') }}';
      openModalLocal(document.getElementById('editUserModal'));
      document.getElementById('edit_username').value = "{{ old('username') }}";
      document.getElementById('edit_role').value     = "{{ old('role') }}";
      document.getElementById('edit_status').value   = "{{ old('status') }}";
    });
  @elseif($errors->has('password') || $errors->has('username'))
    window.addEventListener('DOMContentLoaded', () => {
      openModalLocal(document.getElementById('addUserModal'));
    });
  @endif

  // ── Add User open/close ────────────────────────────────────────────────────
  document.getElementById('openAddUser')?.addEventListener('click', () => {
    resetAddForm();
    openModalLocal(document.getElementById('addUserModal'));
  });
  document.getElementById('closeAddUser')?.addEventListener('click', () => {
    closeModalLocal(document.getElementById('addUserModal'));
  });

  function resetAddForm() {
    document.getElementById('addUserForm')?.reset();
    document.getElementById('add_password_hints').style.display  = 'none';
    document.getElementById('add_strength_wrap').style.display   = 'none';
    document.getElementById('add_confirm_msg').style.display     = 'none';
    document.getElementById('add_username_msg').style.display    = 'none';
    setSubmitState('add', false);
  }

  // ── Edit User open/close ───────────────────────────────────────────────────
  document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      if (btn.classList.contains('restore-btn')) return;
      document.getElementById('editUserForm').action = `/users/${btn.dataset.id}`;
      document.getElementById('edit_user_id').value  = btn.dataset.id;
      document.getElementById('edit_username').value = btn.dataset.username;
      document.getElementById('edit_role').value     = btn.dataset.role;
      document.getElementById('edit_status').value   = btn.dataset.status;
      resetEditForm();
      openModalLocal(document.getElementById('editUserModal'));
    });
  });
  document.getElementById('closeEditUser')?.addEventListener('click', () => closeModalLocal(document.getElementById('editUserModal')));

  function resetEditForm() {
    document.getElementById('edit_password').value          = '';
    document.getElementById('edit_confirm_password').value  = '';
    document.getElementById('edit_password_hints').style.display  = 'none';
    document.getElementById('edit_strength_wrap').style.display   = 'none';
    document.getElementById('edit_confirm_msg').style.display     = 'none';
    document.getElementById('edit_confirm_wrap').style.display    = 'none';
    document.getElementById('edit_username_msg').style.display    = 'none';
    // Edit submit is always enabled (password is optional)
    setSubmitState('edit', true);
  }

  // ── Archive ────────────────────────────────────────────────────────────────
  let pendingDeleteForm = null;
  document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      pendingDeleteForm = btn.closest('.delete-form');
      openModalLocal(document.getElementById('deleteUserModal'));
    });
  });
  document.getElementById('confirmDeleteUser')?.addEventListener('click', () => pendingDeleteForm?.submit());
  document.getElementById('cancelDeleteUser')?.addEventListener('click', () => closeModalLocal(document.getElementById('deleteUserModal')));

  // ── Restore ────────────────────────────────────────────────────────────────
  document.querySelectorAll('.restore-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('restoreUserForm').action = `/users/${btn.dataset.id}/restore`;
      openModalLocal(document.getElementById('restoreUserModal'));
    });
  });
  document.getElementById('cancelRestoreUser')?.addEventListener('click', () => closeModalLocal(document.getElementById('restoreUserModal')));

  // ── Password toggle ────────────────────────────────────────────────────────
  function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
      input.type = 'text';
      icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
      input.type = 'password';
      icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
  }

  // ── Password score ─────────────────────────────────────────────────────────
  function getPasswordScore(val) {
    let score = 0;
    if (val.length >= 8)        score++;
    if (/[a-z]/.test(val))      score++;
    if (/[A-Z]/.test(val))      score++;
    if (/[0-9]/.test(val))      score++;
    if (/[@$!%*?&]/.test(val))  score++;
    return score;
  }

  // ── Strength bar (shared) ──────────────────────────────────────────────────
  function updateStrengthBar(prefix, val) {
    const wrap  = document.getElementById(prefix + '_strength_wrap');
    const bar   = document.getElementById(prefix + '_strength_bar');
    const label = document.getElementById(prefix + '_strength_label');

    if (!val.length) { wrap.style.display = 'none'; return; }
    wrap.style.display = 'block';

    const score = getPasswordScore(val);
    const level = score <= 2
      ? { color: '#c62828', text: 'Weak',   pct: '33%'  }
      : score <= 4
      ? { color: '#f57c00', text: 'Medium', pct: '66%'  }
      : { color: '#1a6b47', text: 'Strong', pct: '100%' };

    bar.style.width      = level.pct;
    bar.style.background = level.color;
    label.textContent    = level.text;
    label.style.color    = level.color;
  }

  // ── Password hints checklist (shared) ─────────────────────────────────────
  function checkPasswordHints(inputId, prefix) {
    const val   = document.getElementById(inputId).value;
    const hints = document.getElementById(prefix + '_password_hints');
    hints.style.display = val.length > 0 ? 'block' : 'none';

    const rules = [
      { id: prefix + '_len',   ok: val.length >= 8,        label: 'At least 8 characters' },
      { id: prefix + '_lower', ok: /[a-z]/.test(val),      label: 'At least one lowercase letter' },
      { id: prefix + '_upper', ok: /[A-Z]/.test(val),      label: 'At least one uppercase letter' },
      { id: prefix + '_num',   ok: /[0-9]/.test(val),      label: 'At least one number' },
      { id: prefix + '_spec',  ok: /[@$!%*?&]/.test(val),  label: 'At least one special character (@$!%*?&)' },
    ];

    rules.forEach(r => {
      const el = document.getElementById('hint_' + r.id);
      if (el) {
        el.style.color = r.ok ? '#1a6b47' : '#c62828';
        el.textContent = (r.ok ? '✓ ' : '✗ ') + r.label;
      }
    });
  }

  // ── Username validation (shared) ───────────────────────────────────────────
  function validateUsername(prefix) {
    const val = document.getElementById(prefix + '_username').value.trim();
    const msg = document.getElementById(prefix + '_username_msg');
    if (!val) { msg.style.display = 'none'; return false; }

    const valid = /^[a-zA-Z0-9_]{3,}$/.test(val);
    msg.style.display = 'block';
    if (!valid) {
      msg.style.color = '#c62828';
      msg.textContent = val.length < 3
        ? 'Username must be at least 3 characters.'
        : 'Only letters, numbers, and underscores allowed.';
      return false;
    }
    msg.style.color = '#1a6b47';
    msg.textContent = '✓ Looks good';
    return true;
  }

  // ── Confirm password validation (shared) ──────────────────────────────────
  function validateConfirm(prefix) {
    const pw      = document.getElementById(prefix + '_password').value;
    const confirm = document.getElementById(prefix + '_confirm_password').value;
    const msg     = document.getElementById(prefix + '_confirm_msg');

    if (!confirm) { msg.style.display = 'none'; return false; }
    msg.style.display = 'block';

    const match = pw === confirm;
    msg.style.color = match ? '#1a6b47' : '#c62828';
    msg.textContent = match ? '✓ Passwords match' : '✗ Passwords do not match';
    return match;
  }

  // ── Submit button state (shared) ───────────────────────────────────────────
  function setSubmitState(prefix, enabled) {
    const btn = document.getElementById(prefix + '_submit_btn');
    if (!btn) return;
    btn.disabled      = !enabled;
    btn.style.opacity = enabled ? '1'       : '0.5';
    btn.style.cursor  = enabled ? 'pointer' : 'not-allowed';
  }

  // ── Add form validity check ────────────────────────────────────────────────
  function checkAddFormValidity() {
    const pw       = document.getElementById('add_password').value;
    const confirm  = document.getElementById('add_confirm_password').value;
    const username = document.getElementById('add_username').value.trim();
    const role     = document.getElementById('add_role').value;

    const usernameOk = /^[a-zA-Z0-9_]{3,}$/.test(username);
    const passwordOk = getPasswordScore(pw) === 5;
    const confirmOk  = pw === confirm && confirm.length > 0;
    const roleOk     = role !== '';

    setSubmitState('add', usernameOk && passwordOk && confirmOk && roleOk);
  }

  // ── Edit form validity check ───────────────────────────────────────────────
  // Password is optional — only validate it if user started typing one
  function checkEditFormValidity() {
    const username  = document.getElementById('edit_username').value.trim();
    const pw        = document.getElementById('edit_password').value;
    const confirm   = document.getElementById('edit_confirm_password').value;

    const usernameOk = /^[a-zA-Z0-9_]{3,}$/.test(username);

    // If password field is empty, skip password checks
    if (!pw) {
      setSubmitState('edit', usernameOk);
      return;
    }

    const passwordOk = getPasswordScore(pw) === 5;
    const confirmOk  = pw === confirm && confirm.length > 0;
    setSubmitState('edit', usernameOk && passwordOk && confirmOk);
  }

  // ── Wire up ADD USER events ────────────────────────────────────────────────
  document.getElementById('add_username')?.addEventListener('input', () => {
    validateUsername('add');
    checkAddFormValidity();
  });

  document.getElementById('add_password')?.addEventListener('input', () => {
    updateStrengthBar('add', document.getElementById('add_password').value);
    checkPasswordHints('add_password', 'add');
    validateConfirm('add');
    checkAddFormValidity();
  });

  document.getElementById('add_confirm_password')?.addEventListener('input', () => {
    validateConfirm('add');
    checkAddFormValidity();
  });

  document.getElementById('add_role')?.addEventListener('change', checkAddFormValidity);

  // ── Wire up EDIT USER events ───────────────────────────────────────────────
  document.getElementById('edit_username')?.addEventListener('input', () => {
    validateUsername('edit');
    checkEditFormValidity();
  });

  document.getElementById('edit_password')?.addEventListener('input', () => {
    const pw = document.getElementById('edit_password').value;

    // Show/hide confirm field depending on whether password is being typed
    document.getElementById('edit_confirm_wrap').style.display = pw.length > 0 ? '' : 'none';

    updateStrengthBar('edit', pw);
    checkPasswordHints('edit_password', 'edit');
    validateConfirm('edit');
    checkEditFormValidity();
  });

  document.getElementById('edit_confirm_password')?.addEventListener('input', () => {
    validateConfirm('edit');
    checkEditFormValidity();
  });

  // ── Enter key submits add form ─────────────────────────────────────────────
  document.getElementById('addUserForm')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !document.getElementById('add_submit_btn').disabled) {
      this.submit();
    }
  });

  // ── Loading state on submit ────────────────────────────────────────────────
  document.getElementById('addUserForm')?.addEventListener('submit', function() {
    const btn = document.getElementById('add_submit_btn');
    btn.disabled     = true;
    btn.innerHTML    = '<i class="fas fa-spinner fa-spin"></i> Adding...';
    btn.style.cursor = 'not-allowed';
  });

  document.getElementById('editUserForm')?.addEventListener('submit', function() {
    const btn = document.getElementById('edit_submit_btn');
    btn.disabled     = true;
    btn.innerHTML    = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    btn.style.cursor = 'not-allowed';
  });
  </script>
  @endpush