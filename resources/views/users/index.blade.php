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

  <div class="table-container">
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
          <td class="td-status"><span class="status-tag {{ $user->status === 'Active' ? 'active' : 'inactive' }}">{{ $user->status }}</span></td>
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
              <button type="button" class="action-btn delete-btn"><i class="fas fa-trash"></i></button>
            </form>
            @endif
          </td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center; padding: 2rem; color: #888;">No users found.</td></tr>
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
        <input type="text" name="username" id="add_username" placeholder="e.g. j_smith" required />
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" placeholder="email@example.com" />
      </div>
      <div class="form-group">
        <label>Role</label>
        <select name="role" id="add_role">
          <option value="">Select Role</option>
          <option value="admin">Admin</option>
          <option value="inventory">Inventory Staff</option>
          <option value="production">Production Staff</option>
          <option value="manager">Manager</option>
        </select>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" id="add_password" placeholder="Enter password" required />
      </div>
      <div class="form-group">
        <label>Status</label>
        <select name="status" id="add_status">
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
        </select>
      </div>
      <div class="modal-buttons">
        <button type="button" id="closeAddUser" class="btn-cancel">Cancel</button>
        <button type="submit" id="saveAddUser" class="btn-save">Add User</button>
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
      <div class="form-group">
        <label>Role</label>
        <select name="role" id="edit_role">
          <option value="admin">Admin</option>
          <option value="inventory">Inventory Staff</option>
          <option value="production">Production Staff</option>
          <option value="manager">Manager</option>
        </select>
      </div>
      <div class="form-group">
        <label>New Password <small style="color:#666;">(leave blank to keep current)</small></label>
        <input type="password" name="password" id="edit_password" placeholder="New password" />
      </div>
      <div class="form-group">
        <label>Status</label>
        <select name="status" id="edit_status">
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
        </select>
      </div>
      <div class="modal-buttons">
        <button type="button" id="closeEditUser" class="btn-cancel">Cancel</button>
        <button type="submit" id="saveEditUser" class="btn-save">Save Changes</button>
      </div>
    </form>
  </div>
</div>

{{-- DELETE USER MODAL --}}
<div id="deleteUserModal" class="modal">
  <div class="modal-content small-modal">
    <h2>Confirm Delete</h2>
    <p>Are you sure you want to delete this user? This action cannot be undone.</p>
    <div class="modal-buttons">
      <button class="btn-cancel" id="cancelDeleteUser">Cancel</button>
      <button class="btn-save btn-delete" id="confirmDeleteUser"><i class="fas fa-trash"></i> Delete User</button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
const openModalLocal  = (m) => { m?.classList.add('active'); document.body.classList.add('modal-open'); };
const closeModalLocal = (m) => { m?.classList.remove('active'); document.body.classList.remove('modal-open'); };

document.getElementById('openAddUser')?.addEventListener('click', () => openModalLocal(document.getElementById('addUserModal')));
document.getElementById('closeAddUser')?.addEventListener('click', () => closeModalLocal(document.getElementById('addUserModal')));

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
