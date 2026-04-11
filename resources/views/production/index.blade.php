      @extends('layouts.app')
      @section('title', 'Production')
      @section('page-title', 'Production Management')
      @section('page-subtitle', 'Track and manage cheese production batches efficiently.')

      @section('content')
      <section id="production">

      @if($errors->has('archive'))
        <div id="errorAlert" style="background:#fdecea;color:#c62828;padding:12px 16px;border-radius:8px;margin-bottom:1rem;display:flex;align-items:center;gap:8px;">
          <i class="fas fa-exclamation-circle"></i> {{ $errors->first('archive') }}
        </div>
      @endif

        <div class="module-header">
          <h2><i class="fas fa-industry"></i> Production Batches</h2>
          <div class="actions">
          <form method="GET" action="{{ route('production.index') }}" id="productionFilterForm" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        <input type="hidden" name="tab" id="productionTabInput" value="active">
        <div class="search-wrapper">
          <i class="fas fa-search"></i>
        <input type="text" name="search" placeholder="Search Batch No or Product Type..." class="input-search" value="{{ $search ?? '' }}" onchange="document.getElementById('productionFilterForm').submit()" />
        </div>
        <input type="date" name="date_from" value="{{ request('date_from', now()->subDays(7)->toDateString()) }}" onchange="document.getElementById('productionFilterForm').submit()" min="2026-01-01" max="{{ date('Y-m-d') }}" style="padding:8px 12px;border:1px solid #ddd;border-radius:8px;font-size:0.9rem;color:#333;" />
        <span style="color:#aaa;font-size:0.8rem;">to</span>
        <input type="date" name="date_to" value="{{ request('date_to', now()->toDateString()) }}" onchange="document.getElementById('productionFilterForm').submit()" min="2026-01-01" max="{{ date('Y-m-d') }}" style="padding:8px 12px;border:1px solid #ddd;border-radius:8px;font-size:0.9rem;color:#333;" />
        <select name="status_filter" onchange="document.getElementById('productionFilterForm').submit()" style="padding:8px 12px;border:1px solid #ddd;border-radius:8px;font-size:0.9rem;color:#333;">
          <option value="">All Status</option>
          <option value="In Production" {{ request('status_filter') == 'In Production' ? 'selected' : '' }}>In Production</option>
          <option value="Curing" {{ request('status_filter') == 'Curing' ? 'selected' : '' }}>Curing</option>
          <option value="Completed" {{ request('status_filter') == 'Completed' ? 'selected' : '' }}>Completed</option>
        </select>
        <select name="product_filter" onchange="document.getElementById('productionFilterForm').submit()" style="padding:8px 12px;border:1px solid #ddd;border-radius:8px;font-size:0.9rem;color:#333;">
          <option value="">All Products</option>
          @foreach(\App\Models\ProductionBatch::distinct()->pluck('product_type') as $pt)
          <option value="{{ $pt }}" {{ request('product_filter') == $pt ? 'selected' : '' }}>{{ $pt }}</option>
          @endforeach
        </select>
        <a href="{{ route('production.index') }}" style="padding:8px 16px;background:#eee;color:#333;border-radius:8px;text-decoration:none;font-size:0.9rem;">Clear</a>
      </form>
          </div>
        </div>
        <div class="production-orders-card">
          <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
            <p style="font-size:1rem; font-weight:600; color:#1a6b47; margin:0;"><i class="fas fa-clipboard-list"></i> Today's Confirmed Orders — {{ now()->format('F d, Y') }}</p>
            <span style="background:#e8f5e9; color:#1a6b47; font-size:0.75rem; font-weight:600; padding:4px 12px; border-radius:99px;">{{ $todayOrders->count() }} order(s)</span>
          </div>
          @if($todayOrders->isEmpty())
            <div style="text-align:center; padding:2rem; color:#888;">
              <i class="fas fa-clipboard" style="font-size:2rem; color:#ccc; margin-bottom:0.5rem; display:block;"></i>
              <p style="margin:0;">No confirmed orders for today yet.</p>
            </div>
          @else
          <div style="display:flex; flex-direction:column; gap:0.75rem;">
            @foreach($todayOrders as $order)
          <div class="production-order-item">
              <div style="display:flex; align-items:center; gap:0.75rem;">
                <div style="background:#e8f5e9; border-radius:8px; padding:0.5rem;">
                  <i class="fas fa-cheese" style="color:#1a6b47;"></i>
                </div>
                <div>
                  <p style="margin:0; font-weight:600; color:#1a1a1a;">{{ $order->client_name }} — {{ $order->items->pluck('cheese_product')->unique()->join(', ') }}</p>
                  <p style="margin:0; font-size:0.78rem; color:#888;">{{ number_format($order->items->sum('quantity_pieces'), 0) }} pcs · {{ number_format($order->items->sum('total_kg'), 2) }} kg · {{ $order->po_number }}</p>
                </div>
              </div>
            @if(auth()->user()->role === 'admin' || auth()->user()->role === 'production')
                @if(in_array($order->po_number, $todayBatchPoNumbers))
                  <span style="background:#e8f5e9; color:#1a6b47; font-size:0.78rem; font-weight:600; padding:6px 14px; border-radius:99px;">
                    <i class="fas fa-check"></i> Batch Started
                  </span>
                @else
                  <button class="btn-primary start-batch-btn" style="font-size:0.8rem; padding:6px 16px; white-space:nowrap;"
                    data-po="{{ $order->po_number }}"
                    data-product="{{ $order->items->pluck('cheese_product')->unique()->join(', ') }}"
                    data-qty="{{ number_format($order->items->sum('total_kg'), 2) }}">
                    <i class="fas fa-play"></i> Start Batch
                  </button>
                @endif
              @endif
            </div>
            @endforeach
          </div>
          @endif
        </div>

        {{-- TABS --}}
      <div style="display:flex; gap:1rem; margin-top:1rem;">
          <button class="btn-tab active" id="tabActive" onclick="switchTab('active')">
            <i class="fas fa-industry"></i> Active Batches ({{ $batches->total() }})
          </button>
          <button class="btn-tab" id="tabArchived" onclick="switchTab('archived')">
            <i class="fas fa-archive"></i> Archived Batches ({{ $archivedBatches->count() }})
          </button>
        </div>

        {{-- SUMMARY BAR --}}
        @php
          $inProductionCount = $batches->where('status', 'In Production')->count();
          $curingCount = $batches->where('status', 'Curing')->count();
          $completedCount = $batches->where('status', 'Completed')->count();
          $delayedCount = $batches->filter(fn($b) => $b->status !== 'Completed' && \Carbon\Carbon::parse($b->production_date)->lt(now()->startOfDay()))->count();
        @endphp
      <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:1rem;margin-top:1rem;">
          <span style="background:#f0f0f0;color:#333;padding:5px 14px;border-radius:99px;font-size:0.82rem;font-weight:600;">
            Showing {{ $batches->count() }} of {{ $batches->total() }}
          </span>
          @if($delayedCount > 0)
          <span style="background:#fdecea;color:#c62828;padding:5px 14px;border-radius:99px;font-size:0.82rem;font-weight:700;">
            🔴 {{ $delayedCount }} Delayed
          </span>
          @endif
          @if($inProductionCount > 0)
          <span style="background:#fff3e0;color:#e65100;padding:5px 14px;border-radius:99px;font-size:0.82rem;font-weight:600;">
            🟠 {{ $inProductionCount }} In Production
          </span>
          @endif
          @if($curingCount > 0)
          <span style="background:#e3f2fd;color:#1565c0;padding:5px 14px;border-radius:99px;font-size:0.82rem;font-weight:600;">
            🔵 {{ $curingCount }} Curing
          </span>
          @endif
          <span style="background:#e8f5e9;color:#1a6b47;padding:5px 14px;border-radius:99px;font-size:0.82rem;font-weight:600;">
            🟢 {{ $completedCount }} Completed
          </span>
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
            <td>
              <span class="status-tag {{ $batch->status === 'Completed' ? 'active' : ($batch->status === 'In Production' ? 'low' : 'inactive') }}">{{ strtoupper($batch->status) }}</span>
              @if($batch->status !== 'Completed' && \Carbon\Carbon::parse($batch->production_date)->lt(now()->startOfDay()))
                <span style="background:#fdecea;color:#c62828;padding:2px 8px;border-radius:12px;font-size:0.75rem;font-weight:700;margin-left:4px;">DELAYED</span>
              @endif
            </td>
                  <td>{{ $batch->remarks ?? 'N/A' }}</td>
                  <td>{{ $batch->staff->username ?? 'N/A' }}</td>
                    <td class="actions-col">
                    @if(auth()->user()->role === 'admin' || auth()->user()->role === 'production')
                    <button class="action-btn edit-btn" title="Edit Batch"
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
                      <button type="button" class="action-btn archive-btn" title="Archive" data-status="{{ $batch->status }}">
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
      <div class="pagination-wrapper">
          <p class="pagination-info">Showing {{ $batches->firstItem() ?? 0 }}–{{ $batches->lastItem() ?? 0 }} of {{ $batches->total() }} batches</p>
          <div class="custom-pagination-nav">
            @if($batches->onFirstPage()) <span class="pg-btn pg-disabled">&#8249;</span>
            @else <a href="{{ $batches->previousPageUrl() }}" class="pg-btn">&#8249;</a> @endif
            @php $current=$batches->currentPage(); $last=$batches->lastPage(); $pages=[]; for($i=1;$i<=$last;$i++){if($i==1||$i==$last||abs($i-$current)<=1)$pages[]=$i;} $pages=array_unique($pages); sort($pages); @endphp
            @php $prev=null; @endphp
            @foreach($pages as $page)
              @if($prev!==null && $page-$prev>1) <span class="pg-btn pg-dots">···</span> @endif
              @if($page==$current) <span class="pg-btn pg-active">{{ $page }}</span>
              @else <a href="{{ $batches->url($page) }}" class="pg-btn">{{ $page }}</a> @endif
              @php $prev=$page; @endphp
            @endforeach
            @if($batches->hasMorePages()) <a href="{{ $batches->nextPageUrl() }}" class="pg-btn">&#8250;</a>
            @else <span class="pg-btn pg-disabled">&#8250;</span> @endif
          </div>
        </div>
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
        <div class="pagination-wrapper">
            <p class="pagination-info">Showing {{ $archivedBatches->firstItem() ?? 0 }}–{{ $archivedBatches->lastItem() ?? 0 }} of {{ $archivedBatches->total() }} batches</p>
            <div class="custom-pagination-nav">
              @if($archivedBatches->onFirstPage()) <span class="pg-btn pg-disabled">&#8249;</span>
              @else <a href="{{ $archivedBatches->previousPageUrl() }}" class="pg-btn">&#8249;</a> @endif
              @php $current=$archivedBatches->currentPage(); $last=$archivedBatches->lastPage(); $pages=[]; for($i=1;$i<=$last;$i++){if($i==1||$i==$last||abs($i-$current)<=1)$pages[]=$i;} $pages=array_unique($pages); sort($pages); @endphp
              @php $prev=null; @endphp
              @foreach($pages as $page)
                @if($prev!==null && $page-$prev>1) <span class="pg-btn pg-dots">···</span> @endif
                @if($page==$current) <span class="pg-btn pg-active">{{ $page }}</span>
                @else <a href="{{ $archivedBatches->url($page) }}" class="pg-btn">{{ $page }}</a> @endif
                @php $prev=$page; @endphp
              @endforeach
              @if($archivedBatches->hasMorePages()) <a href="{{ $archivedBatches->nextPageUrl() }}" class="pg-btn">&#8250;</a>
              @else <span class="pg-btn pg-disabled">&#8250;</span> @endif
            </div>
          </div>
        </div>

      <script>  

      setTimeout(() => document.getElementById('errorAlert')?.remove(), 5000);
      // Tab switching
      function switchTab(tab) {
        document.getElementById('activeTable').style.display   = tab === 'active'   ? 'block' : 'none';
        document.getElementById('archivedTable').style.display = tab === 'archived' ? 'block' : 'none';
        document.getElementById('tabActive').classList.toggle('active',   tab === 'active');
        document.getElementById('tabArchived').classList.toggle('active', tab === 'archived');
        document.getElementById('productionTabInput').value = tab;
      }

      // Edit modal
      document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          document.getElementById('editBatchForm').action = `/production/${btn.dataset.id}`;
          document.getElementById('editBatchNumber').value = btn.dataset.batch;
          document.getElementById('editCheeseType').value = btn.dataset.type;
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
            // Must go one step at a time — can't skip steps
            opt.disabled = optIndex < currentIndex || optIndex > currentIndex + 1;
          });

          openModal(document.getElementById('editBatchModal'));
        });
      });
      document.getElementById('closeEditBatch')?.addEventListener('click', () => closeModal(document.getElementById('editBatchModal')));
      document.getElementById('closeAddBatch')?.addEventListener('click', () => closeModal(document.getElementById('addBatchModal')));

    // Archive modal
    let pendingArchiveForm = null;
    document.querySelectorAll('.archive-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        if (btn.dataset.status !== 'Completed') {
          const msg = document.createElement('div');
          msg.className = 'alert-message';
          msg.style.cssText = 'position:fixed;top:1.5rem;right:1.5rem;z-index:9999;display:flex;align-items:center;gap:8px;';
          msg.innerHTML = '<i class="fas fa-exclamation-circle"></i> Only completed batches can be archived.';
          document.body.appendChild(msg);
          setTimeout(() => msg.remove(), 4000);
          return;
        }
        pendingArchiveForm = btn.closest('form');
        document.getElementById('archiveBatchModal')?.classList.add('active');
        document.body.classList.add('modal-open');
      });
    });
    document.getElementById('confirmArchiveBatch')?.addEventListener('click', () => pendingArchiveForm?.submit());
    document.getElementById('cancelArchiveBatch')?.addEventListener('click', () => {
      document.getElementById('archiveBatchModal')?.classList.remove('active');
      document.body.classList.remove('modal-open');
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
      document.getElementById('cancelDeleteBatch')?.addEventListener('click', () => closeModal(document.getElementById('deleteBatchModal')));

      document.querySelectorAll('.start-batch-btn').forEach(btn => {
        btn.addEventListener('click', async () => { 
          document.querySelector('#addBatchModal [name="product_type"]').value = btn.dataset.product;
          document.querySelector('#addBatchModal [name="quantity"]').value = btn.dataset.qty;
          document.querySelector('#addBatchModal [name="production_date"]').value = '{{ now()->format("Y-m-d") }}';
          document.querySelector('#addBatchModal [name="remarks"]').value = btn.dataset.po;
          const res = await fetch('{{ route("production.nextBatchNumber") }}');
          const data = await res.json();
          document.querySelector('#addBatchModal [name="batch_number"]').value = data.batch_number;
          openModal(document.getElementById('addBatchModal'));
        });
      });
      </script>

      </section>

      @endsection

      @push('modals')
      {{-- ADD MODAL --}}
      <div id="addBatchModal" class="modal">
        <div class="modal-content">
          <h2>Add New Production Batch</h2>
          <form action="{{ route('production.store') }}" method="POST" class="form-grid">
            @csrf
            <div class="form-group"><label>Batch Number</label><input type="text" name="batch_number" required readonly style="background:#f3f4f6;cursor:not-allowed;" /></div>
            <div class="form-group"><label>Product / Cheese Type</label><input type="text" name="product_type" required readonly style="background:#f3f4f6;cursor:not-allowed;" /></div>
            <div class="form-group"><label>Quantity Produced (kg)</label><input type="number" name="quantity" step="0.01" min="0.01" required readonly style="background:#f3f4f6;cursor:not-allowed;" /></div>
            <div class="form-group"><label>Production Date</label><input type="date" name="production_date" required readonly style="background:#f3f4f6;cursor:not-allowed;" /></div>
            <div class="form-group"><label>Status</label><select name="status" disabled><option value="In Production">In Production</option></select><input type="hidden" name="status" value="In Production" /><small style="color:#888;font-size:.75rem;">New batches always start at In Production.</small></div>
            <div class="form-group"><label>Remarks</label><input type="text" name="remarks" placeholder="Optional notes" /></div>
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
            <div class="form-group"><label>Batch Number</label><input type="text" name="batch_number" id="editBatchNumber" required readonly style="background:#f3f4f6;cursor:not-allowed;" /></div>
            <div class="form-group"><label>Product / Cheese Type</label><input type="text" name="product_type" id="editCheeseType" required readonly style="background:#f3f4f6;cursor:not-allowed;" /></div>
            <div class="form-group"><label>Quantity (kg)</label><input type="number" name="quantity" id="editQuantity" step="0.01" required readonly style="background:#f3f4f6;cursor:not-allowed;" /></div>
            <div class="form-group"><label>Production Date</label><input type="date" name="production_date" id="editDate" required readonly style="background:#f3f4f6;cursor:not-allowed;" /></div>
            <div class="form-group">
              <label>Status</label>
              <select name="status" id="editStatus">
                <option value="In Production">In Production</option>
                <option value="Curing">Curing</option>
                <option value="Completed">Completed</option>
              </select>
              <small style="color:#888;font-size:.75rem;">Workflow: In Production → Curing → Completed</small>
            </div>
            <div class="form-group"><label>Remarks</label><input type="text" name="remarks" id="editRemarks" /></div>
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
      @endpush
