@extends('layouts.app')
@section('title', 'Batches')
@section('page-title', 'Batch Tracking')
@section('page-subtitle', 'Monitor and manage all cheese production batches.')

@section('content')
<section id="batches">
  <div class="module-header">
    <h2><i class="fas fa-clipboard-list"></i> Batch Records</h2>
    <div class="actions">
      <div class="search-wrapper">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="Search by batch or cheese type..." class="input-search" id="batchSearch" />
      </div>
      @if(auth()->user()->role === 'admin' || auth()->user()->role === 'production')
      <button class="btn-primary" id="openAddBatchFromBatches">
        <i class="fas fa-plus"></i> Add New Batch
      </button>
      @endif
    </div>
  </div>

  <div class="table-container">
    <table id="batchesTable">
      <thead>
        <tr>
          <th>Batch ID</th><th>Cheese Type</th><th>Quantity</th>
          <th>Start Date</th><th>Completion Date</th><th>Status</th>
          <th>Staff</th><th>Remarks</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($batches as $batch)
        <tr>
          <td>{{ $batch->batch_id }}</td>
          <td>{{ $batch->cheese_type }}</td>
          <td>{{ $batch->quantity }} kg</td>
          <td>{{ $batch->start_date }}</td>
          <td>{{ $batch->completion_date }}</td>
          <td><span class="status-tag {{ $batch->status === 'Completed' ? 'inactive' : 'active' }}">{{ $batch->status }}</span></td>
          <td>{{ $batch->staff->username ?? 'N/A' }}</td>
          <td>{{ $batch->remarks ?? 'N/A' }}</td>
          <td class="actions-col">
            <button class="action-btn view-btn"
              data-batchid="{{ $batch->batch_id }}"
              data-type="{{ $batch->cheese_type }}"
              data-qty="{{ $batch->quantity }}"
              data-start="{{ $batch->start_date }}"
              data-end="{{ $batch->completion_date }}"
              data-status="{{ $batch->status }}"
              data-staff="{{ $batch->staff->username ?? 'N/A' }}"
              data-remarks="{{ $batch->remarks }}">
              <i class="fas fa-eye"></i>
            </button>
            @if(auth()->user()->role === 'admin' || auth()->user()->role === 'production')
            <button class="action-btn edit-btn batch-manage-btn"
              data-id="{{ $batch->id }}"
              data-status="{{ $batch->status }}"
              data-remarks="{{ $batch->remarks }}">
              <i class="fas fa-pen"></i>
            </button>
            <form action="{{ route('batches.destroy', $batch) }}" method="POST" class="d-inline delete-form">
              @csrf @method('DELETE')
              <button type="button" class="action-btn delete-btn"><i class="fas fa-trash"></i></button>
            </form>
            @endif
          </td>
        </tr>
        @empty
        <tr><td colspan="9" style="text-align:center; padding: 2rem; color: #888;">No batch records yet. Click "Add New Batch" to get started.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</section>

{{-- ADD MODAL --}}
<div id="addBatchModalBatches" class="modal">
  <div class="modal-content">
    <h2><i class="fas fa-plus-circle"></i> Add New Batch</h2>
    <form action="{{ route('batches.store') }}" method="POST" class="form-grid">
      @csrf
      <div class="form-group">
        <label><i class="fas fa-hashtag"></i> Batch ID</label>
        <input type="text" name="batch_id" id="batchIdNew" placeholder="e.g., B-2025-003" required />
      </div>
      <div class="form-group">
        <label><i class="fas fa-cheese"></i> Cheese Type</label>
        <select name="cheese_type" id="cheeseTypeNew" required>
          <option value="">Select cheese type...</option>
          @foreach(['Mozzarella','Cheddar','Parmesan','Gouda','Swiss','Brie','Feta','Blue Cheese'] as $t)
            <option value="{{ $t }}">{{ $t }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label><i class="fas fa-weight"></i> Quantity (kg)</label>
        <input type="number" name="quantity" id="quantityNew" placeholder="e.g., 80" required />
      </div>
      <div class="form-group">
        <label><i class="fas fa-calendar-alt"></i> Start Date</label>
        <input type="date" name="start_date" id="startDateNew" required />
      </div>
      <div class="form-group">
        <label><i class="fas fa-calendar-check"></i> Completion Date</label>
        <input type="date" name="completion_date" id="completionDateNew" required />
      </div>
      <div class="form-group">
        <label><i class="fas fa-tasks"></i> Status</label>
        <select name="status" id="statusNew" required>
          <option value="In Production">In Production</option>
          <option value="Curing">Curing</option>
          <option value="Ready for Packaging">Ready for Packaging</option>
          <option value="Completed">Completed</option>
        </select>
      </div>
      <div class="form-group">
        <label><i class="fas fa-user"></i> Staff Assigned</label>
        <select name="staff_id" id="staffNew" required>
          <option value="">Select staff member...</option>
          @foreach($staffList as $staff)
            <option value="{{ $staff->id }}">{{ $staff->username }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group form-group-full">
        <label><i class="fas fa-comment"></i> Remarks</label>
        <textarea name="remarks" id="remarksNew" rows="3" placeholder="Enter any notes or observations..."></textarea>
      </div>
      <div class="modal-buttons">
        <button type="button" id="closeAddBatchBatches" class="btn-cancel"><i class="fas fa-times"></i> Cancel</button>
        <button type="submit" class="btn-save"><i class="fas fa-save"></i> Add Batch</button>
      </div>
    </form>
  </div>
</div>

{{-- VIEW MODAL --}}
<div id="viewBatchModal" class="modal">
  <div class="modal-content">
    <h2>Batch Details</h2>
    <div class="form-grid">
      <p><strong>Batch ID:</strong> <span id="viewBatchId"></span></p>
      <p><strong>Cheese Type:</strong> <span id="viewCheeseType"></span></p>
      <p><strong>Quantity:</strong> <span id="viewQuantity"></span> kg</p>
      <p><strong>Status:</strong> <span id="viewStatus"></span></p>
      <p><strong>Start Date:</strong> <span id="viewStart"></span></p>
      <p><strong>Completion Date:</strong> <span id="viewEnd"></span></p>
      <p><strong>Staff:</strong> <span id="viewStaff"></span></p>
      <p><strong>Remarks:</strong> <span id="viewRemarks"></span></p>
    </div>
    <div class="modal-buttons">
      <button id="closeViewBatch" class="btn-cancel">Close</button>
    </div>
  </div>
</div>

{{-- EDIT MODAL --}}
<div id="editBatchModal" class="modal">
  <div class="modal-content">
    <h2>Update Batch</h2>
    <form id="editBatchForm" action="" method="POST" class="form-grid">
      @csrf @method('PUT')
      <div class="form-group">
        <label>Status</label>
        <select name="status" id="editStatus">
          <option value="In Production">In Production</option>
          <option value="Curing">Curing</option>
          <option value="Ready for Packaging">Ready for Packaging</option>
          <option value="Completed">Completed</option>
        </select>
      </div>
      <div class="form-group">
        <label>Remarks</label>
        <input type="text" name="remarks" id="editRemarks" placeholder="Update notes..." />
      </div>
      <div class="modal-buttons">
        <button type="button" id="closeEditBatch" class="btn-cancel">Cancel</button>
        <button type="submit" class="btn-save">Save Changes</button>
      </div>
    </form>
  </div>
</div>

{{-- DELETE MODAL --}}
<div id="deleteBatchModal" class="modal">
  <div class="modal-content small-modal">
    <h2>Confirm Delete</h2>
    <p>Are you sure you want to delete this batch record?</p>
    <div class="modal-buttons">
      <button class="btn-cancel" id="cancelDeleteBatch">Cancel</button>
      <button class="btn-save btn-delete" id="confirmDeleteBatch"><i class="fas fa-trash"></i> Delete</button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
const openModal  = (m) => { m?.classList.add('active'); document.body.classList.add('modal-open'); };
const closeModal = (m) => { m?.classList.remove('active'); document.body.classList.remove('modal-open'); };

document.getElementById('openAddBatchFromBatches')?.addEventListener('click', () => openModal(document.getElementById('addBatchModalBatches')));
document.getElementById('closeAddBatchBatches')?.addEventListener('click', () => closeModal(document.getElementById('addBatchModalBatches')));

document.querySelectorAll('.view-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.getElementById('viewBatchId').textContent   = btn.dataset.batchid;
    document.getElementById('viewCheeseType').textContent = btn.dataset.type;
    document.getElementById('viewQuantity').textContent  = btn.dataset.qty;
    document.getElementById('viewStart').textContent     = btn.dataset.start;
    document.getElementById('viewEnd').textContent       = btn.dataset.end;
    document.getElementById('viewStatus').textContent    = btn.dataset.status;
    document.getElementById('viewStaff').textContent     = btn.dataset.staff;
    document.getElementById('viewRemarks').textContent   = btn.dataset.remarks;
    openModal(document.getElementById('viewBatchModal'));
  });
});
document.getElementById('closeViewBatch')?.addEventListener('click', () => closeModal(document.getElementById('viewBatchModal')));

document.querySelectorAll('.edit-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.getElementById('editBatchForm').action = `/batches/${btn.dataset.id}`;
    document.getElementById('editStatus').value  = btn.dataset.status;
    document.getElementById('editRemarks').value = btn.dataset.remarks;
    openModal(document.getElementById('editBatchModal'));
  });
});
document.getElementById('closeEditBatch')?.addEventListener('click', () => closeModal(document.getElementById('editBatchModal')));

let pendingDeleteForm = null;
document.querySelectorAll('.delete-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    pendingDeleteForm = btn.closest('.delete-form');
    openModal(document.getElementById('deleteBatchModal'));
  });
});
document.getElementById('confirmDeleteBatch')?.addEventListener('click', () => pendingDeleteForm?.submit());
document.getElementById('cancelDeleteBatch')?.addEventListener('click', () => closeModal(document.getElementById('deleteBatchModal')));

document.getElementById('batchSearch')?.addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('#batchesTable tbody tr').forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
});
</script>
@endpush
