  @extends('layouts.app')

  @section('title', 'Dashboard')
  @section('page-title')
    Welcome Back, <span>{{ auth()->user()->username }}</span>
  @endsection
  @section('page-subtitle', 'Monitor your production and inventory at a glance.')

  @section('content')
  <section id="dashboard">
    {{-- STATS CARDS --}}
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
    </div>

    {{-- TODAY'S SUMMARY --}}
<div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:1.5rem;">

  {{-- Pending Orders --}}
  <div style="background:#fff; border-radius:16px; padding:1.5rem; box-shadow:0 4px 15px rgba(0,0,0,0.08); border-left:4px solid #f57c00;">
    <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1rem;">
      <div style="background:#fff8e1; border-radius:10px; padding:0.6rem;">
        <i class="fas fa-clock" style="color:#f57c00;"></i>
      </div>
      <div>
        <p style="margin:0; font-size:0.8rem; color:#888;">Awaiting Confirmation</p>
        <p style="margin:0; font-weight:700; font-size:1.3rem; color:#e65100;">{{ $pendingOrdersCount }} Pending Orders</p>
      </div>
    </div>
    <a href="{{ route('orders.index') }}" style="font-size:0.8rem; color:#f57c00; font-weight:600; text-decoration:none;">View Orders →</a>
  </div>

 {{-- Today's Orders --}}
  <a href="{{ route('orders.index') }}" style="text-decoration:none; display:block; background:#fff; border-radius:16px; padding:1.5rem; box-shadow:0 4px 15px rgba(0,0,0,0.08); border-left:4px solid #1a6b47; transition:all 0.3s ease;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.12)'" onmouseout="this.style.transform=''; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.08)'">
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
    <div style="display:flex; justify-content:space-between; align-items:center; padding:6px 0; border-bottom:1px solid #f3f4f6;">
      <span style="font-size:0.85rem; font-weight:500; color:#333;">{{ $order->client_name }}</span>
      <span class="status-tag {{ $order->status === 'Completed' ? 'completed' : 'active' }}" style="font-size:0.72rem; padding:3px 10px;">{{ $order->status }}</span>
    </div>
    @empty
    <p style="font-size:0.85rem; color:#aaa; margin:0;">No confirmed orders today.</p>
  @endforelse
  </a>

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

@endsection

@push('scripts')
  <script>
  // Production chart with real data
  const ctx = document.getElementById('productionChart');
  if (ctx) {
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
      options: { responsive: true, maintainAspectRatio: true, scales: { y: { beginAtZero: true } } }
    });
  }

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
  </script>
  @endpush
