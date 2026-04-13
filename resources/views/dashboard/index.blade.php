  @extends('layouts.app')

  @section('title', 'Dashboard')
  @section('page-title')
    Welcome Back, <span>{{ auth()->user()->username }}</span>
  @endsection
  @section('page-subtitle', 'Monitor your production and inventory at a glance.')

  @section('content')
 <section id="dashboard">

   {{-- CRITICAL STRIP — only shows if ≤3 days or expired --}}
    @php
      $criticalItems = collect();
      foreach($expiredItems as $id => $movements) {
        $criticalItems->push(['name' => $movements->first()->item->product_name, 'days' => -1]);
      }
      foreach($expiringItems as $id => $movements) {
        $nearest = $movements->sortBy('expiry_date')->first();
        $days = (int) now()->diffInDays($nearest->expiry_date, false);
        if($days <= 3) $criticalItems->push(['name' => $nearest->item->product_name, 'days' => $days]);
      }
    @endphp
    @if($criticalItems->count() > 0)
    <div style="background:#fff0f0;border:1px solid #ffcdd2;border-radius:10px;padding:0.6rem 1.1rem;display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;gap:1rem;">
      <div style="display:flex;align-items:center;gap:0.6rem;flex-wrap:wrap;">
        <i class="fas fa-exclamation-circle" style="color:#c62828;"></i>
        <span style="font-size:0.85rem;font-weight:700;color:#c62828;">Critical Expiry Alert:</span>
        @foreach($criticalItems as $ci)
          <span style="font-size:0.82rem;color:#b71c1c;background:#fdecea;padding:2px 10px;border-radius:20px;font-weight:600;">
            {{ $ci['name'] }} — {{ $ci['days'] < 0 ? 'EXPIRED' : ($ci['days'] == 0 ? 'expires TODAY' : "only {$ci['days']}d left") }}
          </span>
        @endforeach
      </div>
      @if(in_array('inventory', auth()->user()->allowedPages()))
  <a href="{{ route('inventory.index') }}" style="font-size:0.78rem;color:#c62828;font-weight:700;white-space:nowrap;text-decoration:none;">View Inventory →</a>
@else
  <span style="font-size:0.78rem;color:#aaa;font-weight:600;white-space:nowrap;">View Inventory →</span>
@endif
    </div>
   @endif

   {{-- STATS CARDS --}}
    @php
      $expiringCount = $expiringItems->count() + $expiredItems->count();
    @endphp
   <div class="stats">
    <div class="card" style="cursor:pointer;">
        <i class="fas fa-industry" style="font-size:1.8rem;color:#1a6b47;margin-bottom:0.8rem;"></i>
        <h4>Active Batches</h4>
        <h2>{{ $totalStock }}</h2>
        <p style="font-size:0.75rem;color:#888;margin:0;">Currently in production</p>
      </div>
    <div class="card" id="todayOutputCard" style="cursor:pointer;">
        <i class="fas fa-weight" style="font-size:1.8rem;color:#1a6b47;margin-bottom:0.8rem;"></i>
        <h4>Today's Output</h4>
        <h2>{{ number_format($todayOutput) }} kg</h2>
        <p style="font-size:0.75rem;color:#888;margin:0;">Click to view details</p>
      </div>
     <div class="card alert" id="lowStockCard" style="cursor:pointer;">
        <i class="fas fa-exclamation-circle" style="font-size:1.8rem;color:#c62828;margin-bottom:0.8rem;"></i>
        <h4>Low Stock Alerts</h4>
        <h2>{{ $lowStockCount }}</h2>
      </div>
      <div class="card {{ $expiringCount > 0 ? 'alert' : '' }}" id="expiryCard" style="cursor:pointer;{{ $expiringCount == 0 ? 'background:linear-gradient(135deg,rgba(232,245,233,0.95),rgba(200,235,210,0.9));' : '' }}">
        <i class="fas fa-calendar-times" style="font-size:1.8rem;color:{{ $expiringCount > 0 ? '#c62828' : '#1a6b47' }};margin-bottom:0.8rem;"></i>
        <h4>Expiring Soon</h4>
        <h2 style="color:{{ $expiringCount > 0 ? '#c62828' : '#1a6b47' }};">{{ $expiringCount }}</h2>
        <p style="font-size:0.75rem;color:#888;margin:0;">{{ $expiringCount > 0 ? 'Within 7 days' : 'No items expiring' }}</p>
      </div>
    </div>

    {{-- TODAY'S SUMMARY --}}
<div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:1.5rem;">

  {{-- Pending Orders --}}
  @if(in_array('orders', auth()->user()->allowedPages()))
  <a href="{{ route('orders.index') }}" class="dashboard-summary-card dashboard-summary-link" style="border-left:4px solid #f57c00;">
  @else
  <div class="dashboard-summary-card" style="border-left:4px solid #f57c00;cursor:not-allowed;opacity:0.7;" onclick="showNoAccessToast()">
  @endif
    <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1rem;">
      <div style="background:#fff8e1; border-radius:10px; padding:0.6rem;">
        <i class="fas fa-clock" style="color:#f57c00;"></i>
      </div>
      <div>
        <p style="margin:0; font-size:0.8rem; color:#888;">Awaiting Confirmation</p>
        <p style="margin:0; font-weight:700; font-size:1.3rem; color:#e65100;">{{ $pendingOrdersCount }} Pending Orders</p>
      </div>
    </div>
    <span style="font-size:0.8rem; color:#f57c00; font-weight:600;">View Orders →</span>
  @if(in_array('orders', auth()->user()->allowedPages()))
  </a>
  @else
  </div>
  @endif

  {{-- Today's Orders --}}
  @if(in_array('orders', auth()->user()->allowedPages()))
  <a href="{{ route('orders.index') }}" class="dashboard-summary-card dashboard-summary-link" style="border-left:4px solid #1a6b47;">
  @else
  <div class="dashboard-summary-card" style="border-left:4px solid #1a6b47;cursor:not-allowed;opacity:0.7;" onclick="showNoAccessToast()">
  @endif
    <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1rem;">
      <div style="background:#e8f5e9; border-radius:10px; padding:0.6rem;">
        <i class="fas fa-clipboard-check" style="color:#1a6b47;"></i>
      </div>
      <div>
        <p style="margin:0; font-size:0.8rem; color:#888;">Today's Confirmed Orders</p>
        <p style="margin:0; font-weight:700; font-size:1.3rem; color:#0e472d;">{{ $todayOrders->count() }} Order(s)</p>
      </div>
    </div>
    @forelse($todayOrders as $order)
    <div class="dashboard-order-row">
      <span style="font-size:0.85rem; font-weight:500; color:#333;">{{ $order->client_name }}</span>
      <span class="status-tag {{ $order->status === 'Completed' ? 'completed' : 'active' }}" style="font-size:0.72rem; padding:3px 10px;">{{ $order->status }}</span>
    </div>
    @empty
    <p style="font-size:0.85rem; color:#aaa; margin:0;">No confirmed orders today.</p>
    @endforelse
  @if(in_array('orders', auth()->user()->allowedPages()))
  </a>
  @else
  </div>
  @endif

</div>

    {{-- GRID --}}
    <div class="grid">
      <div class="chart-card">
        <h3>Production Trends</h3>
        <canvas id="productionChart"></canvas>
      </div>

      <div class="notifications">
        <h3>Recent Activity</h3>
        <ul>
          @forelse($recentNotifications as $log)
            <li>
         @php $icon = $log->action === 'Login' ? 'sign-in-alt' : ($log->action === 'Logout' ? 'sign-out-alt' : ($log->action === 'Generated Report' ? 'chart-bar' : ($log->action === 'Restored User' ? 'user-check' : ($log->action === 'Archived User' ? 'user-slash' : ($log->module === 'Production' ? 'clipboard-check' : ($log->module === 'Inventory' ? 'box-open' : 'bell')))))); @endphp
<i class="fas fa-{{ $icon }}"></i>
              {{ $log->action }}
              <span>{{ $log->created_at->diffForHumans() }}</span>
            </li>
          @empty
            <li><i class="fas fa-info-circle"></i> No recent activity.</li>
          @endforelse
        </ul>
      </div>

  
   </div>
</section>

@push('modals')
{{-- EXPIRY MODAL --}}
<div id="expiryModal" class="custom-modal">
  <div class="modal-box" style="width:580px;max-width:95vw;border-radius:16px;padding:2rem;box-shadow:0 20px 60px rgba(0,0,0,0.15);border-top:4px solid #e53935;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:2px solid #f0f0f0;">
      <div style="display:flex;align-items:center;gap:0.75rem;">
        <div style="background:#fff0f0;border-radius:10px;padding:0.6rem;">
          <i class="fas fa-calendar-times" style="color:#e53935;font-size:1.2rem;"></i>
        </div>
        <div>
          <h3 style="margin:0;font-size:1.2rem;color:#0e472d;font-weight:700;">Expiring Ingredients</h3>
          <p style="margin:0;font-size:0.8rem;color:#888;">Items expiring within 7 days</p>
        </div>
      </div>
      <button id="closeExpiry" style="background:none;border:none;cursor:pointer;color:#999;font-size:1.2rem;">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <div style="display:flex;flex-direction:column;gap:0.75rem;max-height:350px;overflow-y:auto;">
      @forelse($expiredItems as $itemId => $movements)
      @php $m = $movements->first(); @endphp
      <div style="display:flex;align-items:center;justify-content:space-between;background:#fff0f0;border:1px solid #ffcdd2;border-radius:10px;padding:0.9rem 1.1rem;">
        <div style="display:flex;align-items:center;gap:0.75rem;">
          <div style="background:#fdecea;border-radius:8px;padding:0.5rem;">
            <i class="fas fa-ban" style="color:#c62828;"></i>
          </div>
          <div>
            <p style="margin:0;font-weight:600;color:#1a1a1a;font-size:0.95rem;">{{ $m->item->product_name }}</p>
            <p style="margin:0;font-size:0.78rem;color:#888;">{{ $m->item->category }}</p>
          </div>
        </div>
        <span style="background:#fdecea;color:#c62828;border-radius:20px;padding:0.3rem 0.8rem;font-size:0.75rem;font-weight:700;">
          ⛔ EXPIRED — {{ $movements->sortBy('expiry_date')->first()->expiry_date->format('M d, Y') }}
        </span>
      </div>
      @empty
      @endforelse

      @forelse($expiringItems as $itemId => $movements)
      @php $nearest = $movements->sortBy('expiry_date')->first(); $daysLeft = (int) now()->diffInDays($nearest->expiry_date, false); @endphp
      <div style="display:flex;align-items:center;justify-content:space-between;background:#fafafa;border:1px solid #f0f0f0;border-radius:10px;padding:0.9rem 1.1rem;">
        <div style="display:flex;align-items:center;gap:0.75rem;">
          <div style="background:#fff8e1;border-radius:8px;padding:0.5rem;">
            <i class="fas fa-exclamation-triangle" style="color:#f57c00;"></i>
          </div>
          <div>
            <p style="margin:0;font-weight:600;color:#1a1a1a;font-size:0.95rem;">{{ $nearest->item->product_name }}</p>
            <p style="margin:0;font-size:0.78rem;color:#888;">{{ $nearest->item->category }}</p>
          </div>
        </div>
        <div style="text-align:right;">
          <p style="margin:0;font-size:0.75rem;color:#888;">Expiry Date</p>
          <p style="margin:0;font-weight:700;color:{{ $daysLeft <= 3 ? '#c62828' : '#e65100' }};">
            {{ $nearest->expiry_date->format('M d, Y') }}
          </p>
          <span style="background:{{ $daysLeft <= 3 ? '#fdecea' : '#fff8e1' }};color:{{ $daysLeft <= 3 ? '#c62828' : '#e65100' }};border-radius:20px;padding:0.2rem 0.7rem;font-size:0.72rem;font-weight:700;">
            {{ $daysLeft == 0 ? 'Today!' : "{$daysLeft} day(s) left" }}
          </span>
        </div>
      </div>
      @empty
      @endforelse

      @if($expiredItems->count() == 0 && $expiringItems->count() == 0)
      <div style="text-align:center;padding:2rem;color:#888;">
        <i class="fas fa-check-circle" style="font-size:2rem;color:#1a6b47;margin-bottom:0.5rem;"></i>
        <p>No expiring ingredients!</p>
      </div>
      @endif
    </div>
  </div>
</div>

<div id="lowStockModal" class="custom-modal">
    <div class="modal-box" style="width:580px;max-width:95vw;border-radius:16px;padding:2rem;box-shadow:0 20px 60px rgba(0,0,0,0.15);border-top:4px solid #e53935;">
      
      {{-- Header --}}
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:2px solid #f0f0f0;">
        <div style="display:flex;align-items:center;gap:0.75rem;">
          <div style="background:#fff0f0;border-radius:10px;padding:0.6rem;">
            <i class="fas fa-exclamation-triangle" style="color:#e53935;font-size:1.2rem;"></i>
          </div>
          <div>
            <h3 style="margin:0;font-size:1.2rem;color:#0e472d;font-weight:700;">Low Stock Alerts</h3>
            <p style="margin:0;font-size:0.8rem;color:#888;">{{ $lowStockCount }} item(s) need restocking</p>
          </div>
        </div>
        <button id="closeLowStock" style="background:none;border:none;cursor:pointer;color:#999;font-size:1.2rem;">
          <i class="fas fa-times"></i>
        </button>
      </div>

      {{-- Items List --}}
      <div style="display:flex;flex-direction:column;gap:0.75rem;max-height:350px;overflow-y:auto;">
        @forelse($lowStockItems as $item)
        <div style="display:flex;align-items:center;justify-content:space-between;background:#fafafa;border:1px solid #f0f0f0;border-radius:10px;padding:0.9rem 1.1rem;">
          <div style="display:flex;align-items:center;gap:0.75rem;">
            <div style="background:#fff3e0;border-radius:8px;padding:0.5rem;">
              <i class="fas fa-box" style="color:#f57c00;"></i>
            </div>
            <div>
              <p style="margin:0;font-weight:600;color:#1a1a1a;font-size:0.95rem;">{{ $item->product_name }}</p>
              <p style="margin:0;font-size:0.78rem;color:#888;">{{ $item->category }}</p>
            </div>
          </div>
          <div style="display:flex;align-items:center;gap:1.5rem;text-align:right;">
            <div>
              <p style="margin:0;font-size:0.75rem;color:#888;">Current</p>
              <p style="margin:0;font-weight:700;color:#e53935;">{{ $item->quantity }} {{ $item->unit }}</p>
            </div>
            <div>
              <p style="margin:0;font-size:0.75rem;color:#888;">Reorder At</p>
              <p style="margin:0;font-weight:600;color:#555;">{{ $item->reorder_level }} {{ $item->unit }}</p>
            </div>
            <span style="background:#fff0f0;color:#e53935;border-radius:20px;padding:0.3rem 0.8rem;font-size:0.75rem;font-weight:600;white-space:nowrap;">
              {{ $item->status }}
            </span>
          </div>
        </div>
        @empty
        <div style="text-align:center;padding:2rem;color:#888;">
          <i class="fas fa-check-circle" style="font-size:2rem;color:#1a6b47;margin-bottom:0.5rem;"></i>
          <p>All items are sufficiently stocked!</p>
        </div>
        @endforelse
      </div>

  </div>
  </div>

  {{-- TODAY'S OUTPUT MODAL --}}
<div id="todayOutputModal" class="custom-modal">
    <div class="modal-box" style="width:580px;max-width:95vw;border-radius:16px;padding:2rem;box-shadow:0 20px 60px rgba(0,0,0,0.15);border-top:4px solid #1a6b47;">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:2px solid #f0f0f0;">
        <div style="display:flex;align-items:center;gap:0.75rem;">
          <div style="background:#e8f5e9;border-radius:10px;padding:0.6rem;">
            <i class="fas fa-industry" style="color:#1a6b47;font-size:1.2rem;"></i>
          </div>
          <div>
            <h3 style="margin:0;font-size:1.2rem;color:#0e472d;font-weight:700;">Today's Output</h3>
            <p style="margin:0;font-size:0.8rem;color:#888;">Production batches for {{ now()->format('F d, Y') }}</p>
          </div>
        </div>
        <button id="closeTodayOutput" style="background:none;border:none;cursor:pointer;color:#999;font-size:1.2rem;">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <div style="display:flex;flex-direction:column;gap:0.75rem;max-height:350px;overflow-y:auto;">
        @forelse($todayBatches as $batch)
        <div style="display:flex;align-items:center;justify-content:space-between;background:#fafafa;border:1px solid #f0f0f0;border-radius:10px;padding:0.9rem 1.1rem;">
          <div style="display:flex;align-items:center;gap:0.75rem;">
            <div style="background:#e8f5e9;border-radius:8px;padding:0.5rem;">
              <i class="fas fa-cheese" style="color:#1a6b47;"></i>
            </div>
            <div>
              <p style="margin:0;font-weight:600;color:#1a1a1a;font-size:0.95rem;">{{ $batch->batch_number }}</p>
              <p style="margin:0;font-size:0.78rem;color:#888;">{{ $batch->product_type }}</p>
            </div>
          </div>
          <div style="display:flex;align-items:center;gap:1.5rem;text-align:right;">
            <div>
              <p style="margin:0;font-size:0.75rem;color:#888;">Quantity</p>
              <p style="margin:0;font-weight:700;color:#1a6b47;">{{ $batch->quantity }} kg</p>
            </div>
            <span style="background:#e8f5e9;color:#1a6b47;border-radius:20px;padding:0.3rem 0.8rem;font-size:0.75rem;font-weight:600;white-space:nowrap;">
              {{ $batch->status }}
            </span>
          </div>
        </div>
        @empty
        <div style="text-align:center;padding:2rem;color:#888;">
          <i class="fas fa-box-open" style="font-size:2rem;color:#ccc;margin-bottom:0.5rem;"></i>
          <p>No production recorded today yet.</p>
        </div>
        @endforelse
      </div>
    </div>
</div>

@endpush
@endsection

@push('scripts')
  <script>
  // Production chart with real data
  const ctx = document.getElementById('productionChart');
 if (ctx) {
    const isDark = document.body.classList.contains('theme-dark');
    const tickColor = isDark ? '#eee' : '#666';
    const gridColor = isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)';

    new Chart(ctx, {
      type: 'line',
      data: {
        labels: {!! json_encode(array_column($productionChartData, 'label')) !!},
        datasets: [{
          label: 'Production (kg)',
          data: {!! json_encode(array_column($productionChartData, 'value')) !!},
          borderColor: '#1a6b47',
          backgroundColor: 'rgba(26,107,71,0.25)',
          fill: true,
          tension: 0.4,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: { labels: { color: tickColor } }
        },
        scales: {
          y: { beginAtZero: true, ticks: { color: tickColor }, grid: { color: gridColor } },
          x: { ticks: { color: tickColor }, grid: { color: gridColor } }
        }
      }
    });
  }
// Expiry modal
  const expiryModal = document.getElementById('expiryModal');
  document.getElementById('expiryCard')?.addEventListener('click', () =>
    expiryModal?.classList.add('active'));
  document.getElementById('closeExpiry')?.addEventListener('click', () =>
    expiryModal?.classList.remove('active'));
  // Low stock modal
  const lowStockModal = document.getElementById('lowStockModal');
  document.getElementById('lowStockCard')?.addEventListener('click', () =>
    lowStockModal?.classList.add('active'));
  document.getElementById('closeLowStock')?.addEventListener('click', () =>
    lowStockModal?.classList.remove('active'));
    const todayOutputModal = document.getElementById('todayOutputModal');
  document.getElementById('todayOutputCard')?.addEventListener('click', () =>
    todayOutputModal?.classList.add('active'));
  document.getElementById('closeTodayOutput')?.addEventListener('click', () =>
    todayOutputModal?.classList.remove('active'));

  function showNoAccessToast() {
    const existing = document.getElementById('noAccessToast');
    if (existing) existing.remove();
    const toast = document.createElement('div');
    toast.id = 'noAccessToast';
    toast.innerHTML = '<i class="fas fa-lock"></i> You don\'t have permission to access this page.';
    toast.style.cssText = 'position:fixed;bottom:2rem;left:50%;transform:translateX(-50%);background:#c62828;color:#fff;padding:0.75rem 1.5rem;border-radius:10px;font-size:0.85rem;font-weight:600;box-shadow:0 4px 20px rgba(0,0,0,0.2);z-index:9999;display:flex;align-items:center;gap:0.5rem;';
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
  }
  </script>
  @endpush
