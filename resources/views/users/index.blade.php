@extends('layouts.app')
@section('title', 'Manage Users')
@section('page-title', 'Manage Users')
@section('page-subtitle', 'Create, edit, and manage user accounts and roles.')

@section('content')
<section id="usersSection">
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
      <i class="fas fa-users"></i> Active Users
    </button>
    <button class="btn-tab" id="tabArchived" onclick="switchTab('archived')">
      <i class="fas fa-archive"></i> Archived Users
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
          <td>{{ ucfirst($user->role) }}</td>
          <td><span class="status-tag {{ $user->status === 'Active' ? 'active' : 'inactive' }}">{{ $user->status }}</span></td>
          <td class="actions-col">
            <button class="action-btn edit-btn"
              data-id="{{ $user->id }}"
              data-username="{{ $user->username }}"
              data-role="{{ $user->role }}"
              data-status="{{ $user->status }}">
              <i class="fas fa-pen"></i>
            </button>
            @if($user->id !== auth()->id())
            <form action="{{ route('users.destroy', $user) }}" method="POST" class="delete-form d-inline">
              @csrf @method('DELETE')
              <button type="button" class="action-btn delete-btn" title="Archive User"><i class="fas fa-archive"></i></button>
            </form>
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
          <td>{{ ucfirst($user->role) }}</td>
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
        <label>Role</label>
        <select name="role" required>
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
      <div class="form-group" style="grid-column: span 2;">
  <label>Username</label>
  <input type="text" name="username" id="edit_username" required />
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
  <label>Password <small style="color:#666;">(leave blank to keep current)</small></label>
  <input type="password" name="password" placeholder="New password (optional)" />
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

{{-- RESTORE CONFIRM MODAL --}}
<div id="restoreUserModal" class="modal">
  <div class="modal-content small-modal">
    <h2>   Restore User</h2>
    <p>Are you sure you want to restore this user? They will be able to log in again.</p>
    <form id="restoreUserForm" action="" method="POST">
      @csrf
      <div class="modal-buttons">
        <button type="button" class="btn-cancel" id="cancelRestoreUser">Cancel</button>
        <button type="submit" class="btn-save"> Restore</button>
      </div>
    </form>
  </div>
</div>
@endpush

@push('scripts')
<script>
const openModalLocal  = (m) => { m?.classList.add('active'); document.body.classList.add('modal-open'); };
const closeModalLocal = (m) => { m?.classList.remove('active'); document.body.classList.remove('modal-open'); };

function switchTab(tab) {
  document.getElementById('activeTab').style.display   = tab === 'active'   ? '' : 'none';
  document.getElementById('archivedTab').style.display = tab === 'archived' ? '' : 'none';
  document.getElementById('tabActive').classList.toggle('active',   tab === 'active');
  document.getElementById('tabArchived').classList.toggle('active', tab === 'archived');
}

// Add user
document.getElementById('openAddUser')?.addEventListener('click', () => openModalLocal(document.getElementById('addUserModal')));
document.getElementById('closeAddUser')?.addEventListener('click', () => closeModalLocal(document.getElementById('addUserModal')));

// Edit user
document.querySelectorAll('.edit-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    if (btn.classList.contains('restore-btn')) return;
    document.getElementById('editUserForm').action = `/users/${btn.dataset.id}`;
    document.getElementById('edit_username').value = btn.dataset.username;
    document.getElementById('edit_role').value     = btn.dataset.role;
    document.getElementById('edit_status').value   = btn.dataset.status;
    openModalLocal(document.getElementById('editUserModal'));
  });
});
document.getElementById('closeEditUser')?.addEventListener('click', () => closeModalLocal(document.getElementById('editUserModal')));

// Archive
let pendingDeleteForm = null;
document.querySelectorAll('.delete-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    pendingDeleteForm = btn.closest('.delete-form');
    openModalLocal(document.getElementById('deleteUserModal'));
  });
});
document.getElementById('confirmDeleteUser')?.addEventListener('click', () => pendingDeleteForm?.submit());
document.getElementById('cancelDeleteUser')?.addEventListener('click', () => closeModalLocal(document.getElementById('deleteUserModal')));

// Restore
document.querySelectorAll('.restore-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.getElementById('restoreUserForm').action = `/users/${btn.dataset.id}/restore`;
    openModalLocal(document.getElementById('restoreUserModal'));
  });
});
document.getElementById('cancelRestoreUser')?.addEventListener('click', () => closeModalLocal(document.getElementById('restoreUserModal')));
</script>
@endpush