@extends('layouts.app')
@section('title', 'Activity Logs')
@section('page-title', 'Activity Logs')
@section('page-subtitle', 'Monitor all user activities and system changes for accountability.')

@section('content')
<section id="activityLogs" class="audit-log-section">

  <div class="module-header">
    <h2><i class="fas fa-list-alt"></i> Activity Log Records</h2>
    <div class="actions">
     <form method="GET" action="{{ route('logs.index') }}" style="display:flex;">
  <input type="hidden" name="module" value="{{ $module }}">
  <input type="hidden" name="start_date" value="{{ $startDate }}">
  <input type="hidden" name="end_date" value="{{ $endDate }}">
  <div class="search-wrapper">
    <i class="fas fa-search"></i>
    <input type="text" name="search" placeholder="Search user, module, or action..." class="input-search" value="{{ $search }}" />
  </div>
</form>
      <a href="{{ route('logs.index') }}" class="btn-refresh view-audit-btn">
        <i class="fas fa-sync-alt"></i> Refresh
      </a>
    </div>
  </div>

  {{-- FILTERS --}}
  <form method="GET" action="{{ route('logs.index') }}" id="logsFilterForm" style="display:flex;flex-wrap:wrap;gap:1rem;align-items:flex-end;margin-bottom:1.5rem;">
    <input type="hidden" name="search" value="{{ $search }}">
    <div class="form-group">
      <label>Module</label>
      <select name="module" style="padding:.6rem;border-radius:8px;border:1px solid #ccc;min-width:150px;">
        <option value="">All Modules</option>
        @foreach($modules as $mod)
          <option value="{{ $mod }}" {{ $module === $mod ? 'selected' : '' }}>{{ $mod }}</option>
        @endforeach
      </select>
    </div>
    <div class="form-group">
      <label>Start Date</label>
      <input type="date" name="start_date" value="{{ $startDate }}" min="2026-01-01" max="{{ date('Y-m-d') }}" style="padding:.6rem;border-radius:8px;border:1px solid #ccc;" />
    </div>
    <div class="form-group">
      <label>End Date</label>
      <input type="date" name="end_date" value="{{ $endDate }}" min="2026-01-01" max="{{ date('Y-m-d') }}" style="padding:.6rem;border-radius:8px;border:1px solid #ccc;" />
    </div>
    <button type="button" onclick="exportCSV()" class="btn-reset">
      <i class="fas fa-file-csv"></i> Export CSV
    </button>
    <button type="button" onclick="exportPDF()" class="btn-primary">
      <i class="fas fa-file-pdf"></i> Export PDF
    </button>
  </form>

  <div class="table-container">
    <table id="logTable">
      <thead>
        <tr>
          <th>Date & Time</th><th>User</th><th>Module</th><th>Action</th><th>Details</th>
        </tr>
      </thead>
      <tbody id="logTableBody">
        @forelse($logs as $log)
        <tr>
          <td style="font-size:0.85rem;">
            <div>{{ $log->created_at->format('Y-m-d h:i A') }}</div>
            <div style="font-size:0.75rem; color:#aaa;">{{ $log->created_at->diffForHumans() }}</div>
          </td>
          <td>{{ $log->username ?? 'System' }}</td>
          <td>
            @php
              $modColors = [
                'Inventory'  => 'background:#fff3e0;color:#e65100;',
                'Orders'     => 'background:#e3f2fd;color:#1565c0;',
                'Production' => 'background:#f3e5f5;color:#6a1b9a;',
                'Reports'    => 'background:#e8f5e9;color:#1a6b47;',
                'Settings'   => 'background:#fce4ec;color:#880e4f;',
                'Users'      => 'background:#e0f7fa;color:#006064;',
              ];
              $modStyle = $modColors[$log->module] ?? 'background:#f0f0f0;color:#555;';
            @endphp
            <span style="{{ $modStyle }} padding:3px 10px; border-radius:99px; font-size:0.75rem; font-weight:600;">{{ $log->module }}</span>
          </td>
          <td>
            @php
              $action = strtolower($log->action);
              if (str_contains($action, 'delete') || str_contains($action, 'archive') || str_contains($action, 'restore')) {
                $actionStyle = 'background:#fdecea;color:#c62828;';
              } elseif (str_contains($action, 'update') || str_contains($action, 'edit') || str_contains($action, 'changed')) {
                $actionStyle = 'background:#fff8e1;color:#f57c00;';
              } else {
                $actionStyle = 'background:#e8f5e9;color:#1a6b47;';
              }
            @endphp
            <span style="{{ $actionStyle }} padding:3px 10px; border-radius:99px; font-size:0.75rem; font-weight:600;">{{ $log->action }}</span>
          </td>
          <td style="max-width:280px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; cursor:pointer; color:#555; font-size:0.85rem;"
              title="{{ $log->details }}"
              onclick="this.style.whiteSpace = this.style.whiteSpace === 'normal' ? 'nowrap' : 'normal'">
            {{ $log->details }}
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="5" style="text-align:center;padding:3rem;color:#aaa;">
            <i class="fas fa-clipboard-list" style="font-size:2rem;margin-bottom:0.75rem;display:block;"></i>
            No activity logs found for the selected filters.
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- PAGINATION --}}
  <div class="pagination-wrapper">
    <p class="pagination-info">
      Showing {{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }} logs
    </p>
    <div class="custom-pagination-nav">
      @if($logs->onFirstPage())
        <span class="pg-btn pg-disabled">&#8249;</span>
      @else
        <a href="{{ $logs->previousPageUrl() }}" class="pg-btn">&#8249;</a>
      @endif

      @php
        $current = $logs->currentPage();
        $last = $logs->lastPage();
        $pages = [];
        for ($i = 1; $i <= $last; $i++) {
          if ($i == 1 || $i == $last || abs($i - $current) <= 1) {
            $pages[] = $i;
          }
        }
        $pages = array_unique($pages);
        sort($pages);
      @endphp

      @php $prev = null; @endphp
      @foreach($pages as $page)
        @if($prev !== null && $page - $prev > 1)
          <span class="pg-btn pg-dots">···</span>
        @endif
        @if($page == $current)
          <span class="pg-btn pg-active">{{ $page }}</span>
        @else
          <a href="{{ $logs->url($page) }}" class="pg-btn">{{ $page }}</a>
        @endif
        @php $prev = $page; @endphp
      @endforeach

      @if($logs->hasMorePages())
        <a href="{{ $logs->nextPageUrl() }}" class="pg-btn">&#8250;</a>
      @else
        <span class="pg-btn pg-disabled">&#8250;</span>
      @endif
    </div>
  </div>

</section>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<script>

  // Auto-submit logs filter
const logsForm = document.getElementById('logsFilterForm');
document.querySelector('[name="module"]')?.addEventListener('change', () => logsForm.submit());
document.querySelector('[name="start_date"]')?.addEventListener('change', () => logsForm.submit());
document.querySelector('[name="end_date"]')?.addEventListener('change', () => logsForm.submit());

function exportCSV() {
  const table = document.getElementById('logTable');
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
  const blob = new Blob([csv.map(r => r.join(',')).join('\n')], { type: 'text/csv' });
  const a = document.createElement('a'); a.href = URL.createObjectURL(blob);
  a.download = 'activity-logs-{{ now()->format("Y-m-d") }}.csv'; a.click();
}

function exportPDF() {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF({ orientation: 'landscape' });
  doc.setFontSize(16);
  doc.text('NaturasiMIS — Activity Logs', 14, 15);
  doc.setFontSize(10);
  doc.text('Generated: ' + new Date().toLocaleString(), 14, 22);
  const headers = [], rows = [];
  document.querySelectorAll('#logTable thead th').forEach(th => headers.push(th.innerText.trim()));
  document.querySelectorAll('#logTable tbody tr').forEach(tr => {
    const cells = [];
    tr.querySelectorAll('td').forEach(td => cells.push(td.innerText.trim()));
    if (cells.length > 1) rows.push(cells);
  });
  doc.autoTable({ head: [headers], body: rows, startY: 28,
    styles: { fontSize: 9 },
    headStyles: { fillColor: [14, 71, 45] },
    alternateRowStyles: { fillColor: [240, 248, 244] }
  });
  doc.save('activity-logs-{{ now()->format("Y-m-d") }}.pdf');
}
</script>
@endpush