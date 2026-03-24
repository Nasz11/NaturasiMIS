@extends('layouts.app')
@section('title', 'Batches')
@section('page-title', 'Batch Tracking')
@section('page-subtitle', 'Monitor and manage all cheese production batches.')

@section('content')
<section id="batches">
  <div class="module-header">
    <h2> Batch Records</h2>
    <div class="actions">
      <div class="search-wrapper" style="display:flex;align-items:center;gap:.5rem;border:1px solid #ccc;border-radius:8px;padding:.4rem .8rem;background:#fff;">
        <i class="fas fa-search" style="color:#888;"></i>
        <input type="text" placeholder="Search by batch or cheese type..." class="input-search"
          id="batchSearch" style="border:none;outline:none;font-size:.95rem;width:240px;" />
      </div>
      @if(auth()->user()->role === 'admin' || auth()->user()->role === 'production')
      <button class="btn-primary" id="openAddBatch">
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
          <th>Start Date</th><th>Completion Date</th>
          <th>Status</th><th>Staff</th><th>Remarks</th><th>Actions</th>
        </tr>
      </thead>
      <tbody id="batchesTableBody">
        @forelse($batches as $batch)
        <tr>
          <td>{{ $batch->batch_id }}</td>
          <td>{{ $batch->cheese_type }}</td>
          <td>{{ $batch->quantity }} kg</td>
          <td>{{ \Carbon\Carbon::parse($batch->start_date)->format('Y-m-d') }}</td>
          <td>{{ \Carbon\Carbon::parse($batch->completion_date)->format('Y-m-d') }}</td>
          <td><span class="status-tag {{ $batch->status === 'Completed' ? 'inactive' : 'active' }}">{{ strtoupper($batch->status) }}</span></td>
          <td>{{ $batch->staff?->username ?? 'N/A' }}</td>
          <td>{{ $batch->remarks ?? 'N/A' }}</td>
          <td class="actions-col">
            <button class="action-btn view-btn"
              data-batchid="{{ $batch->batch_id }}"
              data-cheese="{{ $batch->cheese_type }}"
              data-qty="{{ $batch->quantity }}"
              data-start="{{ \Carbon\Carbon::parse($batch->start_date)->format('Y-m-d') }}"
              data-end="{{ \Carbon\Carbon::parse($batch->completion_date)->format('Y-m-d') }}"
              data-status="{{ $batch->status }}"
              data-staff="{{ $batch->staff?->username ?? 'N/A' }}"
              data-remarks="{{ $batch->remarks ?? 'N/A' }}">
              <i class="fas fa-eye"></i>
            </button>
            @if(auth()->user()->role === 'admin' || auth()->user()->role === 'production')
            <button class="action-btn edit-btn batch-manage-btn"
              data-id="{{ $batch->id }}"
              data-status="{{ $batch->status }}"
              data-remarks="{{ $batch->remarks ?? '' }}">
              <i class="fas fa-pen"></i>
            </button>
            @endif
            @if(auth()->user()->role === 'admin')
            <form action="{{ route('batches.destroy', $batch) }}" method="POST" class="delete-form d-inline">
              @csrf @method('DELETE')
              <button type="button" class="action-btn delete-btn"><i class="fas fa-trash"></i></button>
            </form>
            @endif
          </td>
        </tr>
        @empty
        <tr id="emptyRow"><td colspan="9" style="text-align:center;padding:2rem;color:#888;">No batch records yet. Click "Add New Batch" to get started.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</section>

{{-- ADD MODAL --}}
<div id="addBatchModalBatches" class="modal">
  <div class="modal-content" style="max-width:600px;">
    <div style="margin-bottom:1.25rem;padding-bottom:1rem;border-bottom:2px solid #e8f5e9;">
      <h2 style="margin:0;font-size:1.2rem;color:#1b5e20;">Add New Batch</h2>
      <p style="margin:0;font-size:.82rem;color:#888;">Fill in the details below to create a new batch record.</p>
    </div>

    <form action="{{ route('batches.store') }}" method="POST" class="form-grid">
      @csrf

      <div class="form-group">
        <label style="font-weight:600;font-size:.88rem;color:#444;">Batch ID</label>
        <input type="text" name="batch_id" placeholder="e.g., B-2025-003"
          style="width:100%;padding:.55rem .75rem;border:1.5px solid #ccc;border-radius:8px;font-size:.93rem;outline:none;transition:border .2s;"
          onfocus="this.style.borderColor='#2e7d32'" onblur="this.style.borderColor='#ccc'" required />
      </div>

      <div class="form-group">
        <label style="font-weight:600;font-size:.88rem;color:#444;">Cheese Type</label>
        <select name="cheese_type"
          style="width:100%;padding:.55rem .75rem;border:1.5px solid #ccc;border-radius:8px;font-size:.93rem;outline:none;background:#fff;transition:border .2s;"
          onfocus="this.style.borderColor='#2e7d32'" onblur="this.style.borderColor='#ccc'" required>
          <option value="">Select cheese type...</option>
          @foreach(['Burrata','Stracciatella','Cherry Mozzarella','Traditional Mozzarella','Provola','Mozzarella di Latte'] as $t)
            <option value="{{ $t }}">{{ $t }}</option>
          @endforeach
        </select>
      </div>

      <div class="form-group">
        <label style="font-weight:600;font-size:.88rem;color:#444;">Quantity (kg)</label>
        <input type="number" name="quantity" step="0.01" min="0.01" placeholder="e.g., 50.00"
          style="width:100%;padding:.55rem .75rem;border:1.5px solid #ccc;border-radius:8px;font-size:.93rem;outline:none;transition:border .2s;"
          onfocus="this.style.borderColor='#2e7d32'" onblur="this.style.borderColor='#ccc'" required />
      </div>

      <div class="form-group">
        <label style="font-weight:600;font-size:.88rem;color:#444;">Status</label>
        <select name="status"
          style="width:100%;padding:.55rem .75rem;border:1.5px solid #ccc;border-radius:8px;font-size:.93rem;outline:none;background:#fff;transition:border .2s;"
          onfocus="this.style.borderColor='#2e7d32'" onblur="this.style.borderColor='#ccc'" required>
          <option value="In Production">In Production</option>
          <option value="Curing">Curing</option>
          <option value="Ready for Packaging">Ready for Packaging</option>
          <option value="Completed">Completed</option>
        </select>
      </div>

      <div class="form-group">
        <label style="font-weight:600;font-size:.88rem;color:#444;">Start Date</label>
        <input type="date" name="start_date"
          style="width:100%;padding:.55rem .75rem;border:1.5px solid #ccc;border-radius:8px;font-size:.93rem;outline:none;transition:border .2s;"
          onfocus="this.style.borderColor='#2e7d32'" onblur="this.style.borderColor='#ccc'" required />
      </div>

      <div class="form-group">
        <label style="font-weight:600;font-size:.88rem;color:#444;">Completion Date</label>
        <input type="date" name="completion_date"
          style="width:100%;padding:.55rem .75rem;border:1.5px solid #ccc;border-radius:8px;font-size:.93rem;outline:none;transition:border .2s;"
          onfocus="this.style.borderColor='#2e7d32'" onblur="this.style.borderColor='#ccc'" required />
      </div>

      <div class="form-group" style="grid-column:1/-1;">
        <label style="font-weight:600;font-size:.88rem;color:#444;">Staff Assigned</label>
        <select name="staff_id"
          style="width:100%;padding:.55rem .75rem;border:1.5px solid #ccc;border-radius:8px;font-size:.93rem;outline:none;background:#fff;transition:border .2s;"
          onfocus="this.style.borderColor='#2e7d32'" onblur="this.style.borderColor='#ccc'" required>
          <option value="">Select staff member...</option>
          @foreach($staff as $s)
            <option value="{{ $s->id }}">{{ $s->username }}</option>
          @endforeach
        </select>
      </div>

      <div class="form-group" style="grid-column:1/-1;">
        <label style="font-weight:600;font-size:.88rem;color:#444;">Remarks</label>
        <textarea name="remarks" rows="3" placeholder="Enter any optional notes here..."
          style="width:100%;padding:.55rem .75rem;border:1.5px solid #ccc;border-radius:8px;font-size:.93rem;outline:none;resize:vertical;font-family:inherit;transition:border .2s;"
          onfocus="this.style.borderColor='#2e7d32'" onblur="this.style.borderColor='#ccc'"></textarea>
      </div>

      <div class="modal-buttons" style="grid-column:1/-1;">
        <button type="button" id="closeAddBatch" class="btn-cancel">Cancel</button>
        <button type="submit" class="btn-save">Add Batch</button>
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
        <label style="font-weight:600;font-size:.88rem;color:#444;">Status</label>
        <select name="status" id="editStatus"
          style="width:100%;padding:.55rem .75rem;border:1.5px solid #ccc;border-radius:8px;font-size:.93rem;outline:none;background:#fff;">
          <option value="In Production">In Production</option>
          <option value="Curing">Curing</option>
          <option value="Ready for Packaging">Ready for Packaging</option>
          <option value="Completed">Completed</option>
        </select>
      </div>
      <div class="form-group">
        <label style="font-weight:600;font-size:.88rem;color:#444;">Remarks</label>
        <input type="text" name="remarks" id="editRemarks" placeholder="Update notes..."
          style="width:100%;padding:.55rem .75rem;border:1.5px solid #ccc;border-radius:8px;font-size:.93rem;outline:none;" />
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

document.getElementById('openAddBatch')?.addEventListener('click', () => openModal(document.getElementById('addBatchModalBatches')));
document.getElementById('closeAddBatch')?.addEventListener('click', () => closeModal(document.getElementById('addBatchModalBatches')));
document.getElementById('closeViewBatch')?.addEventListener('click', () => closeModal(document.getElementById('viewBatchModal')));
document.getElementById('closeEditBatch')?.addEventListener('click', () => closeModal(document.getElementById('editBatchModal')));
document.getElementById('cancelDeleteBatch')?.addEventListener('click', () => closeModal(document.getElementById('deleteBatchModal')));

// View modal
document.querySelectorAll('.view-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.getElementById('viewBatchId').textContent    = btn.dataset.batchid;
    document.getElementById('viewCheeseType').textContent = btn.dataset.cheese;
    document.getElementById('viewQuantity').textContent   = btn.dataset.qty;
    document.getElementById('viewStart').textContent      = btn.dataset.start;
    document.getElementById('viewEnd').textContent        = btn.dataset.end;
    document.getElementById('viewStatus').textContent     = btn.dataset.status;
    document.getElementById('viewStaff').textContent      = btn.dataset.staff;
    document.getElementById('viewRemarks').textContent    = btn.dataset.remarks;
    openModal(document.getElementById('viewBatchModal'));
  });
});

// Edit modal
document.querySelectorAll('.edit-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.getElementById('editBatchForm').action = `/batches/${btn.dataset.id}`;
    document.getElementById('editStatus').value  = btn.dataset.status;
    document.getElementById('editRemarks').value = btn.dataset.remarks;
    openModal(document.getElementById('editBatchModal'));
  });
});

// Delete modal
let pendingDeleteForm = null;
document.querySelectorAll('.delete-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    pendingDeleteForm = btn.closest('.delete-form');
    openModal(document.getElementById('deleteBatchModal'));
  });
});
document.getElementById('confirmDeleteBatch')?.addEventListener('click', () => pendingDeleteForm?.submit());

// SEARCH
function filterBatches() {
  const q = document.getElementById('batchSearch').value.toLowerCase();
  document.querySelectorAll('#batchesTableBody tr').forEach(row => {
    if (row.id === 'emptyRow') return;
    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
}

document.getElementById('batchSearch')?.addEventListener('keydown', function (e) {
  if (e.key === 'Enter') filterBatches();
});

document.querySelector('.fa-search')?.addEventListener('click', filterBatches);

</script>
@endpush