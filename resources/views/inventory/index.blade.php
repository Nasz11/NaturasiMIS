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
  <div class="module-header">
    <h2><i class="fas fa-box"></i> Inventory Records</h2>
    <div class="actions">
      <div class="search-wrapper">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="Search by product name..." class="input-search" id="inventorySearch" />
      </div>
      @if(auth()->user()->can('manageInventory'))
      <button class="btn-primary inventory-add-btn" id="openAddItem">
        <i class="fas fa-plus"></i> Add New Item
      </button>
      @endif
    </div>
  </div>

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
            @if(auth()->user()->can('deleteRecords'))
            <form action="{{ route('inventory.destroy', $item) }}" method="POST" class="d-inline delete-form">
              @csrf @method('DELETE')
              <button type="button" class="action-btn delete-btn"><i class="fas fa-trash"></i></button>
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
</section>

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

{{-- DELETE MODAL --}}
<div id="deleteItemModal" class="modal">
  <div class="modal-content small-modal">
    <h2>Confirm Delete</h2>
    <p>Are you sure you want to remove this item from inventory?</p>
    <div class="modal-buttons">
      <button class="btn-cancel" id="cancelDelete">Cancel</button>
      <button class="btn-save btn-delete" id="confirmDelete"><i class="fas fa-trash"></i> Delete</button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
const cheeseProducts = @json($cheeseProducts);
const rawMaterials   = @json($rawMaterials);
const unitMap        = @json($unitMap);

function filterProducts(mode) {
  const category = document.getElementById(mode + 'Category').value;
  const productSelect = document.getElementById(mode + 'Product');
  productSelect.innerHTML = '<option value="" disabled selected>Select a product</option>';

  let list = [];
  if (category === 'Cheese Product') list = cheeseProducts;
  if (category === 'Raw Materials')  list = rawMaterials;

  list.forEach(p => {
    const opt = document.createElement('option');
    opt.value = p; opt.textContent = p;
    productSelect.appendChild(opt);
  });
}

function autoFillUnit(mode) {
  const product = document.getElementById(mode + 'Product').value;
  const unitSelect = document.getElementById(mode + 'Unit');
  if (unitMap[product]) unitSelect.value = unitMap[product];
}

// Add modal
document.getElementById('openAddItem')?.addEventListener('click', () => openModal(document.getElementById('addItemModal')));
document.getElementById('closeAddItem')?.addEventListener('click', () => closeModal(document.getElementById('addItemModal')));

// Edit modal
document.querySelectorAll('.edit-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const id       = btn.dataset.id;
    const category = btn.dataset.category;
    const name     = btn.dataset.name;

    document.getElementById('editItemForm').action    = `/inventory/${id}`;
    document.getElementById('editCategory').value     = category;
    document.getElementById('editQuantity').value     = btn.dataset.quantity;
    document.getElementById('editUnit').value         = btn.dataset.unit;
    document.getElementById('editReorder').value      = btn.dataset.reorder;

    // populate product dropdown then set value
    filterProducts('edit');
    document.getElementById('editProduct').value = name;

    openModal(document.getElementById('editItemModal'));
  });
});
document.getElementById('closeEditItem')?.addEventListener('click', () => closeModal(document.getElementById('editItemModal')));

// Delete confirmation
let pendingDeleteForm = null;
document.querySelectorAll('.delete-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    pendingDeleteForm = btn.closest('.delete-form');
    openModal(document.getElementById('deleteItemModal'));
  });
});
document.getElementById('confirmDelete')?.addEventListener('click', () => pendingDeleteForm?.submit());
document.getElementById('cancelDelete')?.addEventListener('click', () => closeModal(document.getElementById('deleteItemModal')));

// Search filter
document.getElementById('inventorySearch')?.addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('#inventoryTable tbody tr').forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
});
</script>
@endpush