@extends('layouts.app')
@section('title', 'Inventory')
@section('page-title', 'Inventory Management')
@section('page-subtitle', 'Manage all product and material stocks efficiently.')

@php
$cheeseProducts = [
    'Burrata', 'Stracciatella', 'Cherry Mozzarella',
    'Traditional Mozzarella', 'Provola', 'Mozzarella di Latte',
];
$rawMaterials = [
    'Cagliata', 'Fresh Milk', 'Cream', 'Iodized Salt', 'Rock Salt',
    'Trisodium', 'Rennet', 'Citric Acid', 'Palm Oil', 'Skimmed Milk',
    'High Melt Starch', 'Butter Flavor', 'Butter Milk', 'Parmesan Flavor',
    'Cheddar Flavor', 'Milk Powder',
];
$unitMap = [
    'Burrata'               => 'kg', 'Stracciatella'       => 'kg',
    'Cherry Mozzarella'     => 'kg', 'Traditional Mozzarella' => 'kg',
    'Provola'               => 'kg', 'Mozzarella di Latte' => 'kg',
    'Cagliata'              => 'kg', 'Fresh Milk'          => 'L',
    'Cream'                 => 'L',  'Iodized Salt'        => 'kg',
    'Rock Salt'             => 'kg', 'Trisodium'           => 'kg',
    'Rennet'                => 'kg', 'Citric Acid'         => 'kg',
    'Palm Oil'              => 'L',  'Skimmed Milk'        => 'L',
    'High Melt Starch'      => 'kg', 'Butter Flavor'       => 'kg',
    'Butter Milk'           => 'L',  'Parmesan Flavor'     => 'kg',
    'Cheddar Flavor'        => 'kg', 'Milk Powder'         => 'kg',
];
@endphp

@section('content')
<section id="inventorySection">

  @if(session('success'))
  <div class="success-message" id="successAlert">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
  </div>
  @endif

  <div class="module-header">
    <h2><i class="fas fa-box"></i> Inventory Records</h2>
    <div class="actions">
      <div class="search-wrapper">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="Search by product name..." class="input-search" id="inventorySearch" />
      </div>
      @if(auth()->user()->can('manageInventory'))
      <button class="btn-primary" id="openAddItem">
        <i class="fas fa-plus"></i> Add New Item
      </button>
      @endif
    </div>
  </div>

{{-- TABS --}}
  <div style="display:flex; gap:1rem; margin-bottom:1.5rem;">
    <button class="btn-tab active" id="tabActive" onclick="switchTab('active')">
      <i class="fas fa-box"></i> Active Items
    </button>
    <button class="btn-tab" id="tabArchived" onclick="switchTab('archived')">
      <i class="fas fa-archive"></i> Archived Items
    </button>
  </div>

  {{-- ACTIVE ITEMS TABLE --}}
  <div id="activeTable">
    <div class="table-container">
      <table id="inventoryTable">
        <thead>
          <tr>
            <th>Product Name</th><th>Category</th><th>Quantity</th>
            <th>Unit</th><th>Reorder Level</th><th>Status</th>
            <th>Date Updated</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($items as $item)
          <tr>
            <td>{{ $item->product_name }}</td>
            <td>{{ $item->category }}</td>
            <td>{{ $item->quantity }}</td>
            <td>{{ $item->unit }}</td>
            <td>{{ $item->reorder_level }}</td>
            <td><span class="status-tag {{ $item->status === 'In Stock' ? 'active' : 'inactive' }}">{{ $item->status }}</span></td>
            <td>{{ $item->updated_at->format('Y-m-d') }}</td>
            <td class="actions-col">
              @if(auth()->user()->can('manageInventory'))
              <button class="action-btn edit-btn" data-id="{{ $item->id }}"
                data-name="{{ $item->product_name }}" data-category="{{ $item->category }}"
                data-quantity="{{ $item->quantity }}" data-unit="{{ $item->unit }}"
                data-reorder="{{ $item->reorder_level }}">
                <i class="fas fa-pen"></i>
              </button>
              @endif
             @if(auth()->user()->can('manageInventory'))
              <form action="{{ route('inventory.archive', $item) }}" method="POST" class="d-inline">
                @csrf
                <button type="button" class="action-btn archive-btn" title="Archive">
                  <i class="fas fa-archive"></i>
                </button>
              </form>
              @endif
            </td>
          </tr>
          @empty
          <tr><td colspan="8" style="text-align:center;">No inventory items yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- ARCHIVED ITEMS TABLE --}}
  <div id="archivedTable" style="display:none;">
    <div class="table-container">
      <table id="archivedInventoryTable">
        <thead>
          <tr>
            <th>Product Name</th><th>Category</th><th>Quantity</th>
            <th>Unit</th><th>Reorder Level</th><th>Status</th>
            <th>Date Updated</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($archivedItems as $item)
          <tr>
            <td>{{ $item->product_name }}</td>
            <td>{{ $item->category }}</td>
            <td>{{ $item->quantity }}</td>
            <td>{{ $item->unit }}</td>
            <td>{{ $item->reorder_level }}</td>
            <td><span class="status-tag inactive">{{ $item->status }}</span></td>
            <td>{{ $item->updated_at->format('Y-m-d') }}</td>
            <td class="actions-col">
              <form action="{{ route('inventory.restore', $item) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="action-btn" title="Restore" style="color:#1a6b47;">
                  <i class="fas fa-undo"></i>
                </button>
              </form>
              @if(auth()->user()->can('deleteRecords'))
              <form action="{{ route('inventory.destroy', $item) }}" method="POST" class="d-inline delete-form">
                @csrf @method('DELETE')
                <button type="button" class="action-btn delete-btn" title="Permanently Delete">
                  <i class="fas fa-trash"></i>
                </button>
              </form>
              @endif
            </td>
          </tr>
          @empty
          <tr><td colspan="8" style="text-align:center;">No archived items.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</section>
@endsection

@push('modals')
{{-- ADD MODAL --}}
<div id="addItemModal" class="modal">
  <div class="modal-content">
    <h2>Add New Item</h2>
    <form action="{{ route('inventory.store') }}" method="POST" class="form-grid">
      @csrf
      <div class="form-group" style="grid-column: span 2;">
        <label>Category</label>
        <select name="category" id="addCategory" required onchange="filterProducts('add')">
          <option value="" disabled selected>Select a category</option>
          <option value="Cheese Product">Cheese Product</option>
          <option value="Raw Materials">Raw Materials</option>
        </select>
      </div>
      <div class="form-group" style="grid-column: span 2;">
        <label>Product Name</label>
        <select name="product_name" id="addProduct" required onchange="autoFillUnit('add')">
          <option value="" disabled selected>Select a product</option>
        </select>
      </div>
      <div class="form-group">
        <label>Quantity</label>
        <input type="number" name="quantity" step="0.01" min="0" placeholder="0" required />
      </div>
      <div class="form-group">
        <label>Unit</label>
        <select name="unit" id="addUnit">
          <option value="kg">kg</option>
          <option value="L">L</option>
          <option value="g">g</option>
          <option value="mL">mL</option>
          <option value="pcs">pcs</option>
        </select>
      </div>
      <div class="form-group" style="grid-column: span 2;">
        <label>Reorder Level</label>
        <input type="number" name="reorder_level" step="0.01" min="0" placeholder="Minimum stock before alert" required />
      </div>
      <div class="modal-buttons">
        <button type="button" id="closeAddItem" class="btn-cancel">Cancel</button>
        <button type="submit" class="btn-save"><i class="fas fa-plus"></i> Add Item</button>
      </div>
    </form>
  </div>
</div>

{{-- EDIT MODAL --}}
<div id="editItemModal" class="modal">
  <div class="modal-content">
    <h2>Edit Inventory Item</h2>
    <form id="editItemForm" action="" method="POST" class="form-grid">
      @csrf @method('PUT')
      <div class="form-group" style="grid-column: span 2;">
        <label>Category</label>
        <select name="category" id="editCategory" required onchange="filterProducts('edit')">
          <option value="" disabled selected>Select a category</option>
          <option value="Cheese Product">Cheese Product</option>
          <option value="Raw Materials">Raw Materials</option>
        </select>
      </div>
      <div class="form-group" style="grid-column: span 2;">
        <label>Product Name</label>
        <select name="product_name" id="editProduct" required onchange="autoFillUnit('edit')">
          <option value="" disabled selected>Select a product</option>
        </select>
      </div>
      <div class="form-group">
        <label>Quantity</label>
        <input type="number" name="quantity" id="editQuantity" step="0.01" min="0" required />
      </div>
      <div class="form-group">
        <label>Unit</label>
        <select name="unit" id="editUnit">
          <option value="kg">kg</option>
          <option value="L">L</option>
          <option value="g">g</option>
          <option value="mL">mL</option>
          <option value="pcs">pcs</option>
        </select>
      </div>
      <div class="form-group" style="grid-column: span 2;">
        <label>Reorder Level</label>
        <input type="number" name="reorder_level" id="editReorder" step="0.01" min="0" required />
      </div>
      <div class="modal-buttons">
        <button type="button" id="closeEditItem" class="btn-cancel">Cancel</button>
        <button type="submit" class="btn-save">Save Changes</button>
      </div>
    </form>
  </div>
</div>

{{-- ARCHIVE CONFIRM MODAL --}}
<div id="archiveItemModal" class="modal">
  <div class="modal-content small-modal">
    <h2>Confirm Archive</h2>
    <p>Are you sure you want to archive this item? You can restore it later from the Archived Items tab.</p>
    <div class="modal-buttons">
      <button class="btn-cancel" id="cancelArchive">Cancel</button>
      <button class="btn-delete" id="confirmArchive"><i class="fas fa-archive"></i> Archive</button>
    </div>
  </div>
</div>

{{-- DELETE CONFIRM MODAL --}}
<div id="deleteItemModal" class="modal">
  <div class="modal-content small-modal">
    <h2>Confirm Delete</h2>
    <p>Are you sure you want to permanently delete this item? This cannot be undone.</p>
    <div class="modal-buttons">
      <button class="btn-cancel" id="cancelDelete">Cancel</button>
      <button class="btn-delete" id="confirmDelete"><i class="fas fa-trash"></i> Delete</button>
    </div>
  </div>
</div>
@endpush

@push('scripts')
<script>
const cheeseProducts = @json($cheeseProducts);
const rawMaterials   = @json($rawMaterials);
const unitMap        = @json($unitMap);

// Auto hide success
setTimeout(() => document.getElementById('successAlert')?.remove(), 4000);

// Tab switching
function switchTab(tab) {
  document.getElementById('activeTable').style.display   = tab === 'active'   ? 'block' : 'none';
  document.getElementById('archivedTable').style.display = tab === 'archived' ? 'block' : 'none';
  document.getElementById('tabActive').classList.toggle('active',   tab === 'active');
  document.getElementById('tabArchived').classList.toggle('active', tab === 'archived');
}

function filterProducts(mode) {
  const category      = document.getElementById(mode + 'Category').value;
  const productSelect = document.getElementById(mode + 'Product');
  productSelect.innerHTML = '<option value="" disabled selected>Select a product</option>';
  let list = category === 'Cheese Product' ? cheeseProducts : rawMaterials;
  list.forEach(p => {
    const opt = document.createElement('option');
    opt.value = p; opt.textContent = p;
    productSelect.appendChild(opt);
  });
}

function autoFillUnit(mode) {
  const product    = document.getElementById(mode + 'Product').value;
  const unitSelect = document.getElementById(mode + 'Unit');
  if (unitMap[product]) unitSelect.value = unitMap[product];
}

// Add modal
document.getElementById('openAddItem')?.addEventListener('click', () => {
  document.getElementById('addItemModal').classList.add('active');
  document.body.classList.add('modal-open');
});
document.getElementById('closeAddItem')?.addEventListener('click', () => {
  document.getElementById('addItemModal').classList.remove('active');
  document.body.classList.remove('modal-open');
});

// Edit modal
document.querySelectorAll('.edit-btn').forEach(btn => {
  btn.addEventListener('click', function () {
    const id       = this.dataset.id;
    const category = this.dataset.category;
    const name     = this.dataset.name;
    document.getElementById('editItemForm').action = `/inventory/${id}`;
    document.getElementById('editCategory').value  = category;
    document.getElementById('editQuantity').value  = this.dataset.quantity;
    document.getElementById('editUnit').value      = this.dataset.unit;
    document.getElementById('editReorder').value   = this.dataset.reorder;
    filterProducts('edit');
    document.getElementById('editProduct').value = name;
    document.getElementById('editItemModal').classList.add('active');
    document.body.classList.add('modal-open');
  });
});
document.getElementById('closeEditItem')?.addEventListener('click', () => {
  document.getElementById('editItemModal').classList.remove('active');
  document.body.classList.remove('modal-open');
});

// Archive modal
let pendingArchiveForm = null;
document.querySelectorAll('.archive-btn').forEach(btn => {
  btn.addEventListener('click', function () {
    pendingArchiveForm = this.closest('form');
    document.getElementById('archiveItemModal').classList.add('active');
    document.body.classList.add('modal-open');
  });
});
document.getElementById('confirmArchive')?.addEventListener('click', () => {
  pendingArchiveForm?.submit();
});
document.getElementById('cancelArchive')?.addEventListener('click', () => {
  document.getElementById('archiveItemModal').classList.remove('active');
  document.body.classList.remove('modal-open');
});

// Delete modal
let pendingDeleteForm = null;
document.querySelectorAll('.delete-btn').forEach(btn => {
  btn.addEventListener('click', function () {
    pendingDeleteForm = this.closest('.delete-form');
    document.getElementById('deleteItemModal').classList.add('active');
    document.body.classList.add('modal-open');
  });
});
document.getElementById('confirmDelete')?.addEventListener('click', () => {
  pendingDeleteForm?.submit();
});
document.getElementById('cancelDelete')?.addEventListener('click', () => {
  document.getElementById('deleteItemModal').classList.remove('active');
  document.body.classList.remove('modal-open');
});

// Search
document.getElementById('inventorySearch')?.addEventListener('input', function () {
  const q = this.value.toLowerCase();
  document.querySelectorAll('#inventoryTable tbody tr, #archivedInventoryTable tbody tr').forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
});
</script>
@endpush