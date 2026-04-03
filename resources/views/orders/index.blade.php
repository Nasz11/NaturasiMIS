@extends('layouts.app')
@section('title', 'Orders')
@section('page-title', 'Production Orders')
@section('page-subtitle', 'Create production orders and automatically deduct ingredients from inventory.')

@section('content')
<section id="ordersSection">

  {{-- SUCCESS / ERROR ALERTS --}}
  @if(session('success'))
  <div class="success-message" id="successAlert">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
  </div>
  @endif
  @if($errors->has('insufficient'))
  <div class="alert-message" id="errorAlert">
    <i class="fas fa-exclamation-circle"></i> {{ $errors->first('insufficient') }}
  </div>
  @endif

  {{-- MODULE HEADER --}}
  <div class="module-header">
    <h2><i class="fas fa-clipboard-list"></i> Production Orders</h2>
   <div class="actions">
  <form method="GET" action="{{ route('orders.index') }}" style="display:flex;">
    <div class="search-wrapper">
      <i class="fas fa-search"></i>
      <input type="text" name="search" placeholder="Search by P.O#, product, status..." class="input-search" value="{{ $search ?? '' }}" />
    </div>
  </form>
  <button class="btn-primary" id="openCreateOrder">
    <i class="fas fa-plus"></i> New Order
  </button>
</div>
</div>

  {{-- ORDERS TABLE --}}
  <div class="table-container">
    <table id="ordersTable">
      <thead>
        <tr>
          <th>P.O. Number</th>
<th>Cheese Product</th>
<th>Quantity</th>
<th>Unit</th>
<th>Status</th>
<th>Created By</th>
<th>Date Created</th>
<th>Confirmed At</th>
<th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($orders as $order)
        <tr>
         <td>{{ $order->po_number ?? '—' }}</td>
<td>{{ $order->cheese_product }}</td>
<td>{{ $order->quantity }}</td>
<td>{{ $order->unit }}</td>
<td>
    <span class="status-tag {{ $order->status === 'Confirmed' ? 'active' : 'inactive' }}">
      {{ $order->status }}
    </span>
</td>
<td>{{ $order->createdBy?->username ?? 'N/A' }}</td>
<td>{{ $order->created_at->format('Y-m-d') }}</td>
<td>{{ $order->confirmed_at ? $order->confirmed_at->format('Y-m-d H:i') : '—' }}</td>
<td>
  @if($order->status === 'Pending')
    <form method="POST" action="{{ route('orders.updateStatus', $order) }}" style="display:inline;">
      @csrf @method('PATCH')
      <input type="hidden" name="status" value="Confirmed">
      <button type="submit" class="btn-primary" style="padding:6px 12px; font-size:0.8rem;">
        <i class="fas fa-check"></i> Confirm
      </button>
    </form>
    <form method="POST" action="{{ route('orders.updateStatus', $order) }}" style="display:inline; margin-left:4px;">
      @csrf @method('PATCH')
      <input type="hidden" name="status" value="Cancelled">
      <button type="submit" class="btn-cancel" style="padding:6px 12px; font-size:0.8rem;">
        <i class="fas fa-times"></i> Cancel
      </button>
    </form>
  @else
    <span style="color:#aaa; font-size:0.85rem;">—</span>
  @endif
</td>
        </tr>
        @empty
<tr><td colspan="8" style="text-align:center;">No orders yet.</td></tr>
        @endforelse
      </tbody>
    </table>
</div>
 <div class="pagination-wrapper">
    <p class="pagination-info">Showing {{ $orders->firstItem() ?? 0 }}–{{ $orders->lastItem() ?? 0 }} of {{ $orders->total() }} orders</p>
    <div class="custom-pagination-nav">
      @if($orders->onFirstPage()) <span class="pg-btn pg-disabled">&#8249;</span>
      @else <a href="{{ $orders->previousPageUrl() }}" class="pg-btn">&#8249;</a> @endif
      @php $current=$orders->currentPage(); $last=$orders->lastPage(); $pages=[]; for($i=1;$i<=$last;$i++){if($i==1||$i==$last||abs($i-$current)<=1)$pages[]=$i;} $pages=array_unique($pages); sort($pages); @endphp
      @php $prev=null; @endphp
      @foreach($pages as $page)
        @if($prev!==null && $page-$prev>1) <span class="pg-btn pg-dots">···</span> @endif
        @if($page==$current) <span class="pg-btn pg-active">{{ $page }}</span>
        @else <a href="{{ $orders->url($page) }}" class="pg-btn">{{ $page }}</a> @endif
        @php $prev=$page; @endphp
      @endforeach
      @if($orders->hasMorePages()) <a href="{{ $orders->nextPageUrl() }}" class="pg-btn">&#8250;</a>
      @else <span class="pg-btn pg-disabled">&#8250;</span> @endif
    </div>
  </div>
</section>
@endsection

@push('modals')
{{-- CREATE ORDER MODAL --}}
<div id="createOrderModal" class="modal">
  <div class="modal-content" style="max-width:650px;">
    <h2><i class="fas fa-clipboard-list"></i> New Production Order</h2>

    {{-- STEP 1: Input products --}}
    <div id="step1">
      <p style="color:#666; margin-bottom:1.5rem; font-size:0.95rem;">
        Add the cheese products you want to produce today.
      </p>

      <div id="orderItems">
        <div class="order-item-row" style="display:grid; grid-template-columns:1fr 1fr auto; gap:1rem; margin-bottom:1rem;">
          <div class="form-group" style="margin:0;">
            <label>Cheese Product</label>
            <select class="product-select" required>
              <option value="" disabled selected>Select product</option>
              @foreach($cheeseProducts as $product)
              <option value="{{ $product }}">{{ $product }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group" style="margin:0;">
            <label>Quantity (kg)</label>
            <input type="number" class="quantity-input" step="0.01" min="0.01" placeholder="e.g. 10" required />
          </div>
          <div style="display:flex; align-items:flex-end;">
            <button type="button" class="btn-cancel remove-row" style="padding:10px 14px;" disabled>
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
      </div>

      <div id="orderValidationMsg" class="alert-message" style="display:none; margin-bottom:1rem;">
  <i class="fas fa-exclamation-circle"></i> Please fill in all product and quantity fields.
</div>
      <button type="button" class="btn-reset" id="addOrderRow" style="margin-bottom:1.5rem;">
        <i class="fas fa-plus"></i> Add Another Product
      </button>

      <div class="modal-buttons" style="margin-top:0;">
        <button type="button" class="btn-cancel" id="closeCreateOrder">Cancel</button>
        <button type="button" class="btn-primary" id="previewOrder">
          <i class="fas fa-eye"></i> Preview Ingredients
        </button>
      </div>
    </div>

    {{-- STEP 2: Preview ingredients --}}
    <div id="step2" style="display:none;">
      <p style="color:#666; margin-bottom:1.5rem; font-size:0.95rem;">
        Review the ingredients needed. Green means sufficient stock, red means insufficient.
      </p>

      <div class="table-container" style="margin-top:0; box-shadow:none; padding:0;">
        <table id="previewTable">
          <thead>
            <tr>
              <th>Ingredient</th>
              <th>Needed</th>
              <th>Available</th>
              <th>Unit</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody id="previewBody"></tbody>
        </table>
      </div>

      <div id="insufficientWarning" style="display:none; margin-top:1rem;" class="alert-message">
        <i class="fas fa-exclamation-circle"></i>
        Some ingredients are insufficient. You cannot confirm this order.
      </div>

      <div class="modal-buttons" style="margin-top:1.5rem;">
        <button type="button" class="btn-cancel" id="backToStep1">
          <i class="fas fa-arrow-left"></i> Back
        </button>
        <button type="button" class="btn-primary" id="confirmOrder">
          <i class="fas fa-check"></i> Confirm & Deduct Inventory
        </button>
      </div>
    </div>

  </div>
</div>

{{-- HIDDEN CONFIRM FORM --}}
<form id="confirmOrderForm" action="{{ route('orders.confirm') }}" method="POST" style="display:none;">
  @csrf
  <div id="confirmOrderInputs"></div>
</form>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

const openModal  = (m) => { m?.classList.add('active'); document.body.classList.add('modal-open'); };
const closeModal = (m) => { m?.classList.remove('active'); document.body.classList.remove('modal-open'); };

// Auto-hide alerts
setTimeout(() => document.getElementById('successAlert')?.remove(), 4000);
setTimeout(() => document.getElementById('errorAlert')?.remove(), 5000);

// Open/close modal
document.getElementById('openCreateOrder')?.addEventListener('click', () => {
  resetModal();
  openModal(document.getElementById('createOrderModal'));
});
document.getElementById('closeCreateOrder')?.addEventListener('click', () => {
  closeModal(document.getElementById('createOrderModal'));
});

// Add another product row
document.getElementById('addOrderRow')?.addEventListener('click', () => {
  const container = document.getElementById('orderItems');
  const row = document.createElement('div');
  row.className = 'order-item-row';
  row.style.cssText = 'display:grid; grid-template-columns:1fr 1fr auto; gap:1rem; margin-bottom:1rem;';
  row.innerHTML = `
    <div class="form-group" style="margin:0;">
      <label>Cheese Product</label>
      <select class="product-select" required>
        <option value="" disabled selected>Select product</option>
        @foreach($cheeseProducts as $product)
        <option value="{{ $product }}">{{ $product }}</option>
        @endforeach
      </select>
    </div>
    <div class="form-group" style="margin:0;">
      <label>Quantity (kg)</label>
      <input type="number" class="quantity-input" step="0.01" min="0.01" placeholder="e.g. 10" required />
    </div>
    <div style="display:flex; align-items:flex-end;">
      <button type="button" class="btn-cancel remove-row" style="padding:10px 14px;">
        <i class="fas fa-times"></i>
      </button>
    </div>
  `;
  container.appendChild(row);
  updateRemoveButtons();
});

// Remove row
document.getElementById('orderItems')?.addEventListener('click', (e) => {
  if (e.target.closest('.remove-row')) {
    e.target.closest('.order-item-row').remove();
    updateRemoveButtons();
  }
});

function updateRemoveButtons() {
  const rows = document.querySelectorAll('.order-item-row');
  rows.forEach(row => {
    const btn = row.querySelector('.remove-row');
    btn.disabled = rows.length === 1;
  });
}

// Preview ingredients
document.getElementById('previewOrder')?.addEventListener('click', async () => {
  const rows = document.querySelectorAll('.order-item-row');
  const items = [];

  let valid = true;
  rows.forEach(row => {
    const product  = row.querySelector('.product-select').value;
    const quantity = row.querySelector('.quantity-input').value;
    if (!product || !quantity || quantity <= 0) { valid = false; return; }
    items.push({ product, quantity });
  });

  if (!valid) {
    document.getElementById('orderValidationMsg').style.display = 'flex';
    return;
}
document.getElementById('orderValidationMsg').style.display = 'none';

  try {
    const response = await fetch('{{ route("orders.preview") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify({ items }),
    });

    const data = await response.json();

    const tbody = document.getElementById('previewBody');
    tbody.innerHTML = '';

    data.preview.forEach(row => {
      const sufficient = row.available >= row.needed;
      tbody.innerHTML += `
        <tr>
          <td>${row.ingredient}</td>
          <td>${row.needed.toFixed(2)}</td>
          <td>${row.available.toFixed(2)}</td>
          <td>${row.unit}</td>
          <td><span class="status-tag ${sufficient ? 'active' : 'inactive'}">${sufficient ? 'OK' : 'Insufficient'}</span></td>
        </tr>
      `;
    });

    document.getElementById('insufficientWarning').style.display = data.insufficient > 0 ? 'flex' : 'none';
    document.getElementById('confirmOrder').disabled = data.insufficient > 0;

    window._orderItems = items;

    document.getElementById('step1').style.display = 'none';
    document.getElementById('step2').style.display = 'block';

  } catch (err) {
    alert('Something went wrong. Please try again.');
  }
});

// Back to step 1
document.getElementById('backToStep1')?.addEventListener('click', () => {
  document.getElementById('step1').style.display = 'block';
  document.getElementById('step2').style.display = 'none';
});

// Confirm order
document.getElementById('confirmOrder')?.addEventListener('click', () => {
  const items = window._orderItems;
  const inputsDiv = document.getElementById('confirmOrderInputs');
  inputsDiv.innerHTML = '';

  items.forEach((item, i) => {
    inputsDiv.innerHTML += `
      <input type="hidden" name="items[${i}][product]"  value="${item.product}">
      <input type="hidden" name="items[${i}][quantity]" value="${item.quantity}">
    `;
  });

  document.getElementById('confirmOrderForm').submit();
});

function resetModal() {
  document.getElementById('step1').style.display = 'block';
  document.getElementById('step2').style.display = 'none';

  const container = document.getElementById('orderItems');
  const rows = container.querySelectorAll('.order-item-row');
  rows.forEach((row, i) => { if (i > 0) row.remove(); });

  const firstRow = container.querySelector('.order-item-row');
  if (firstRow) {
    firstRow.querySelector('.product-select').value = '';
    firstRow.querySelector('.quantity-input').value = '';
  }
}

});
</script>
@endpush