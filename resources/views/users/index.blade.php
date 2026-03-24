@extends('layouts.app')
@section('title', 'Manage Users')
@section('page-title', 'Manage Users')
@section('page-subtitle', 'Create, edit, and manage user accounts and roles.')

@section('content')
<section id="users">
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
    <button id="tabActive" onclick="switchTab('active')"
      style="padding:0.5rem 1.4rem; border-radius:20px; border:none; cursor:pointer; font-weight:600; background:#1a6b47; color:#fff;">
      Active Users
    </button>
    <button id="tabArchived" onclick="switchTab('archived')"
      style="padding:0.5rem 1.4rem; border-radius:20px; border:2px solid #1a6b47; cursor:pointer; font-weight:600; background:transparent; color:#1a6b47;">
      Archived Users
    </button>
  </div>

  {{-- ACTIVE USERS TABLE --}}
  <div id="activeTab" class="table-container">
    <table id="usersTable">
      <thead>
        <tr>
          <th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($users as $user)
        <tr>
          <td class="td-username">{{ $user->username }}</td>
          <td>{{ $user->email ?? 'N/A' }}</td>
          <td class="td-role">{{ ucfirst($user->role) }}</td>
          <td class="td-status">
            <span class="status-tag {{ $user->status === 'Active' ? 'active' : 'inactive' }}">{{ $user->status }}</span>
          </td>
          <td class="actions-col">
            <button class="action-btn edit-btn"
              data-id="{{ $user->id }}"
              data-username="{{ $user->username }}"
              data-email="{{ $user->email }}"
              data-role="{{ $user->role }}"
              data-status="{{ $user->status }}">
              <i class="fas fa-pen"></i>
            </button>
            @if($user->id !== auth()->id())
            <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline delete-form">
              @csrf @method('DELETE')
              <button type="button" class="action-btn delete-btn" title="Archive User">
                <i class="fas fa-archive"></i>
              </button>
            </form>
            @endif
          </td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center; padding: 2rem; color: #888;">No active users found.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- ARCHIVED USERS TABLE --}}
  <div id="archivedTab" class="table-container" style="display:none;">
    <table>
      <thead>
        <tr>
          <th>Username</th><th>Email</th><th>Role</th><th>Archived On</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($archivedUsers as $user)
        <tr>
          <td>{{ $user->username }}</td>
          <td>{{ $user->email ?? 'N/A' }}</td>
          <td>{{ ucfirst($user->role) }}</td>
          <td>{{ $user->deleted_at->format('M d, Y') }}</td>
          <td class="actions-col">
            <form action="{{ route('users.restore', $user->id) }}" method="POST" class="d-inline">
              @csrf
              <button type="submit" class="action-btn edit-btn" title="Restore User" style="background:#e8f5e9; color:#1a6b47;">
                <i class="fas fa-undo"></i>
              </button>
            </form>
          </td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center; padding: 2rem; color: #888;">No archived users.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</section>

{{-- ADD USER MODAL --}}
<div id="addUserModal" class="modal">
  <div class="modal-content">
    <h2>Add New User</h2>
    <form action="{{ route('users.store') }}" method="POST" class="form-grid">
      @csrf
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" placeholder="e.g. j_smith" required />
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" placeholder="email@example.com" />
      </div>
      <div class="form-group">
        <label>Role</label>
        <select name="role">
          <option value="">Select Role</option>
          <option value="admin">Admin</option>
          <option value="inventory">Inventory Staff</option>
          <option value="production">Production Staff</option>
          <option value="manager">Manager</option>
        </select>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="Enter password" required />
      </div>
      <div class="form-group">
        <label>Status</label>
        <select name="status">
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
        </select>
      </div>
      <div class="modal-buttons">
        <button type="button" id="closeAddUser" class="btn-cancel">Cancel</button>
        <button type="submit" class="btn-save">Add User</button>
      </div>
    </form>
  </div>
</div>

{{-- EDIT USER MODAL --}}
<div id="editUserModal" class="modal">
  <div class="modal-content">
    <h2>Edit User</h2>
    <form id="editUserForm" action="" method="POST" class="form-grid">
      @csrf @method('PUT')
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" id="edit_username" required />
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" id="edit_email" />
      </div>
  <div class="form-group" style="grid-column: span 2;">
        <label>Role</label>
        <select name="role" id="edit_role">
          <option value="admin">Admin</option>
          <option value="inventory">Inventory Staff</option>
          <option value="production">Production Staff</option>
          <option value="manager">Manager</option>
        </select>
      </div>
      <div class="form-group" style="grid-column: span 2;">
        <label>New Password <small style="color:#666;">(leave blank to keep current)</small></label>
        <input type="password" name="password" placeholder="New password" />
      </div>
      <div class="form-group" style="grid-column: span 2;">
        <label>Status</label>
        <select name="status" id="edit_status">
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
        </select>
      </div>    
      <div class="modal-buttons">
        <button type="button" id="closeEditUser" class="btn-cancel">Cancel</button>
        <button type="submit" class="btn-save">Save Changes</button>
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
@endsection

@push('scripts')
<script>
const openModalLocal  = (m) => { m?.classList.add('active'); document.body.classList.add('modal-open'); };
const closeModalLocal = (m) => { m?.classList.remove('active'); document.body.classList.remove('modal-open'); };

// Tab switching
function switchTab(tab) {
  const activeTab    = document.getElementById('activeTab');
  const archivedTab  = document.getElementById('archivedTab');
  const tabActive    = document.getElementById('tabActive');
  const tabArchived  = document.getElementById('tabArchived');

  if (tab === 'active') {
    activeTab.style.display   = '';
    archivedTab.style.display = 'none';
    tabActive.style.background   = '#1a6b47';
    tabActive.style.color        = '#fff';
    tabActive.style.border       = 'none';
    tabArchived.style.background = 'transparent';
    tabArchived.style.color      = '#1a6b47';
    tabArchived.style.border     = '2px solid #1a6b47';
  } else {
    activeTab.style.display   = 'none';
    archivedTab.style.display = '';
    tabArchived.style.background = '#1a6b47';
    tabArchived.style.color      = '#fff';
    tabArchived.style.border     = 'none';
    tabActive.style.background   = 'transparent';
    tabActive.style.color        = '#1a6b47';
    tabActive.style.border       = '2px solid #1a6b47';
  }
}

// Add user
document.getElementById('openAddUser')?.addEventListener('click', () => openModalLocal(document.getElementById('addUserModal')));
document.getElementById('closeAddUser')?.addEventListener('click', () => closeModalLocal(document.getElementById('addUserModal')));

// Edit user
document.querySelectorAll('.edit-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.getElementById('editUserForm').action = `/users/${btn.dataset.id}`;
    document.getElementById('edit_username').value = btn.dataset.username;
    document.getElementById('edit_email').value    = btn.dataset.email;
    document.getElementById('edit_role').value     = btn.dataset.role;
    document.getElementById('edit_status').value   = btn.dataset.status;
    openModalLocal(document.getElementById('editUserModal'));
  });
});
document.getElementById('closeEditUser')?.addEventListener('click', () => closeModalLocal(document.getElementById('editUserModal')));

// Archive (was delete)
let pendingDeleteForm = null;
document.querySelectorAll('.delete-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    pendingDeleteForm = btn.closest('.delete-form');
    openModalLocal(document.getElementById('deleteUserModal'));
  });
});
document.getElementById('confirmDeleteUser')?.addEventListener('click', () => pendingDeleteForm?.submit());
document.getElementById('cancelDeleteUser')?.addEventListener('click', () => closeModalLocal(document.getElementById('deleteUserModal')));
</script>
@endpush