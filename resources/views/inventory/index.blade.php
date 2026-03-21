@extends('layouts.app')
@section('title', 'Inventory')
@section('page-title', 'Inventory Management')
@section('page-subtitle', 'Manage all product and material stocks efficiently.')

@section('content')
<section id="inventory">
  <div class="module-header">
    <h2><i class="fas fa-box"></i> Inventory Records</h2>
    <div class="actions">
      <div class="search-wrapper">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="Search by product name..." class="input-search" id="inventorySearch" />
      </div>
      @if(auth()->user()->role === 'admin' || auth()->user()->role === 'inventory')
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
            @if(auth()->user()->role === 'admin' || auth()->user()->role === 'inventory')
            <button class="action-btn edit-btn"
              data-id="{{ $item->id }}"
              data-name="{{ $item->product_name }}"
              data-category="{{ $item->category }}"
              data-quantity="{{ $item->quantity }}"
              data-unit="{{ $item->unit }}"
              data-reorder="{{ $item->reorder_level }}">
              <i class="fas fa-pen"></i>
            </button>
            @endif
            @if(auth()->user()->role === 'admin')
            <form action="{{ route('inventory.destroy', $item) }}" method="POST" class="d-inline delete-form">
              @csrf @method('DELETE')
              <button type="button" class="action-btn delete-btn"><i class="fas fa-trash"></i></button>
            </form>
            @endif
          </td>
        </tr>
        @empty
        <tr><td colspan="8" style="text-align:center; padding: 2rem; color: #888;">No inventory items yet. Click "Add New Item" to get started.</td></tr>
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
      <div class="form-group">
        <label for="add_name">Product Name</label>
        <select name="product_name" id="add_name" required>
          <option value="">Select a product</option>
          @foreach(['Mozzarella Cheese','Cheddar Cheese','Parmesan Cheese','Gouda Cheese','Fresh Milk','Rennet','Salt','Cheese Cultures','Packaging Boxes','Vacuum Bags','Labels'] as $p)
            <option value="{{ $p }}">{{ $p }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label for="add_category">Category</label>
        <select name="category" id="add_category" required>
          <option value="">Select a category</option>
          @foreach(['Cheese Product','Raw Materials','Packaging','Ingredients','Finished Goods','Equipment','Cleaning Supplies'] as $c)
            <option value="{{ $c }}">{{ $c }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label for="add_quantity">Quantity</label>
        <input type="number" name="quantity" id="add_quantity" placeholder="e.g. 100" step="0.01" min="0" required />
      </div>
      <div class="form-group">
        <label for="add_unit">Unit</label>
        <select name="unit" id="add_unit">
          <option value="kg">kg</option><option value="pcs">pcs</option>
          <option value="L">L</option><option value="g">g</option><option value="mL">mL</option>
        </select>
      </div>
      <div class="form-group">
        <label for="add_reorder">Reorder Point</label>
        <input type="number" name="reorder_level" id="add_reorder" placeholder="e.g. 50" step="0.01" min="0" required />
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
      <div class="form-group">
        <label for="editName">Product Name</label>
        <select name="product_name" id="editName" required>
          @foreach(['Mozzarella Cheese','Cheddar Cheese','Parmesan Cheese','Gouda Cheese','Fresh Milk','Rennet','Salt','Cheese Cultures','Packaging Boxes','Vacuum Bags','Labels'] as $p)
            <option value="{{ $p }}">{{ $p }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label for="editCategory">Category</label>
        <select name="category" id="editCategory" required>
          @foreach(['Cheese Product','Raw Materials','Packaging','Ingredients','Finished Goods','Equipment','Cleaning Supplies'] as $c)
            <option value="{{ $c }}">{{ $c }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label for="editQuantity">Quantity</label>
        <input type="number" name="quantity" id="editQuantity" step="0.01" min="0" required />
      </div>
      <div class="form-group">
        <label for="editUnit">Unit</label>
        <select name="unit" id="editUnit">
          <option value="kg">kg</option><option value="pcs">pcs</option>
          <option value="L">L</option><option value="g">g</option><option value="mL">mL</option>
        </select>
      </div>
      <div class="form-group">
        <label for="editReorder">Reorder Level</label>
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
const openModal  = (m) => { m?.classList.add('active'); document.body.classList.add('modal-open'); };
const closeModal = (m) => { m?.classList.remove('active'); document.body.classList.remove('modal-open'); };

document.getElementById('openAddItem')?.addEventListener('click', () => openModal(document.getElementById('addItemModal')));
document.getElementById('closeAddItem')?.addEventListener('click', () => closeModal(document.getElementById('addItemModal')));

document.querySelectorAll('.edit-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.getElementById('editItemForm').action = `/inventory/${btn.dataset.id}`;
    document.getElementById('editName').value     = btn.dataset.name;
    document.getElementById('editCategory').value = btn.dataset.category;
    document.getElementById('editQuantity').value = btn.dataset.quantity;
    document.getElementById('editUnit').value     = btn.dataset.unit;
    document.getElementById('editReorder').value  = btn.dataset.reorder;
    openModal(document.getElementById('editItemModal'));
  });
});
document.getElementById('closeEditItem')?.addEventListener('click', () => closeModal(document.getElementById('editItemModal')));

let pendingDeleteForm = null;
document.querySelectorAll('.delete-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    pendingDeleteForm = btn.closest('.delete-form');
    openModal(document.getElementById('deleteItemModal'));
  });
});
document.getElementById('confirmDelete')?.addEventListener('click', () => pendingDeleteForm?.submit());
document.getElementById('cancelDelete')?.addEventListener('click', () => closeModal(document.getElementById('deleteItemModal')));

document.getElementById('inventorySearch')?.addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('#inventoryTable tbody tr').forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
});
</script>
@endpush
