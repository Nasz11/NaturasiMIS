@extends('layouts.app')
@section('title', 'Reports')
@section('page-title', 'Reports Management')
@section('page-subtitle', 'Generate, review, and export system performance and production summaries.')

@section('content')
<section id="reports" class="report-section">
  <div class="module-header">
    <h2><i class="fas fa-chart-pie"></i> Generate Reports</h2>
    <div class="actions">
      <button class="btn-refresh view-reports-btn" id="refreshReports">
        <i class="fas fa-rotate"></i> Refresh
      </button>
    </div>
  </div>

  <div class="filters" style="margin-bottom: 1.5rem; display: flex; flex-wrap: wrap; gap: 1rem;">
    <div class="form-group">
      <label for="reportType">Report Type</label>
      <select id="reportType" style="padding: 0.6rem; border-radius: 8px; border: 1px solid #ccc;">
        <option value="inventory">Inventory Report</option>
        <option value="production">Production Report</option>
        <option value="batches">Batch Report</option>
        <option value="activity">User Activity Summary</option>
      </select>
    </div>
    <div class="form-group">
      <label for="startDate">Start Date</label>
      <input type="date" id="startDate" style="padding: 0.6rem; border-radius: 8px; border: 1px solid #ccc;" />
    </div>
    <div class="form-group">
      <label for="endDate">End Date</label>
      <input type="date" id="endDate" style="padding: 0.6rem; border-radius: 8px; border: 1px solid #ccc;" />
    </div>
    <button class="btn-primary view-reports-btn" id="generateReport" style="align-self: end;">
      <i class="fas fa-chart-line"></i> Generate Report
    </button>
  </div>

  <div class="chart-card" style="margin-bottom: 2rem;">
    <h3 style="margin-bottom: 1rem; color: #0e472d;">Report Visualization</h3>
    <canvas id="reportChart" height="120"></canvas>
  </div>

  <div class="table-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
      <h3 style="color: #0e472d;">Report Summary</h3>
      <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <button class="btn-reset view-reports-btn" id="exportPDF">
          <i class="fas fa-file-pdf"></i> Export PDF
        </button>
        <button class="btn-reset view-reports-btn" id="printReport">
          <i class="fas fa-print"></i> Print
        </button>
      </div>
    </div>

    <table id="reportTable">
      <thead>
        <tr>
          <th>Date</th><th>Module</th><th>Description</th><th>Value / Quantity</th><th>Status</th>
        </tr>
      </thead>
      <tbody>
        @forelse($reportData as $row)
        <tr>
          <td>{{ $row['date'] }}</td>
          <td>{{ $row['module'] }}</td>
          <td>{{ $row['description'] }}</td>
          <td>{{ $row['value'] }}</td>
         <td><span class="status-tag active">{{ $row['status'] }}</span></td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center; padding: 2rem; color: #888;">No report data available. Select filters and click Generate Report.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</section>
@endsection

@push('scripts')
<script>
const ctxReport = document.getElementById("reportChart");
if (ctxReport) {
 const reportChart = new Chart(ctxReport, {
    type: "bar",
    data: {
      labels: {!! json_encode($chartLabels) !!},
      datasets: [
        { label: "Production (kg)", data: {!! json_encode($chartProduction) !!}, backgroundColor: "rgba(26, 107, 71, 0.7)" },
        { label: "Inventory (kg)", data: {!! json_encode($chartInventory) !!}, backgroundColor: "rgba(14, 71, 45, 0.5)" }
      ],
    },
    options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: "top" } }, scales: { y: { beginAtZero: true } } }
  });

 document.getElementById("generateReport")?.addEventListener("click", () => {
    window.location.href = `/reports?report_type=${document.getElementById('reportType').value}&start_date=${document.getElementById('startDate').value}&end_date=${document.getElementById('endDate').value}`;
  });
}

document.getElementById("printReport")?.addEventListener("click", () => window.print());
</script>
@endpush
