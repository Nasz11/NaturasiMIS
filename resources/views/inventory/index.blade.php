@extends('layouts.app')
@section('title', 'Inventory')
@section('page-title', 'Inventory Management')
@section('page-subtitle', 'Manage all product and material stocks efficiently.')
@section('suppressGlobalErrors', true)

@php

$rawMaterials = [
    'Cagliata', 'Fresh Milk', 'Cream', 'Iodized Salt', 'Rock Salt',
    'Trisodium', 'Rennet', 'Citric Acid', 'Palm Oil', 'Skimmed Milk',
    'High Melt Starch', 'Butter Flavor', 'Butter Milk', 'Parmesan Flavor',
    'Cheddar Flavor', 'Milk Powder',
];
$unitMap = [
    'Cagliata'               => 'kg', 'Fresh Milk'             => 'L',
    'Cream'                  => 'L',  'Iodized Salt'           => 'kg',
    'Rock Salt'              => 'kg', 'Trisodium'              => 'kg',
    'Rennet'                 => 'kg', 'Citric Acid'            => 'kg',
    'Palm Oil'               => 'L',  'Skimmed Milk'           => 'L',
    'High Melt Starch'       => 'kg', 'Butter Flavor'          => 'kg',
    'Butter Milk'            => 'L',  'Parmesan Flavor'        => 'kg',
    'Cheddar Flavor'         => 'kg', 'Milk Powder'            => 'kg',
];
@endphp

@section('content')
<section id="inventorySection">

  @if(session('success'))
  <div class="success-message" id="successAlert">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
  </div>
@endif

@if($errors->has('archive'))
  <div class="error-message" id="errorAlert" style="background:#fdecea;color:#c62828;padding:12px 16px;border-radius:8px;margin-bottom:1rem;display:flex;align-items:center;gap:8px;">
    <i class="fas fa-exclamation-circle"></i> {{ $errors->first('archive') }}
  </div>
@endif
@if($errors->has('quantity'))
  <div class="error-message" id="errorAlert" style="background:#fdecea;color:#c62828;padding:12px 16px;border-radius:8px;margin-bottom:1rem;display:flex;align-items:center;gap:8px;">
    <i class="fas fa-exclamation-circle"></i> {{ $errors->first('quantity') }}
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
      <button class="btn-primary" id="openAddItem" style="display:none;">
        <i class="fas fa-plus"></i> Add New Item
      </button>
      @endif
    </div>
  </div>

  {{-- TABS + DATE + COLUMNS all in one row --}}
  <div style="display:flex;align-items:center;gap:8px;margin-bottom:1.5rem;flex-wrap:wrap;">

    {{-- Tab Pills --}}
    <div style="display:flex;gap:4px;background:#f0f0f0;border-radius:10px;padding:4px;">
      <button class="inv-tab" id="tabActive" onclick="switchTab('active')"
        style="display:flex;align-items:center;gap:6px;padding:7px 16px;border-radius:7px;border:none;font-size:0.85rem;font-weight:600;cursor:pointer;transition:all 0.2s;background:#1a6b47;color:#fff;box-shadow:0 2px 6px rgba(26,107,71,0.25);">
        <i class="fas fa-box"></i> Current Stock
      </button>
      <button class="inv-tab" id="tabInbound" onclick="switchTab('inbound')"
        style="display:flex;align-items:center;gap:6px;padding:7px 16px;border-radius:7px;border:none;font-size:0.85rem;font-weight:600;cursor:pointer;transition:all 0.2s;background:transparent;color:#555;">
        <i class="fas fa-arrow-circle-down"></i> Inbound
      </button>
      <button class="inv-tab" id="tabOutbound" onclick="switchTab('outbound')"
        style="display:flex;align-items:center;gap:6px;padding:7px 16px;border-radius:7px;border:none;font-size:0.85rem;font-weight:600;cursor:pointer;transition:all 0.2s;background:transparent;color:#555;">
        <i class="fas fa-arrow-circle-up"></i> Outbound
      </button>
      <button class="inv-tab" id="tabArchived" onclick="switchTab('archived')"
        style="display:flex;align-items:center;gap:6px;padding:7px 16px;border-radius:7px;border:none;font-size:0.85rem;font-weight:600;cursor:pointer;transition:all 0.2s;background:transparent;color:#555;">
        <i class="fas fa-archive"></i> Archived
      </button>
      <button class="inv-tab" id="tabRestock" onclick="switchTab('restock')"
        style="display:flex;align-items:center;gap:6px;padding:7px 16px;border-radius:7px;border:none;font-size:0.85rem;font-weight:600;cursor:pointer;transition:all 0.2s;background:transparent;color:#555;">
        <i class="fas fa-chart-line"></i> Predictive Restock
      </button>
    </div>

    {{-- Date Picker --}}
    <form method="GET" action="{{ route('inventory.index') }}" style="display:flex;align-items:center;">
      <div style="display:flex;align-items:center;gap:6px;background:#fff;border:1px solid #ddd;border-radius:8px;padding:5px 10px;">
        <i class="fas fa-calendar-alt" style="color:#1a6b47;font-size:0.75rem;"></i>
       <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()"
          min="2026-01-01" max="{{ date('Y-m-d') }}"
          style="border:none;outline:none;font-size:0.8rem;font-weight:600;color:#333;background:transparent;cursor:pointer;" />
      </div>
    </form>

    {{-- Columns Dropdown --}}
    <div style="position:relative;display:inline-block;">
      <button id="colToggleBtn" style="display:flex;align-items:center;gap:6px;padding:5px 12px;border-radius:8px;border:1px solid #ddd;background:#fff;font-size:0.8rem;cursor:pointer;font-weight:600;color:#333;">
        <i class="fas fa-table-columns" style="color:#1a6b47;"></i> Columns
        <span id="colActiveCount" style="background:#1a6b47;color:#fff;border-radius:20px;padding:1px 6px;font-size:0.7rem;"></span>
        <i class="fas fa-chevron-down" style="font-size:0.6rem;color:#aaa;"></i>
      </button>
      <div id="colDropdown" style="display:none;position:absolute;top:110%;left:0;background:#fff;border:1px solid #e0e0e0;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,0.12);padding:8px;z-index:999;min-width:220px;">
        <div style="padding:6px 10px 8px;border-bottom:1px solid #f0f0f0;margin-bottom:6px;">
          <span style="font-size:0.75rem;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:0.5px;">Toggle Columns</span>
        </div>
        <div id="colToggleBar" style="display:flex;flex-direction:column;gap:2px;"></div>
      </div>
    </div>

  </div>

  <style>
  #colDropdown.open { display:block !important; }
  #colToggleBar button {
    display:flex;align-items:center;gap:10px;padding:7px 10px;
    border-radius:8px;border:none;background:none;cursor:pointer;
    font-size:0.85rem;color:#333;text-align:left;width:100%;transition:background 0.15s;
  }
  #colToggleBar button:hover { background:#f5f9f7; }
  #colToggleBar button .check-icon {
    width:17px;height:17px;border-radius:4px;border:1.5px solid #ccc;
    display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all 0.15s;
  }
  #colToggleBar button.col-on .check-icon { background:#1a6b47;border-color:#1a6b47;color:#fff; }
  .status-tag.in-stock-muted { background:#e8f5e9; color:#2e7d52; font-weight:600; border-radius:20px; padding:3px 12px; font-size:0.78rem; }
  </style>

  {{-- SUMMARY BAR --}}
  @php
    $totalItems = $items->count();
    $inStockCount = $items->filter(fn($i) => $i->status === 'In Stock')->count();
    $lowStockCount = $items->filter(fn($i) => $i->status === 'Low Stock')->count();
    $outOfStockCount = $items->filter(fn($i) => $i->status === 'Out of Stock')->count();
  @endphp
  <div id="inventorySummaryBar" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:1rem;">
    <span style="background:#f0f0f0;color:#333;padding:5px 14px;border-radius:99px;font-size:0.82rem;font-weight:600;">
      {{ $totalItems }} Total
    </span>
    @if($outOfStockCount > 0)
    <span style="background:#fdecea;color:#c62828;padding:5px 14px;border-radius:99px;font-size:0.82rem;font-weight:700;">
      🔴 {{ $outOfStockCount }} Out of Stock
    </span>
    @endif
    @if($lowStockCount > 0)
    <span style="background:#fff3e0;color:#e65100;padding:5px 14px;border-radius:99px;font-size:0.82rem;font-weight:700;">
      🟠 {{ $lowStockCount }} Low Stock
    </span>
    @endif
    <span style="background:#e8f5e9;color:#1a6b47;padding:5px 14px;border-radius:99px;font-size:0.82rem;font-weight:600;">
      🟢 {{ $inStockCount }} In Stock
    </span>
  </div>

  {{-- CURRENT STOCK TABLE --}}
  <div id="activeTable">
    <div class="table-container" style="overflow-x:auto;">
     <table id="inventoryTable" style="min-width:900px;width:100%;table-layout:auto;">
        <thead>
          <tr id="invHeaderRow"></tr>
        </thead>
        <tbody id="invTableBody">
          @foreach($items as $item)
          @php
            $selectedDate = \Carbon\Carbon::parse($date);
            $starting     = $item->endingInventory($selectedDate->copy()->subDay());
            $inboundQty   = isset($inboundMovements[$item->id]) ? $inboundMovements[$item->id]->sum('quantity') : 0;
            $outboundQty  = isset($outboundMovements[$item->id]) ? $outboundMovements[$item->id]->sum('quantity') : 0;
           $ending        = $starting + $inboundQty - $outboundQty;
$currentStock  = $item->computedQuantity();
$discrepancy   = $ending - $currentStock;
$status = $ending <= 0 ? 'Out of Stock' : ($ending <= $item->reorder_level ? 'Low Stock' : 'In Stock');
          @endphp
          <tr
            data-id="{{ $item->id }}"
            data-product="{{ $item->product_name }}"
            data-name="{{ $item->product_name }}"
            data-category="{{ $item->category }}"
            data-start="{{ number_format($starting, 2) }} {{ $item->unit }}"
            data-inbound="{{ number_format($inboundQty, 2) }} {{ $item->unit }}"
            data-outbound="{{ number_format($outboundQty, 2) }} {{ $item->unit }}"
            data-end="{{ number_format($ending, 2) }} {{ $item->unit }}"
            data-qty="{{ number_format($ending, 2) }}"
            data-unit="{{ $item->unit }}"
            data-unitraw="{{ $item->unit }}"
            data-cost="₱{{ number_format($item->cost_per_unit, 2) }}"
            data-costraw="{{ $item->cost_per_unit }}"
            data-reorderraw="{{ $item->reorder_level }}"
            data-status="{{ $status }}"
            data-discrepancy="{{ number_format($discrepancy, 2) }} {{ $item->unit }}"
            data-discrepancyraw="{{ $discrepancy }}"
            data-canmanage="{{ auth()->user()->can('manageInventory') ? '1' : '0' }}"
            data-archiveurl="{{ route('inventory.archive', $item) }}"
          ></tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="pagination-wrapper" style="margin-top:1rem;">
      <p class="pagination-info" id="invPaginationInfo"></p>
      <div class="custom-pagination-nav" id="invPaginationNav"></div>
    </div>
  </div>

  {{-- INBOUND TABLE --}}
  <div id="inboundTable" style="display:none;">
    <div class="module-header" style="margin-bottom:1rem;">
      <h2 style="font-size:1.1rem;"><i class="fas fa-arrow-circle-down" style="color:#1a6b47;"></i> Inbound Movements</h2>
      @if(auth()->user()->can('manageInventory'))
      <button class="btn-primary" id="openAddInbound">
        <i class="fas fa-plus"></i> Record Inbound
      </button>
      @endif
    </div>
    <div class="table-container">
      <table id="inboundMovementsTable">
        <thead>
          <tr>
           <th>Date</th><th>Product</th><th>Category</th><th>Quantity</th>
            <th>Unit</th><th>Remarks</th><th>Expiry Date</th><th>Recorded By</th>
          </tr>
        </thead>
        <tbody>
          @forelse($inboundMovements->flatten() as $movement)
          <tr>
            <td>{{ \Carbon\Carbon::parse($movement->movement_date)->format('Y-m-d') }}</td>
            <td>{{ $movement->item->product_name }}</td>
            <td>{{ $movement->item->category }}</td>
            <td style="color:#1a6b47;font-weight:600;">+{{ $movement->quantity }}</td>
            <td>{{ $movement->item->unit }}</td>
            <td>{{ $movement->remarks ?? '—' }}</td>
            <td>
              @if($movement->expiry_date)
               @php $daysLeft = (int) now()->diffInDays($movement->expiry_date, false); @endphp
                @if($daysLeft < 0)
                  <span style="color:#c62828;font-weight:600;">⛔ Expired</span>
                @elseif($daysLeft <= 7)
                  <span style="color:#e65100;font-weight:600;">⚠️ {{ $movement->expiry_date->format('Y-m-d') }} ({{ $daysLeft }}d left)</span>
                @else
                  <span style="color:#1a6b47;">{{ $movement->expiry_date->format('Y-m-d') }}</span>
                @endif
              @else
                <span style="color:#aaa;">—</span>
              @endif
            </td>
            <td>{{ $movement->recordedBy->username ?? 'System' }}</td>
          </tr>
          @empty
          <tr><td colspan="8" style="text-align:center;padding:2rem;color:#888;">No inbound movements yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="pagination-wrapper" style="margin-top:1rem;">
      <p class="pagination-info" id="inboundPaginationInfo"></p>
      <div class="custom-pagination-nav" id="inboundPaginationNav"></div>
    </div>
  </div>

  {{-- OUTBOUND TABLE --}}
  <div id="outboundTable" style="display:none;">
    <div class="module-header" style="margin-bottom:1rem;">
      <h2 style="font-size:1.1rem;"><i class="fas fa-arrow-circle-up" style="color:#c62828;"></i> Outbound Movements</h2>
      @if(auth()->user()->can('manageInventory'))
      <button class="btn-primary" id="openAddOutbound">
        <i class="fas fa-plus"></i> Record Outbound
      </button>
      @endif
    </div>
    <div class="table-container">
      <table id="outboundMovementsTable">
        <thead>
          <tr>
            <th>Date</th><th>Product</th><th>Category</th><th>Quantity</th>
            <th>Unit</th><th>Reference</th><th>Remarks</th><th>Recorded By</th>
          </tr>
        </thead>
        <tbody>
         @forelse($outboundMovements->flatten()->sortBy([['movement_date', 'desc'], ['reference', 'desc']]) as $movement)
          <tr>
            <td>{{ \Carbon\Carbon::parse($movement->movement_date)->format('Y-m-d') }}</td>
            <td>{{ $movement->item->product_name }}</td>
            <td>{{ $movement->item->category }}</td>
            <td style="color:#c62828;font-weight:600;">-{{ $movement->quantity }}</td>
            <td>{{ $movement->item->unit }}</td>
            <td>{{ $movement->reference ?? '—' }}</td>
            <td>{{ $movement->remarks ?? '—' }}</td>
            <td>{{ $movement->recordedBy->username ?? 'System' }}</td>
          </tr>
          @empty
          <tr><td colspan="8" style="text-align:center;padding:2rem;color:#888;">No outbound movements yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="pagination-wrapper" style="margin-top:1rem;">
      <p class="pagination-info" id="outboundPaginationInfo"></p>
      <div class="custom-pagination-nav" id="outboundPaginationNav"></div>
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

{{-- PREDICTIVE RESTOCKING TABLE --}}
  <div id="restockTable" style="display:none;">
    <div class="module-header" style="margin-bottom:1rem;">
      <h2 style="font-size:1.1rem;"><i class="fas fa-chart-line" style="color:#1a6b47;"></i> Predictive Restocking</h2>
      <span style="font-size:0.8rem;color:#888;">Based on last 30 days of outbound usage · Lead time: 3 days</span>
    </div>
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Product</th>
            <th>Current Stock</th>
            <th>Avg Daily Usage</th>
            <th>Days Left</th>
            <th>Suggested Order (14 days)</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse($restockData as $r)
          <tr>
            <td style="font-weight:600;">{{ $r['product_name'] }}</td>
            <td>{{ number_format($r['quantity'], 2) }} {{ $r['unit'] }}</td>
            <td>{{ $r['avg_daily_usage'] > 0 ? $r['avg_daily_usage'].' '.$r['unit'].'/day' : '—' }}</td>
            <td>
              @if($r['days_left'] === null)
                <span style="color:#aaa;">—</span>
              @elseif($r['days_left'] <= 3)
                <span style="color:#c62828;font-weight:700;">{{ $r['days_left'] }} days</span>
              @elseif($r['days_left'] <= 7)
                <span style="color:#e65100;font-weight:600;">{{ $r['days_left'] }} days</span>
              @else
                <span style="color:#1a6b47;">{{ $r['days_left'] }} days</span>
              @endif
            </td>
            <td>{{ $r['suggested_order'] > 0 ? $r['suggested_order'].' '.$r['unit'] : '—' }}</td>
            <td>
              @if($r['restock_status'] === 'Restock Now')
                <span style="background:#fdecea;color:#c62828;border-radius:20px;padding:3px 12px;font-size:0.78rem;font-weight:700;">🔴 Restock Now</span>
              @elseif($r['restock_status'] === 'Restock Soon')
                <span style="background:#fff8e1;color:#e65100;border-radius:20px;padding:3px 12px;font-size:0.78rem;font-weight:700;">🟡 Restock Soon</span>
              @elseif($r['restock_status'] === 'Safe')
                <span style="background:#e8f5e9;color:#1a6b47;border-radius:20px;padding:3px 12px;font-size:0.78rem;font-weight:700;">🟢 Safe</span>
              @else
                <span style="background:#f5f5f5;color:#aaa;border-radius:20px;padding:3px 12px;font-size:0.78rem;">No Data</span>
              @endif
            </td>
          </tr>
          @empty
          <tr><td colspan="6" style="text-align:center;padding:2rem;color:#888;">No inventory data available.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</section>
@endsection

@push('modals')
{{-- ADD ITEM MODAL --}}
<div id="addItemModal" class="modal">
  <div class="modal-content">
    <h2>Add New Item</h2>
    <form action="{{ route('inventory.store') }}" method="POST" class="form-grid">
      @csrf
     <input type="hidden" name="category" value="Raw Materials">
      <div class="form-group" style="grid-column: span 2;">
        <label>Product Name</label>
        <select name="product_name" id="addProduct" required onchange="autoFillUnit('add')">
          <option value="" disabled selected>Select a product</option>
        </select>
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
      <div class="form-group">
        <label>Reorder Level</label>
        <input type="number" name="reorder_level" step="0.01" min="0" placeholder="Minimum stock before alert" required />
      </div>
      <div class="form-group" style="grid-column: span 2;">
        <label>Cost per Unit (₱)</label>
        <input type="number" name="cost_per_unit" step="0.01" min="0" placeholder="e.g. 250.00" required />
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
     <input type="hidden" name="category" id="editCategory" value="Raw Materials">
      <div class="form-group" style="grid-column: span 2;">
        <label>Product Name</label>
        <select name="product_name" id="editProduct" required onchange="autoFillUnit('edit')">
          <option value="" disabled selected>Select a product</option>
        </select>
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
      <div class="form-group">
        <label>Reorder Level</label>
        <input type="number" name="reorder_level" id="editReorder" step="0.01" min="0" required />
      </div>
      <div class="form-group" style="grid-column: span 2;">
        <label>Cost per Unit (₱)</label>
        <input type="number" name="cost_per_unit" id="editCost" step="0.01" min="0" required />
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

{{-- ADD INBOUND MODAL --}}
<div id="addInboundModal" class="modal">
  <div class="modal-content">
    <h2><i class="fas fa-arrow-circle-down" style="color:#1a6b47;"></i> Record Inbound</h2>
    <form action="{{ route('inventory.movement') }}" method="POST" class="form-grid">
      @csrf
      <input type="hidden" name="type" value="inbound">
      <div class="form-group" style="grid-column:span 2;">
        <label>Product</label>
        <select name="inventory_item_id" required>
          <option value="" disabled selected>Select a product</option>
          @foreach($items as $item)
            <option value="{{ $item->id }}">{{ $item->product_name }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label>Quantity</label>
        <input type="number" name="quantity" step="0.01" min="0.01" required placeholder="e.g. 50" />
      </div>
      <div class="form-group">
        <label>Date</label>
        <input type="date" name="movement_date" required value="{{ date('Y-m-d') }}" min="2026-01-01" max="{{ date('Y-m-d') }}" />
      </div>

     <div class="form-group">
        <label>Remarks</label>
        <input type="text" name="remarks" placeholder="Optional notes" />
      </div>
      <div class="form-group">
        <label>Expiry Date <span style="color:#888;font-size:0.8rem;">(optional)</span></label>
        <input type="date" name="expiry_date" />
      </div>
      <div class="modal-buttons">
        <button type="button" id="closeAddInbound" class="btn-cancel">Cancel</button>
        <button type="submit" class="btn-save"><i class="fas fa-save"></i> Record</button>
      </div>
    </form>
  </div>
</div>

{{-- ADD OUTBOUND MODAL --}}
<div id="addOutboundModal" class="modal">
  <div class="modal-content">
    <h2><i class="fas fa-arrow-circle-up" style="color:#c62828;"></i> Record Outbound</h2>
    <form action="{{ route('inventory.movement') }}" method="POST" class="form-grid">
      @csrf
      <input type="hidden" name="type" value="outbound">
      <div class="form-group" style="grid-column:span 2;">
        <label>Product</label>
        <select name="inventory_item_id" required>
          <option value="" disabled selected>Select a product</option>
          @foreach($items as $item)
          <option value="{{ $item->id }}">{{ $item->product_name }} — {{ number_format($item->quantity, 2) }} {{ $item->unit }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label>Quantity</label>
        <input type="number" name="quantity" step="0.01" min="0.01" required placeholder="e.g. 20" />
      </div>
      <div class="form-group">
        <label>Date</label>
        <input type="date" name="movement_date" required value="{{ date('Y-m-d') }}" min="2026-01-01" max="{{ date('Y-m-d') }}" />
      </div>
      <div class="form-group">
        <label>Reference</label>
        <input type="text" name="reference" placeholder="e.g. Batch B-2026-001" />
      </div>
      <div class="form-group">
        <label>Remarks</label>
        <input type="text" name="remarks" placeholder="Optional notes" />
      </div>
      <div class="modal-buttons">
        <button type="button" id="closeAddOutbound" class="btn-cancel">Cancel</button>
        <button type="submit" class="btn-save"><i class="fas fa-save"></i> Record</button>
      </div>
    </form>
  </div>
</div>
@endpush

@push('scripts')
<script>

const rawMaterials   = @json($rawMaterials);
const unitMap        = @json($unitMap);

setTimeout(() => document.getElementById('successAlert')?.remove(), 4000);
setTimeout(() => document.getElementById('errorAlert')?.remove(), 5000);

const invColumns = [
  { key:'product',  label:'Product Name',       default:true  },
  { key:'category', label:'Category',            default:false  },
 { key:'start',    label:'Starting Inventory',  default:false  },
  { key:'inbound',  label:'Inbound',             default:true  },
  { key:'outbound', label:'Outbound',            default:true  },
  { key:'end',      label:'Ending Inventory',    default:true  },
  { key:'unit',     label:'Unit',                default:true  },
  { key:'cost',     label:'Cost/Unit (₱)',       default:false    },
  { key:'discrepancy', label:'Discrepancy',      default:true  },
  { key:'status',   label:'Status',              default:true  },
  { key:'actions',  label:'Actions',             default:true  },
];

const colState = {};
invColumns.forEach(c => colState[c.key] = c.default);

function renderInvTable() {
  const bar = document.getElementById('colToggleBar');
  bar.innerHTML = '';

  const activeCount = invColumns.filter(c => colState[c.key] && c.key !== 'actions').length;
  const countEl = document.getElementById('colActiveCount');
  if (countEl) countEl.textContent = activeCount;

  invColumns.forEach(col => {
    const btn = document.createElement('button');
    btn.className = colState[col.key] ? 'col-on' : '';
    btn.innerHTML = `<span class="check-icon">${colState[col.key] ? '<i class="fas fa-check" style="font-size:10px;"></i>' : ''}</span> ${col.label}`;
    btn.onclick = () => { colState[col.key] = !colState[col.key]; renderInvTable(); };
    bar.appendChild(btn);
  });

  const header = document.getElementById('invHeaderRow');
  header.innerHTML = '';
  invColumns.filter(c => colState[c.key]).forEach(c => {
    const th = document.createElement('th');
    th.textContent = c.label;
    header.appendChild(th);
  });

  document.querySelectorAll('#invTableBody tr').forEach(row => {
    row.innerHTML = '';
    invColumns.filter(c => colState[c.key]).forEach(c => {
      const td = document.createElement('td');
      if (c.key === 'status') {
        const status = row.dataset.status;
        const cls = status === 'In Stock' ? 'in-stock-muted' : (status === 'Low Stock' ? 'low' : 'inactive');
        td.innerHTML = `<span class="status-tag ${cls}">${status}</span>`;
        // Row highlighting — dark mode aware
        const isDark = document.body.classList.contains('theme-dark');
        if (status === 'Out of Stock') row.style.background = isDark ? '#2a1a1a' : '#fff5f5';
        else if (status === 'Low Stock') row.style.background = isDark ? '#2a1f10' : '#fff8f0';
        else row.style.background = '';
        } else if (c.key === 'discrepancy') {
  const raw = parseFloat(row.dataset.discrepancyraw);
  if (raw > 0) {
    td.innerHTML = `<span style="color:#1a6b47;font-weight:600;">+${row.dataset.discrepancy} (Surplus)</span>`;
  } else if (raw < 0) {
    td.innerHTML = `<span style="color:#c62828;font-weight:600;">${row.dataset.discrepancy} (Shortage)</span>`;
  } else {
    td.innerHTML = `<span style="color:#888;">0.00 (Matched)</span>`;
  }
      } else if (c.key === 'actions') {
        const canManage = row.dataset.canmanage === '1';
        if (canManage) {
          td.className = 'actions-col';
          td.innerHTML = `
            <button class="action-btn edit-btn"
              data-id="${row.dataset.id}"
              data-name="${row.dataset.name}"
              data-category="${row.dataset.category}"
              data-unit="${row.dataset.unitraw}"
              data-reorder="${row.dataset.reorderraw}"
              data-cost="${row.dataset.costraw}">
              <i class="fas fa-pen"></i>
            </button>
            <form action="${row.dataset.archiveurl}" method="POST" class="d-inline">
              <input type="hidden" name="_token" value="{{ csrf_token() }}">
              <button type="button" class="action-btn archive-btn" title="Archive">
                <i class="fas fa-archive"></i>
              </button>
            </form>`;
        }
      } else {
        td.textContent = row.dataset[c.key] ?? '';
      }
      row.appendChild(td);
    });
  });

  document.querySelectorAll('#invTableBody .edit-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      const category = this.dataset.category;
      const name     = this.dataset.name;
      document.getElementById('editItemForm').action = `/inventory/${this.dataset.id}`;
      document.getElementById('editCategory').value  = category;
      document.getElementById('editUnit').value      = this.dataset.unit;
      document.getElementById('editReorder').value   = this.dataset.reorder;
      document.getElementById('editCost').value      = this.dataset.cost;
      filterProducts('edit');
      document.getElementById('editProduct').value   = name;
      document.getElementById('editItemModal').classList.add('active');
      document.body.classList.add('modal-open');
    });
  });

  document.querySelectorAll('#invTableBody .archive-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      pendingArchiveForm = this.closest('form');
      document.getElementById('archiveItemModal').classList.add('active');
      document.body.classList.add('modal-open');
    });
  });
}


renderInvTable();
// Show Add button on initial load if on Current Stock tab
const addBtn = document.getElementById('openAddItem');
if (addBtn) addBtn.style.display = 'inline-flex';

document.getElementById('colToggleBtn').addEventListener('click', function(e) {
  e.stopPropagation();
  document.getElementById('colDropdown').classList.toggle('open');
});
document.addEventListener('click', function(e) {
  const dropdown = document.getElementById('colDropdown');
  const btn = document.getElementById('colToggleBtn');
  if (dropdown && !dropdown.contains(e.target) && !btn.contains(e.target)) {
    dropdown.classList.remove('open');
  }
});

@if(session('tab'))
window.addEventListener('DOMContentLoaded', function() {
  switchTab('{{ session("tab") }}');
});
@endif

function switchTab(tab) {
  document.getElementById('activeTable').style.display   = tab === 'active'   ? 'block' : 'none';
  document.getElementById('inboundTable').style.display  = tab === 'inbound'  ? 'block' : 'none';
  document.getElementById('outboundTable').style.display = tab === 'outbound' ? 'block' : 'none';
  document.getElementById('archivedTable').style.display = tab === 'archived' ? 'block' : 'none';
  document.getElementById('restockTable').style.display  = tab === 'restock'  ? 'block' : 'none';
  document.querySelectorAll('.inv-tab').forEach(btn => {
    btn.style.background = 'transparent';
    btn.style.color      = '#555';
    btn.style.boxShadow  = 'none';
  });
 const activeBtn = document.getElementById('tab' + tab.charAt(0).toUpperCase() + tab.slice(1));
  activeBtn.style.background = '#1a6b47';
  activeBtn.style.color      = '#fff';
  activeBtn.style.boxShadow  = '0 2px 6px rgba(26,107,71,0.25)';

 const addBtn = document.getElementById('openAddItem');
  if (addBtn) addBtn.style.display = tab === 'active' ? 'inline-flex' : 'none';

  const colBtn = document.getElementById('colToggleBtn');
  if (colBtn) colBtn.style.display = tab === 'active' ? 'inline-flex' : 'none';

 const datePicker = document.querySelector('#inventorySection form[action]');
  if (datePicker) datePicker.style.display = tab === 'active' ? 'flex' : 'none';
  const summaryBar = document.getElementById('inventorySummaryBar');
  if (summaryBar) summaryBar.style.display = tab === 'active' ? 'block' : 'none';

  if (tab === 'inbound')  inboundPaginate();
  if (tab === 'outbound') outboundPaginate();
}

function filterProducts(mode) {
  const productSelect = document.getElementById(mode + 'Product');
  productSelect.innerHTML = '<option value="" disabled selected>Select a product</option>';
  rawMaterials.forEach(p => {
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

document.getElementById('openAddItem')?.addEventListener('click', () => {
  filterProducts('add');
  document.getElementById('addItemModal').classList.add('active');
  document.body.classList.add('modal-open');
});
document.getElementById('closeAddItem')?.addEventListener('click', () => {
  document.getElementById('addItemModal').classList.remove('active');
  document.body.classList.remove('modal-open');
});

document.getElementById('closeEditItem')?.addEventListener('click', () => {
  document.getElementById('editItemModal').classList.remove('active');
  document.body.classList.remove('modal-open');
});

let pendingArchiveForm = null;
document.getElementById('confirmArchive')?.addEventListener('click', () => {
  pendingArchiveForm?.submit();
});
document.getElementById('cancelArchive')?.addEventListener('click', () => {
  document.getElementById('archiveItemModal').classList.remove('active');
  document.body.classList.remove('modal-open');
});

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

document.getElementById('openAddInbound')?.addEventListener('click', () => {
  document.getElementById('addInboundModal').classList.add('active');
  document.body.classList.add('modal-open');
});
document.getElementById('closeAddInbound')?.addEventListener('click', () => {
  document.getElementById('addInboundModal').classList.remove('active');
  document.body.classList.remove('modal-open');
});

document.getElementById('openAddOutbound')?.addEventListener('click', () => {
  document.getElementById('addOutboundModal').classList.add('active');
  document.body.classList.add('modal-open');
});
document.getElementById('closeAddOutbound')?.addEventListener('click', () => {
  document.getElementById('addOutboundModal').classList.remove('active');
  document.body.classList.remove('modal-open');
});

const INV_PER_PAGE = 10;
let invCurrentPage = 1;
let invFilteredRows = [];

function invPaginate() {
  const rows = Array.from(document.querySelectorAll('#invTableBody tr'));
  const q = document.getElementById('inventorySearch')?.value.toLowerCase() ?? '';
  invFilteredRows = rows.filter(row => row.dataset.product?.toLowerCase().includes(q));

  const totalPages = Math.ceil(invFilteredRows.length / INV_PER_PAGE);
  if (invCurrentPage > totalPages) invCurrentPage = 1;

  rows.forEach(r => r.style.display = 'none');
  const start = (invCurrentPage - 1) * INV_PER_PAGE;
  invFilteredRows.slice(start, start + INV_PER_PAGE).forEach(r => r.style.display = '');

  const from = invFilteredRows.length ? start + 1 : 0;
  const to   = Math.min(start + INV_PER_PAGE, invFilteredRows.length);
  const info = `Showing ${from}–${to} of ${invFilteredRows.length} items`;
  document.getElementById('invPaginationInfo').textContent = info;

  ['invPaginationNav'].forEach(id => {
    const nav = document.getElementById(id);
    nav.innerHTML = '';

    const prev = document.createElement(invCurrentPage === 1 ? 'span' : 'a');
    prev.className = 'pg-btn' + (invCurrentPage === 1 ? ' pg-disabled' : '');
    prev.innerHTML = '&#8249;';
    if (invCurrentPage > 1) prev.href = '#';
    prev.addEventListener('click', e => { e.preventDefault(); if(invCurrentPage>1){invCurrentPage--;invPaginate();} });
    nav.appendChild(prev);

    for (let i = 1; i <= totalPages; i++) {
      if (i === 1 || i === totalPages || Math.abs(i - invCurrentPage) <= 1) {
        const a = document.createElement(i === invCurrentPage ? 'span' : 'a');
        a.className = 'pg-btn' + (i === invCurrentPage ? ' pg-active' : '');
        a.textContent = i;
        if (i !== invCurrentPage) { a.href = '#'; a.addEventListener('click', e => { e.preventDefault(); invCurrentPage=i; invPaginate(); }); }
        nav.appendChild(a);
      } else if (
        (i === invCurrentPage - 2 && i > 1) ||
        (i === invCurrentPage + 2 && i < totalPages)
      ) {
        const dots = document.createElement('span');
        dots.className = 'pg-btn pg-dots';
        dots.textContent = '···';
        nav.appendChild(dots);
      }
    }

    const next = document.createElement(invCurrentPage === totalPages || totalPages === 0 ? 'span' : 'a');
    next.className = 'pg-btn' + (invCurrentPage === totalPages || totalPages === 0 ? ' pg-disabled' : '');
    next.innerHTML = '&#8250;';
    if (invCurrentPage < totalPages) next.href = '#';
    next.addEventListener('click', e => { e.preventDefault(); if(invCurrentPage<totalPages){invCurrentPage++;invPaginate();} });
    nav.appendChild(next);
  });
}

document.getElementById('inventorySearch')?.addEventListener('keydown', function (e) {
  if (e.key === 'Enter') {
    invCurrentPage = 1;
    invPaginate();
  }
});

invPaginate();

// ── INBOUND PAGINATION ──
const INBOUND_PER_PAGE = 10;
let inboundPage = 1;

function inboundPaginate() {
  const rows = Array.from(document.querySelectorAll('#inboundMovementsTable tbody tr'));
  const dataRows = rows.filter(r => r.cells.length > 1);
  const totalPages = Math.ceil(dataRows.length / INBOUND_PER_PAGE);
  if (inboundPage > totalPages) inboundPage = 1;

  dataRows.forEach(r => r.style.display = 'none');
  const start = (inboundPage - 1) * INBOUND_PER_PAGE;
  dataRows.slice(start, start + INBOUND_PER_PAGE).forEach(r => r.style.display = '');

  const from = dataRows.length ? start + 1 : 0;
  const to   = Math.min(start + INBOUND_PER_PAGE, dataRows.length);
  document.getElementById('inboundPaginationInfo').textContent = `Showing ${from}–${to} of ${dataRows.length} records`;

  renderPagNav('inboundPaginationNav', inboundPage, totalPages, (p) => { inboundPage = p; inboundPaginate(); });
}

// ── OUTBOUND PAGINATION ──
const OUTBOUND_PER_PAGE = 10;
let outboundPage = 1;

function outboundPaginate() {
  const rows = Array.from(document.querySelectorAll('#outboundMovementsTable tbody tr'));
  const dataRows = rows.filter(r => r.cells.length > 1);
  const totalPages = Math.ceil(dataRows.length / OUTBOUND_PER_PAGE);
  if (outboundPage > totalPages) outboundPage = 1;

  dataRows.forEach(r => r.style.display = 'none');
  const start = (outboundPage - 1) * OUTBOUND_PER_PAGE;
  dataRows.slice(start, start + OUTBOUND_PER_PAGE).forEach(r => r.style.display = '');

  const from = dataRows.length ? start + 1 : 0;
  const to   = Math.min(start + OUTBOUND_PER_PAGE, dataRows.length);
  document.getElementById('outboundPaginationInfo').textContent = `Showing ${from}–${to} of ${dataRows.length} records`;

  renderPagNav('outboundPaginationNav', outboundPage, totalPages, (p) => { outboundPage = p; outboundPaginate(); });
}

// ── SHARED PAGINATION NAV RENDERER ──
function renderPagNav(navId, current, totalPages, onPageClick) {
  const nav = document.getElementById(navId);
  nav.innerHTML = '';

  const prev = document.createElement(current === 1 ? 'span' : 'a');
  prev.className = 'pg-btn' + (current === 1 ? ' pg-disabled' : '');
  prev.innerHTML = '&#8249;';
  if (current > 1) { prev.href = '#'; prev.addEventListener('click', e => { e.preventDefault(); onPageClick(current - 1); }); }
  nav.appendChild(prev);

  for (let i = 1; i <= totalPages; i++) {
    if (i === 1 || i === totalPages || Math.abs(i - current) <= 1) {
      const a = document.createElement(i === current ? 'span' : 'a');
      a.className = 'pg-btn' + (i === current ? ' pg-active' : '');
      a.textContent = i;
      if (i !== current) { a.href = '#'; a.addEventListener('click', e => { e.preventDefault(); onPageClick(i); }); }
      nav.appendChild(a);
    } else if ((i === current - 2 && i > 1) || (i === current + 2 && i < totalPages)) {
      const dots = document.createElement('span');
      dots.className = 'pg-btn pg-dots';
      dots.textContent = '···';
      nav.appendChild(dots);
    }
  }

  const next = document.createElement(current === totalPages || totalPages === 0 ? 'span' : 'a');
  next.className = 'pg-btn' + (current === totalPages || totalPages === 0 ? ' pg-disabled' : '');
  next.innerHTML = '&#8250;';
  if (current < totalPages) { next.href = '#'; next.addEventListener('click', e => { e.preventDefault(); onPageClick(current + 1); }); }
  nav.appendChild(next);
}

inboundPaginate();
outboundPaginate();
</script>
@endpush





