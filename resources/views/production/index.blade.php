@extends('layouts.app')
@section('title', 'Production')
@section('page-title', 'Production Management')
@section('page-subtitle', 'Track and manage cheese production batches efficiently.')

@section('content')
<section id="production">

  <div class="module-header">
    <h2><i class="fas fa-industry"></i> Production Batches</h2>
    <div class="actions">
     <form method="GET" action="{{ route('production.index') }}" style="display:flex;">
  <div class="search-wrapper">
    <i class="fas fa-search"></i>
    <input type="text" name="search" placeholder="Search by batch number or type..." class="input-search" value="{{ $search ?? '' }}" />
  </div>
</form>
      @if(auth()->user()->role === 'admin' || auth()->user()->role === 'production')
      <button class="btn-primary" id="openAddBatch">
        <i class="fas fa-plus"></i> Add New Batch
      </button>
      @endif
    </div>
  </div>

  {{-- TABS --}}
 <div style="display:flex; gap:1rem; margin-top:1rem;">
    <button class="btn-tab active" id="tabActive" onclick="switchTab('active')">
      <i class="fas fa-industry"></i> Active Batches
    </button>
    <button class="btn-tab" id="tabArchived" onclick="switchTab('archived')">
      <i class="fas fa-archive"></i> Archived Batches
    </button>
  </div>

  {{-- ACTIVE BATCHES TABLE --}}
  <div id="activeTable">
    <div class="table-container">
      <table id="productionTable">
        <thead>
          <tr>
            <th>Batch No.</th><th>Product Type</th><th>Quantity</th>
            <th>Production Date</th><th>Status</th><th>Remarks</th><th>Staff</th><th>Actions</th>
          </tr>
        </thead>
        <tbody id="productionTableBody">
          @forelse($batches as $batch)
          <tr>
            <td>{{ $batch->batch_number }}</td>
            <td>{{ $batch->product_type }}</td>
            <td>{{ $batch->quantity }} kg</td>
            <td>{{ \Carbon\Carbon::parse($batch->production_date)->format('Y-m-d') }}</td>
       <td><span class="status-tag {{ $batch->status === 'Completed' ? 'active' : ($batch->status === 'In Production' ? 'low' : 'inactive') }}">{{ strtoupper($batch->status) }}</span></td>
            <td>{{ $batch->remarks ?? 'N/A' }}</td>
            <td>{{ $batch->staff->username ?? 'N/A' }}</td>
            <td class="actions-col">
              @if(auth()->user()->role === 'admin' || auth()->user()->role === 'production')
              <button class="action-btn edit-btn"
                data-id="{{ $batch->id }}"
                data-batch="{{ $batch->batch_number }}"
                data-type="{{ $batch->product_type }}"
                data-qty="{{ $batch->quantity }}"
                data-date="{{ \Carbon\Carbon::parse($batch->production_date)->format('Y-m-d') }}"
                data-status="{{ $batch->status }}"
                data-remarks="{{ $batch->remarks ?? '' }}">
                <i class="fas fa-pen"></i>
              </button>
              <form action="{{ route('production.archive', $batch) }}" method="POST" class="d-inline">
                @csrf
                <button type="button" class="action-btn archive-btn" title="Archive">
                  <i class="fas fa-archive"></i>
                </button>
              </form>
              @endif
            </td>
          </tr>
          @empty
          <tr id="emptyRow"><td colspan="8" style="text-align:center;padding:2rem;color:#888;">No production batches yet.</td></tr>
          @endforelse
        </tbody>
      </table>
   </div>
  <div style="margin-top:1rem;">{{ $batches->links() }}</div>
</div>

  {{-- ARCHIVED BATCHES TABLE --}}
  <div id="archivedTable" style="display:none;">
    <div class="table-container">
      <table id="archivedProductionTable">
        <thead>
          <tr>
            <th>Batch No.</th><th>Product Type</th><th>Quantity</th>
            <th>Production Date</th><th>Status</th><th>Remarks</th><th>Staff</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($archivedBatches as $batch)
          <tr>
            <td>{{ $batch->batch_number }}</td>
            <td>{{ $batch->product_type }}</td>
            <td>{{ $batch->quantity }} kg</td>
            <td>{{ \Carbon\Carbon::parse($batch->production_date)->format('Y-m-d') }}</td>
            <td><span class="status-tag inactive">{{ strtoupper($batch->status) }}</span></td>
            <td>{{ $batch->remarks ?? 'N/A' }}</td>
            <td>{{ $batch->staff->username ?? 'N/A' }}</td>
            <td class="actions-col">
              <form action="{{ route('production.restore', $batch) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="action-btn" title="Restore" style="color:#1a6b47;">
                  <i class="fas fa-undo"></i>
                </button>
              </form>
              @if(auth()->user()->role === 'admin')
              <form action="{{ route('production.destroy', $batch) }}" method="POST" class="d-inline delete-form">
                @csrf @method('DELETE')
                <button type="button" class="action-btn delete-btn" title="Permanently Delete">
                  <i class="fas fa-trash"></i>
                </button>
              </form>
              @endif
            </td>
          </tr>
          @empty
          <tr><td colspan="8" style="text-align:center;padding:2rem;color:#888;">No archived batches.</td></tr>
         @endforelse
        </tbody>
      </table>
    </div>
    <div style="margin-top:1rem;">{{ $archivedBatches->links() }}</div>
  </div>

{{-- ADD MODAL --}}
<div id="addBatchModal" class="modal">    
  <div class="modal-content">
    <h2>Add New Production Batch</h2>
    <form action="{{ route('production.store') }}" method="POST" class="form-grid">
      @csrf
      <div class="form-group">
        <label>Batch Number</label>
        <input type="text" name="batch_number" placeholder="e.g., B-2025-003" required />
      </div>
      <div class="form-group">
        <label>Product / Cheese Type</label>
        <select name="product_type" required>
          <option value="">Select cheese type</option>
          @foreach(['Burrata','Stracciatella','Cherry Mozzarella','Traditional Mozzarella','Provola','Mozzarella di Latte'] as $t)
            <option value="{{ $t }}">{{ $t }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label>Quantity Produced (kg)</label>
        <input type="number" name="quantity" step="0.01" min="0.01" placeholder="e.g., 100" required />
      </div>
      <div class="form-group">
        <label>Production Date</label>
        <input type="date" name="production_date" required />
      </div>
      <div class="form-group">
        <label>Status</label>
        <select name="status" disabled>
          <option value="In Production">In Production</option>
        </select>
        <input type="hidden" name="status" value="In Production" />
        <small style="color:#888;font-size:.75rem;">New batches always start at In Production.</small>
      </div>
      <div class="form-group">
        <label>Remarks</label>
        <input type="text" name="remarks" placeholder="Optional notes" />
      </div>
      <div class="modal-buttons">
        <button type="button" id="closeAddBatch" class="btn-cancel">Cancel</button>
        <button type="submit" class="btn-save"><i class="fas fa-plus"></i> Add Batch</button>
      </div>
    </form>
  </div>
</div>

{{-- EDIT MODAL --}}
<div id="editBatchModal" class="modal">
  <div class="modal-content">
    <h2>Edit Batch</h2>
    <form id="editBatchForm" action="" method="POST" class="form-grid">
      @csrf @method('PUT')
      <div class="form-group">
        <label>Batch Number</label>
        <input type="text" name="batch_number" id="editBatchNumber" required />
      </div>
      <div class="form-group">
        <label>Product / Cheese Type</label>
        <select name="product_type" id="editCheeseType" required>
          @foreach(['Burrata','Stracciatella','Cherry Mozzarella','Traditional Mozzarella','Provola','Mozzarella di Latte'] as $t)
            <option value="{{ $t }}">{{ $t }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label>Quantity (kg)</label>
        <input type="number" name="quantity" id="editQuantity" step="0.01" required />
      </div>
      <div class="form-group">
        <label>Production Date</label>
        <input type="date" name="production_date" id="editDate" required />
      </div>
    <div class="form-group">
        <label>Status</label>
        <select name="status" id="editStatus">
          <option value="In Production">In Production</option>
          <option value="Curing">Curing</option>
          <option value="Completed">Completed</option>
        </select>
        <small style="color:#888;font-size:.75rem;">Workflow: In Production → Curing → Completed</small>
      </div>
      <div class="form-group">
        <label>Remarks</label>
        <input type="text" name="remarks" id="editRemarks" />
      </div>
      <div class="modal-buttons">
        <button type="button" id="closeEditBatch" class="btn-cancel">Cancel</button>
        <button type="submit" class="btn-save">Save Changes</button>
      </div>
    </form>
  </div>
</div>

{{-- ARCHIVE CONFIRM MODAL --}}
<div id="archiveBatchModal" class="modal">
  <div class="modal-content small-modal">
    <h2>Confirm Archive</h2>
    <p>Are you sure you want to archive this batch? You can restore it later.</p>
    <div class="modal-buttons">
      <button class="btn-cancel" id="cancelArchiveBatch">Cancel</button>
      <button class="btn-delete" id="confirmArchiveBatch"><i class="fas fa-archive"></i> Archive</button>
    </div>
  </div>
</div>

{{-- DELETE CONFIRM MODAL --}}
<div id="deleteBatchModal" class="modal">
  <div class="modal-content small-modal">
    <h2>Confirm Delete</h2>
    <p>Are you sure you want to permanently delete this batch? This cannot be undone.</p>
    <div class="modal-buttons">
      <button class="btn-cancel" id="cancelDeleteBatch">Cancel</button>
     <button class="btn-delete" id="confirmDeleteBatch"><i class="fas fa-trash"></i> Delete</button>
    </div>
  </div>
</div>

<script>  
// Tab switching
function switchTab(tab) {
  document.getElementById('activeTable').style.display   = tab === 'active'   ? 'block' : 'none';
  document.getElementById('archivedTable').style.display = tab === 'archived' ? 'block' : 'none';
  document.getElementById('tabActive').classList.toggle('active',   tab === 'active');
  document.getElementById('tabArchived').classList.toggle('active', tab === 'archived');
}

// Add modal
document.getElementById('openAddBatch')?.addEventListener('click', () => openModal(document.getElementById('addBatchModal')));
document.getElementById('closeAddBatch')?.addEventListener('click', () => closeModal(document.getElementById('addBatchModal')));

// Edit modal
document.querySelectorAll('.edit-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.getElementById('editBatchForm').action = `/production/${btn.dataset.id}`;
    document.getElementById('editBatchNumber').value = btn.dataset.batch;
    document.getElementById('editCheeseType').value  = btn.dataset.type;
    document.getElementById('editQuantity').value    = btn.dataset.qty;
    document.getElementById('editDate').value        = btn.dataset.date;
    document.getElementById('editRemarks').value     = btn.dataset.remarks;

    const currentStatus = btn.dataset.status;
    const statusSelect = document.getElementById('editStatus');
    statusSelect.value = currentStatus;

    const workflow = ['In Production', 'Curing', 'Completed'];
    const currentIndex = workflow.indexOf(currentStatus);
    Array.from(statusSelect.options).forEach(opt => {
      const optIndex = workflow.indexOf(opt.value);
      opt.disabled = optIndex < currentIndex;
    });

    openModal(document.getElementById('editBatchModal'));
  });
});
document.getElementById('closeEditBatch')?.addEventListener('click', () => closeModal(document.getElementById('editBatchModal')));

// Archive modal
let pendingArchiveForm = null;
document.querySelectorAll('.archive-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    pendingArchiveForm = btn.closest('form');
    openModal(document.getElementById('archiveBatchModal'));
  });
});
document.getElementById('confirmArchiveBatch')?.addEventListener('click', () => pendingArchiveForm?.submit());
document.getElementById('cancelArchiveBatch')?.addEventListener('click', () => closeModal(document.getElementById('archiveBatchModal')));

// Delete modal
let pendingDeleteForm = null;
document.querySelectorAll('.delete-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    pendingDeleteForm = btn.closest('.delete-form');
    openModal(document.getElementById('deleteBatchModal'));
  });
});
document.getElementById('confirmDeleteBatch')?.addEventListener('click', () => pendingDeleteForm?.submit());
document.getElementById('cancelDeleteBatch')?.addEventListener('click', () => closeModal(document.getElementById('deleteBatchModal')));

</script>

</section>
@endsection 