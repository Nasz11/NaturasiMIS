{{-- reports/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Reports')
@section('page-title', 'Reports Management')
@section('page-subtitle', 'Generate, review, and export system performance and production summaries.')

@section('content')
<section id="reports" class="report-section">
  <div class="module-header">
    <h2><i class="fas fa-chart-pie"></i> Generate Reports</h2>
    <div class="actions">
      {{-- Refresh keeps current filters --}}
      <a href="{{ route('reports.index', ['report_type' => $type, 'start_date' => $startDate, 'end_date' => $endDate]) }}"
         class="btn-refresh view-reports-btn">
        <i class="fas fa-rotate"></i> Refresh
      </a>
    </div>
  </div>

  {{-- FILTERS --}}
  <form action="{{ route('reports.index') }}" method="GET"
    style="margin-bottom:1.5rem;display:flex;flex-wrap:wrap;gap:1rem;align-items:flex-end;">
    <div class="form-group">
      <label>Report Type</label>
    <select name="report_type" style="padding:.6rem;border-radius:8px;border:1px solid #ccc;">
        @if(in_array(auth()->user()->role, ['admin', 'inventory', 'manager']))
        <option value="inventory"  {{ $type==='inventory'  ? 'selected':'' }}>Inventory Report</option>
        @endif
        @if(in_array(auth()->user()->role, ['admin', 'production', 'manager']))
        <option value="production" {{ $type==='production' ? 'selected':'' }}>Production Report</option>
        @endif
        @if(in_array(auth()->user()->role, ['admin', 'inventory', 'manager']))
        <option value="orders"     {{ $type==='orders'     ? 'selected':'' }}>Orders Report</option>
        @endif
        @if(auth()->user()->role === 'admin')
        <option value="activity"   {{ $type==='activity'   ? 'selected':'' }}>User Activity Summary</option>
        @endif
      </select>
    </div>
    <div class="form-group">
      <label>Start Date</label>
      <input type="date" name="start_date" value="{{ $startDate }}"
        style="padding:.6rem;border-radius:8px;border:1px solid #ccc;" />
    </div>
    <div class="form-group">
      <label>End Date</label>
      <input type="date" name="end_date" value="{{ $endDate }}"
        style="padding:.6rem;border-radius:8px;border:1px solid #ccc;" />
    </div>
    <button type="submit" class="btn-primary view-reports-btn">
      <i class="fas fa-chart-line"></i> Generate Report
    </button>
  </form>

 {{-- INSIGHTS CARDS --}}
<div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem;">
  @if($bestSelling)
  <div style="background:#f0f7f4;border-left:4px solid #1a6b47;border-radius:8px;padding:1rem 1.5rem;flex:1;min-width:180px;">
    <div style="font-size:.75rem;color:#666;font-weight:600;text-transform:uppercase;">🏆 Best Selling</div>
    <div style="font-size:1.2rem;font-weight:700;color:#1a6b47;">{{ $bestSelling->cheese_product }}</div>
    <div style="font-size:.8rem;color:#888;">{{ number_format($bestSelling->total, 2) }} kg total ordered</div>
  </div>
  @endif
  @if($slowMoving)
 <div style="background:#fff8f0;border-left:4px solid #f57c00;border-radius:8px;padding:1rem 1.5rem;flex:1;min-width:180px;">
    <div style="font-size:.75rem;color:#666;font-weight:600;text-transform:uppercase;">🐢 Slow Moving</div>
    <div style="font-size:1.2rem;font-weight:700;color:#f57c00;">{{ $slowMoving->cheese_product }}</div>
    <div style="font-size:.8rem;color:#888;">{{ number_format($slowMoving->total, 2) }} kg total ordered</div>
  </div>
  @endif
</div>

{{-- DYNAMIC CHART (hidden for activity report) --}}
@if($type !== 'activity')
<div class="chart-card" style="margin-bottom:2rem;">
  <h3 style="margin-bottom:0.3rem;color:#0e472d;">
    @if($type === 'inventory') Stock Levels per Product
    @elseif($type === 'production') Production: This Year vs Last Year
    @elseif($type === 'orders') Orders: This Year vs Last Year
    @endif
  </h3>
  <small style="color:#888;font-size:0.78rem;">
    @if($type === 'production' || $type === 'orders') Last 6 months comparison @endif
  </small>
  <canvas id="reportChart" height="120" style="margin-top:1rem;"></canvas>
</div>
@endif

  {{-- TABLE --}}
  <div class="table-container" id="printArea">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:1rem;">
      <h3 style="color:#0e472d;">
        Report Summary
        <small style="font-size:.8rem;font-weight:normal;color:#666;">
          — {{ ucfirst($type) }} Report
          @if($startDate || $endDate)
            ({{ $startDate ?? '...' }} to {{ $endDate ?? '...' }})
          @endif
        </small>
      </h3>
      <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
        {{-- Print Button --}}
        <button class="btn-reset view-reports-btn" onclick="printReport()">
          <i class="fas fa-print"></i> Print
        </button>
        {{-- Export to PDF Button --}}
       <button class="btn-reset view-reports-btn" onclick="exportCSV()">
          <i class="fas fa-file-csv"></i> Export CSV
        </button>
        <button class="btn-primary view-reports-btn" onclick="exportPDF()">
          <i class="fas fa-file-pdf"></i> Export PDF
        </button>
      </div>
    </div>
    {{-- SUMMARY TOTALS --}}
    @if(count($reportData) > 0)
    <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem;">
      <div style="background:#f0f7f4;border-left:4px solid #1a6b47;border-radius:8px;padding:1rem 1.5rem;flex:1;min-width:150px;">
        <div style="font-size:.8rem;color:#666;font-weight:600;text-transform:uppercase;">Total Records</div>
        <div style="font-size:1.8rem;font-weight:700;color:#1a6b47;">{{ count($reportData) }}</div>
      </div>
 @if($type === 'inventory' || $type === 'production' || $type === 'orders')
<div style="background:#f0f7f4;border-left:4px solid #1a6b47;border-radius:8px;padding:1rem 1.5rem;flex:1;min-width:150px;">
  <div style="font-size:.8rem;color:#666;font-weight:600;text-transform:uppercase;">Total Quantity</div>
  <div style="font-size:1.8rem;font-weight:700;color:#1a6b47;">
    {{ number_format($reportData->sum('quantity'), 2) }} kg
  </div>
</div>
@endif
      <div style="background:#f0f7f4;border-left:4px solid #1a6b47;border-radius:8px;padding:1rem 1.5rem;flex:1;min-width:150px;">
        <div style="font-size:.8rem;color:#666;font-weight:600;text-transform:uppercase;">Report Type</div>
        <div style="font-size:1.8rem;font-weight:700;color:#1a6b47;">{{ ucfirst($type) }}</div>
      </div>
    </div>
    @endif

  <table id="reportTable" style="width:100%;border-collapse:collapse;">
  <thead>
    <tr>
      @if($type === 'inventory')
        <th style="padding:.75rem;border:1px solid #ddd;background:#f5f5f5;color:#0e472d;">Product Name</th>
        <th style="padding:.75rem;border:1px solid #ddd;background:#f5f5f5;color:#0e472d;">Category</th>
        <th style="padding:.75rem;border:1px solid #ddd;background:#f5f5f5;color:#0e472d;">Quantity</th>
        <th style="padding:.75rem;border:1px solid #ddd;background:#f5f5f5;color:#0e472d;">Unit</th>
        <th style="padding:.75rem;border:1px solid #ddd;background:#f5f5f5;color:#0e472d;">Reorder Level</th>
        <th style="padding:.75rem;border:1px solid #ddd;background:#f5f5f5;color:#0e472d;">Status</th>
      @elseif($type === 'production')
        <th style="padding:.75rem;border:1px solid #ddd;background:#f5f5f5;color:#0e472d;">Batch Name</th>
        <th style="padding:.75rem;border:1px solid #ddd;background:#f5f5f5;color:#0e472d;">Product</th>
        <th style="padding:.75rem;border:1px solid #ddd;background:#f5f5f5;color:#0e472d;">Quantity</th>
        <th style="padding:.75rem;border:1px solid #ddd;background:#f5f5f5;color:#0e472d;">Production Date</th>
        <th style="padding:.75rem;border:1px solid #ddd;background:#f5f5f5;color:#0e472d;">Status</th>
      @elseif($type === 'orders')
        <th style="padding:.75rem;border:1px solid #ddd;background:#f5f5f5;color:#0e472d;">P.O. Number</th>
        <th style="padding:.75rem;border:1px solid #ddd;background:#f5f5f5;color:#0e472d;">Product</th>
        <th style="padding:.75rem;border:1px solid #ddd;background:#f5f5f5;color:#0e472d;">Quantity</th>
        <th style="padding:.75rem;border:1px solid #ddd;background:#f5f5f5;color:#0e472d;">Unit</th>
        <th style="padding:.75rem;border:1px solid #ddd;background:#f5f5f5;color:#0e472d;">Created By</th>
        <th style="padding:.75rem;border:1px solid #ddd;background:#f5f5f5;color:#0e472d;">Date</th>
        <th style="padding:.75rem;border:1px solid #ddd;background:#f5f5f5;color:#0e472d;">Status</th>
      @elseif($type === 'activity')
        <th style="padding:.75rem;border:1px solid #ddd;background:#f5f5f5;color:#0e472d;">Date</th>
        <th style="padding:.75rem;border:1px solid #ddd;background:#f5f5f5;color:#0e472d;">User</th>
        <th style="padding:.75rem;border:1px solid #ddd;background:#f5f5f5;color:#0e472d;">Module</th>
        <th style="padding:.75rem;border:1px solid #ddd;background:#f5f5f5;color:#0e472d;">Action</th>
        <th style="padding:.75rem;border:1px solid #ddd;background:#f5f5f5;color:#0e472d;">Description</th>
      @endif
    </tr>
  </thead>
  <tbody>
    @forelse($reportData as $row)
    <tr>
      @if($type === 'inventory')
        <td style="padding:.75rem;border:1px solid #ddd;">{{ $row->product_name }}</td>
        <td style="padding:.75rem;border:1px solid #ddd;">{{ $row->category }}</td>
        <td style="padding:.75rem;border:1px solid #ddd;">{{ number_format($row->quantity, 2) }}</td>
        <td style="padding:.75rem;border:1px solid #ddd;">{{ $row->unit }}</td>
        <td style="padding:.75rem;border:1px solid #ddd;">{{ $row->reorder_level }}</td>
        <td style="padding:.75rem;border:1px solid #ddd;">
          <span class="status-tag {{ $row->status === 'In Stock' ? 'active' : 'inactive' }}">{{ $row->status }}</span>
        </td>
      @elseif($type === 'production')
        <td style="padding:.75rem;border:1px solid #ddd;">{{ $row->batch_number ?? '—' }}</td>
<td style="padding:.75rem;border:1px solid #ddd;">{{ $row->product_type ?? '—' }}</td>
        <td style="padding:.75rem;border:1px solid #ddd;">{{ number_format($row->quantity, 2) }}</td>
        <td style="padding:.75rem;border:1px solid #ddd;">{{ $row->production_date }}</td>
        <td style="padding:.75rem;border:1px solid #ddd;">
          <span class="status-tag {{ $row->status === 'Completed' ? 'active' : 'inactive' }}">{{ $row->status }}</span>
        </td>
      @elseif($type === 'orders')
        <td style="padding:.75rem;border:1px solid #ddd;">{{ $row->po_number ?? '—' }}</td>
        <td style="padding:.75rem;border:1px solid #ddd;">{{ $row->cheese_product }}</td>
        <td style="padding:.75rem;border:1px solid #ddd;">{{ number_format($row->quantity, 2) }}</td>
        <td style="padding:.75rem;border:1px solid #ddd;">{{ $row->unit }}</td>
        <td style="padding:.75rem;border:1px solid #ddd;">{{ $row->createdBy?->username ?? '—' }}</td>
        <td style="padding:.75rem;border:1px solid #ddd;">{{ $row->created_at->format('Y-m-d') }}</td>
        <td style="padding:.75rem;border:1px solid #ddd;">
          <span class="status-tag {{ $row->status === 'Confirmed' ? 'active' : 'inactive' }}">{{ $row->status }}</span>
        </td>
      @elseif($type === 'activity')
        <td style="padding:.75rem;border:1px solid #ddd;">{{ $row->created_at->format('Y-m-d H:i') }}</td>
        <td style="padding:.75rem;border:1px solid #ddd;">{{ $row->user?->username ?? '—' }}</td>
        <td style="padding:.75rem;border:1px solid #ddd;">{{ $row->module }}</td>
        <td style="padding:.75rem;border:1px solid #ddd;">{{ $row->action }}</td>
        <td style="padding:.75rem;border:1px solid #ddd;">{{ $row->details ?? '—' }}</td>
      @endif
    </tr>
    @empty
    <tr>
      <td colspan="7" style="text-align:center;padding:1.5rem;color:#888;">No data available for this report.</td>
    </tr>
    @endforelse
  </tbody>
</table>
  </div>
</section>

{{-- Print CSS: only show the table, hide nav/sidebar --}}
<style>
  @media print {
    body * { visibility: hidden; }
    #printArea, #printArea * { visibility: visible; }
    #printArea { position: absolute; left: 0; top: 0; width: 100%; }
  }
</style>
@endsection

@push('scripts')
{{-- jsPDF + AutoTable for PDF export --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

<script>
// Dynamic Chart
const ctx = document.getElementById('reportChart');
if (ctx) {
  const type = '{{ $type }}';
  const labels = {!! json_encode($chartLabels) !!};
  const thisYear = {!! json_encode($chartThisYear) !!};
  const lastYear = {!! json_encode($chartLastYear) !!};

  const datasets = type === 'inventory'
    ? [{ label: 'Current Stock', data: thisYear, backgroundColor: 'rgba(26,107,71,0.7)' }]
    : [
        { label: '{{ now()->year }}', data: thisYear, backgroundColor: 'rgba(26,107,71,0.7)' },
        { label: '{{ now()->year - 1 }}', data: lastYear, backgroundColor: 'rgba(180,180,180,0.6)' }
      ];

  new Chart(ctx, {
    type: 'bar',
    data: { labels, datasets },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: { legend: { position: 'top' } },
      scales: { y: { beginAtZero: true } }
    }
  });
}

function printReport() { window.print(); }

function exportPDF() {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF({ orientation: 'landscape' });
  doc.setFontSize(16);
  doc.text('NaturasiMIS — {{ ucfirst($type) }} Report', 14, 15);
  doc.setFontSize(10);
  doc.text('Generated: ' + new Date().toLocaleString(), 14, 22);
  @if($startDate || $endDate)
  doc.text('Date Range: {{ $startDate ?? "..." }} to {{ $endDate ?? "..." }}', 14, 28);
  @endif
  const table = document.getElementById('reportTable');
  const headers = [];
  const rows = [];
  table.querySelectorAll('thead th').forEach(th => headers.push(th.innerText.trim()));
  table.querySelectorAll('tbody tr').forEach(tr => {
    const cells = [];
    tr.querySelectorAll('td').forEach(td => cells.push(td.innerText.trim()));
    if (cells.length > 1) rows.push(cells);
  });
  doc.autoTable({
    head: [headers], body: rows,
    startY: @if($startDate || $endDate) 34 @else 28 @endif,
    styles: { fontSize: 9 },
    headStyles: { fillColor: [14, 71, 45] },
    alternateRowStyles: { fillColor: [240, 248, 244] },
  });
  doc.save('{{ $type }}-report-{{ now()->format("Y-m-d") }}.pdf');
}

function exportCSV() {
  const table = document.getElementById('reportTable');
  let csv = [];
  table.querySelectorAll('thead th').forEach((th, i) => {
    if (i === 0) csv.push([]);
    csv[0].push('"' + th.innerText.trim() + '"');
  });
  table.querySelectorAll('tbody tr').forEach(tr => {
    const cells = [];
    tr.querySelectorAll('td').forEach(td => cells.push('"' + td.innerText.trim() + '"'));
    if (cells.length > 1) csv.push(cells);
  });
  const csvContent = csv.map(r => r.join(',')).join('\n');
  const blob = new Blob([csvContent], { type: 'text/csv' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = '{{ $type }}-report-{{ now()->format("Y-m-d") }}.csv';
  a.click();
}
</script>
@endpush