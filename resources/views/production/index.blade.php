@extends('layouts.app')
@section('title', 'Production')
@section('page-title', 'Production Management')
@section('page-subtitle', 'Track and manage cheese production batches efficiently.')

@section('content')
<section id="production">
  <div class="module-header">
    <h2><i class="fas fa-industry"></i> Production Batches</h2>
    <div class="actions">
      <div class="search-wrapper">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="Search by batch number or type..." class="input-search" id="batchSearch" />
      </div>
      @if(auth()->user()->role === 'admin' || auth()->user()->role === 'production')
      <button class="btn-primary production-add-btn" id="openAddBatch">
        <i class="fas fa-plus"></i> Add New Batch
      </button>
      @endif
    </div>
  </div>

  <div class="table-container">
    <table id="productionTable">
      <thead>
        <tr>
          <th>Batch No.</th><th>Product Type</th><th>Quantity</th>
          <th>Production Date</th><th>Status</th><th>Remarks</th><th>Staff</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($batches as $batch)
        <tr>
          <td>{{ $batch->batch_number }}</td>
         <td>{{ $batch->product_type }}</td>
          <td>{{ $batch->quantity }} kg</td>
          <td>{{ \Carbon\Carbon::parse($batch->production_date)->format('Y-m-d') }}</td>
          <td><span class="status-tag {{ $batch->status === 'Completed' ? 'inactive' : 'active' }}">{{ $batch->status }}</span></td>
          <td>{{ $batch->remarks ?? 'N/A' }}</td>
          <td>{{ $batch->staff->username ?? 'N/A' }}</td>
          <td class="actions-col">
            @if(auth()->user()->role === 'admin' || auth()->user()->role === 'production')
            <button class="action-btn edit-btn"
              data-id="{{ $batch->id }}"
              data-batch="{{ $batch->batch_number }}"
              data-type="{{ $batch->product_type }}"
              data-qty="{{ $batch->quantity }}"
              data-date="{{ $batch->production_date }}"
              data-status="{{ $batch->status }}"
              data-remarks="{{ $batch->remarks }}">
              <i class="fas fa-pen"></i>
            </button>
            <form action="{{ route('production.destroy', $batch) }}" method="POST" class="d-inline delete-form">
              @csrf @method('DELETE')
              <button type="button" class="action-btn delete-btn"><i class="fas fa-trash"></i></button>
            </form>
            @endif
          </td>
        </tr>
        @empty
        <tr><td colspan="8" style="text-align:center; padding: 2rem; color: #888;">No production batches yet. Click "Add New Batch" to get started.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</section>

{{-- ADD MODAL --}}
<div id="addBatchModal" class="modal">
  <div class="modal-content">
    <h2>Add New Production Batch</h2>
    <form action="{{ route('production.store') }}" method="POST" class="form-grid">
      @csrf
      <div class="form-group">
        <label for="batchNumber">Batch Number</label>
        <input type="text" name="batch_number" id="batchNumber" placeholder="e.g., B-2025-003" required />
      </div>
      <div class="form-group">
        <label for="productType">Product / Cheese Type</label>
       <select name="product_type" id="productType" required>
          <option value="">Select cheese type</option>
          @foreach(['Mozzarella Cheese','Cheddar Cheese','Parmesan Cheese','Gouda Cheese','Swiss Cheese','Brie Cheese','Blue Cheese','Feta Cheese','Cream Cheese','Ricotta Cheese'] as $t)
            <option value="{{ $t }}">{{ $t }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label for="batchQuantity">Quantity Produced (kg)</label>
        <input type="number" name="quantity" id="batchQuantity" placeholder="e.g., 100" required />
      </div>
      <div class="form-group">
        <label for="productionDate">Production Date</label>
        <input type="date" name="production_date" id="productionDate" required />
      </div>
      <div class="form-group">
        <label for="batchStatus">Status</label>
        <select name="status" id="batchStatus">
          <option value="In Production">In Production</option>
          <option value="Curing">Curing</option>
          <option value="Completed">Completed</option>
        </select>
      </div>
      <div class="form-group">
        <label for="batchRemarks">Remarks</label>
        <input type="text" name="remarks" id="batchRemarks" placeholder="Optional notes" />
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
          @foreach(['Mozzarella Cheese','Cheddar Cheese','Parmesan Cheese','Gouda Cheese','Swiss Cheese','Brie Cheese','Blue Cheese','Feta Cheese','Cream Cheese','Ricotta Cheese'] as $t)
            <option value="{{ $t }}">{{ $t }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label>Quantity (kg)</label>
        <input type="number" name="quantity" id="editQuantity" required />
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

{{-- DELETE MODAL --}}
<div id="deleteBatchModal" class="modal">
  <div class="modal-content small-modal">
    <h2>Confirm Delete</h2>
    <p>Are you sure you want to delete this production batch?</p>
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

document.getElementById('openAddBatch')?.addEventListener('click', () => openModal(document.getElementById('addBatchModal')));
document.getElementById('closeAddBatch')?.addEventListener('click', () => closeModal(document.getElementById('addBatchModal')));

document.querySelectorAll('.edit-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.getElementById('editBatchForm').action = `/production/${btn.dataset.id}`;
    document.getElementById('editBatchNumber').value = btn.dataset.batch;
    document.getElementById('editCheeseType').value  = btn.dataset.type;
    document.getElementById('editQuantity').value    = btn.dataset.qty;
    document.getElementById('editDate').value        = btn.dataset.date;
    document.getElementById('editStatus').value      = btn.dataset.status;
    document.getElementById('editRemarks').value     = btn.dataset.remarks;
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
  document.querySelectorAll('#productionTable tbody tr').forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
});
</script>
@endpush
