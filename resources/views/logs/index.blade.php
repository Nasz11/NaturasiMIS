@extends('layouts.app')
@section('title', 'Activity Logs')
@section('page-title', 'Activity Logs')
@section('page-subtitle', 'Monitor all user activities and system changes for accountability.')

@section('content')
<section id="activityLogs" class="audit-log-section">
  <div class="module-header">
    <h2><i class="fas fa-list-alt"></i> Activity Log Records</h2>
    <div class="actions">
      <div class="search-wrapper">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="Search user, module, or action..." class="input-search" id="logSearch" />
      </div>
      <button class="btn-refresh view-audit-btn" title="Refresh Logs" onclick="window.location.reload()">
        <i class="fas fa-sync-alt"></i> Refresh
      </button>
    </div>
  </div>

  <div class="table-container">
    <table id="logTable">
      <thead>
        <tr>
          <th>Date & Time</th><th>User</th><th>Module</th><th>Action</th><th>Details</th>
        </tr>
      </thead>
      <tbody id="logTableBody">
        @foreach($logs as $log)
        <tr>
          <td>{{ $log->created_at->format('Y-m-d h:i A') }}</td>
          <td>{{ $log->username ?? 'System' }}</td>
          <td>{{ $log->module }}</td>
          <td>{{ $log->action }}</td>
          <td>{{ $log->details }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</section>
@endsection

@push('scripts')
<script>
document.getElementById('logSearch')?.addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('#logTable tbody tr').forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
});
</script>
@endpush
