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
        <option value="inventory"  {{ $type==='inventory'  ? 'selected':'' }}>Inventory Report</option>
        <option value="production" {{ $type==='production' ? 'selected':'' }}>Production Report</option>
        <option value="batches"    {{ $type==='batches'    ? 'selected':'' }}>Batch Report</option>
        <option value="activity"   {{ $type==='activity'   ? 'selected':'' }}>User Activity Summary</option>
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

  {{-- CHART --}}
  <div class="chart-card" style="margin-bottom:2rem;">
    <h3 style="margin-bottom:1rem;color:#0e472d;">Report Visualization</h3>
    <canvas id="reportChart" height="120"></canvas>
  </div>

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
        <button class="btn-primary view-reports-btn" onclick="exportPDF()">
          <i class="fas fa-file-pdf"></i> Export PDF
        </button>
      </div>
    </div>

    <table id="reportTable" style="width:100%;border-collapse:collapse;">
      <thead>
        <tr>
         <th style="padding:.75rem;border:1px solid #ddd;background:#f5f5f5;color:#0e472d;font-weight:600;">Date</th>
<th style="padding:.75rem;border:1px solid #ddd;background:#f5f5f5;color:#0e472d;font-weight:600;">Module</th>
<th style="padding:.75rem;border:1px solid #ddd;background:#f5f5f5;color:#0e472d;font-weight:600;">Description</th>
<th style="padding:.75rem;border:1px solid #ddd;background:#f5f5f5;color:#0e472d;font-weight:600;">Value / Quantity</th>
<th style="padding:.75rem;border:1px solid #ddd;background:#f5f5f5;color:#0e472d;font-weight:600;">Status</th>
        </tr>
      </thead>
      <tbody>
        {{-- FIX: use $reportData not $data --}}
        @forelse($reportData as $row)
        <tr>
          <td style="padding:.75rem;border:1px solid #ddd;">{{ $row['date'] }}</td>
          <td style="padding:.75rem;border:1px solid #ddd;">{{ $row['module'] }}</td>
          <td style="padding:.75rem;border:1px solid #ddd;">{{ $row['description'] }}</td>
          <td style="padding:.75rem;border:1px solid #ddd;">{{ $row['value'] }}</td>
          <td style="padding:.75rem;border:1px solid #ddd;">
            <span class="status-tag {{ in_array($row['status'], ['In Stock','Completed','Logged']) ? 'active' : 'inactive' }}">
              {{ $row['status'] }}
            </span>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="5" style="text-align:center;padding:1.5rem;color:#888;">
            No data available for this report.
          </td>
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
// Chart
const ctx = document.getElementById('reportChart');
if (ctx) {
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: {!! json_encode($chartLabels) !!},
      datasets: [
        {
          label: 'Production (kg)',
          data: {!! json_encode($chartProduction) !!},
          backgroundColor: 'rgba(26,107,71,0.7)'
        },
        {
          label: 'Inventory (kg)',
          data: {!! json_encode($chartInventory) !!},
          backgroundColor: 'rgba(14,71,45,0.5)'
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: { legend: { position: 'top' } },
      scales: { y: { beginAtZero: true } }
    }
  });
}

// Print — only prints the table area, not the sidebar/nav
function printReport() {
  window.print();
}

// Export to PDF using jsPDF + AutoTable
function exportPDF() {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF({ orientation: 'landscape' });

  // Title
  doc.setFontSize(16);
  doc.text('NaturasiMIS — {{ ucfirst($type) }} Report', 14, 15);
  doc.setFontSize(10);
  doc.text('Generated: ' + new Date().toLocaleString(), 14, 22);
  @if($startDate || $endDate)
  doc.text('Date Range: {{ $startDate ?? "..." }} to {{ $endDate ?? "..." }}', 14, 28);
  @endif

  // Table
  const table = document.getElementById('reportTable');
  const headers = [];
  const rows    = [];

  // Get headers
  table.querySelectorAll('thead th').forEach(th => headers.push(th.innerText.trim()));

  // Get rows
  table.querySelectorAll('tbody tr').forEach(tr => {
    const cells = [];
    tr.querySelectorAll('td').forEach(td => cells.push(td.innerText.trim()));
    if (cells.length > 1) rows.push(cells); // skip empty-state row
  });

  doc.autoTable({
    head: [headers],
    body: rows,
    startY: @if($startDate || $endDate) 34 @else 28 @endif,
    styles: { fontSize: 9 },
    headStyles: { fillColor: [14, 71, 45] },
    alternateRowStyles: { fillColor: [240, 248, 244] },
  });

  doc.save('{{ $type }}-report-{{ now()->format("Y-m-d") }}.pdf');
}
</script>
@endpush    