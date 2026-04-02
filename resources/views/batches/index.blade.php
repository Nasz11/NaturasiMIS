@extends('layouts.app')
@section('title', 'Batch Tracking')
@section('page-title', 'Batch Tracking')
@section('page-subtitle', 'Monitor all cheese production batches.')

@section('content')
<section id="batches">

  <div class="module-header">
    <h2><i class="fas fa-clipboard-list"></i> Batch Records</h2>
    <div class="actions">
      <select id="statusFilter" onchange="filterBatches()" style="padding:.5rem .75rem;border:1.5px solid #ccc;border-radius:8px;font-size:.93rem;outline:none;background:#fff;">
        <option value="">All Statuses</option>
       <option value="In Production">In Production</option>
        <option value="Curing">Curing</option>
        <option value="Completed">Completed</option>
      </select>
      <div class="search-wrapper">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="Search by batch or cheese type..." class="input-search" id="batchSearch" oninput="filterBatches()" />
      </div>
    </div>
  </div>

  <div class="table-container">
    <table id="batchesTable">
      <thead>
        <tr>
          <th>Batch ID</th>
          <th>Cheese Type</th>
          <th>Quantity</th>
          <th>Start Date</th>
          <th>Completion Date</th>
          <th>Status</th>
          <th>Staff</th>
          <th>Remarks</th>
        </tr>
      </thead>
      <tbody id="batchesTableBody">
        @forelse($batches as $batch)
        <tr data-status="{{ $batch->status }}">
          <td>{{ $batch->batch_id }}</td>
          <td>{{ $batch->cheese_type }}</td>
          <td>{{ $batch->quantity }} kg</td>
          <td>{{ \Carbon\Carbon::parse($batch->start_date)->format('Y-m-d') }}</td>
          <td>{{ \Carbon\Carbon::parse($batch->completion_date)->format('Y-m-d') }}</td>
          <td>
            <span class="status-tag {{ $batch->status === 'Completed' ? 'active' : ($batch->status === 'In Production' ? 'low' : 'inactive') }}">
              {{ strtoupper($batch->status) }}
            </span>
          </td>
          <td>{{ $batch->staff?->username ?? 'N/A' }}</td>
          <td>{{ $batch->remarks ?? 'N/A' }}</td>
        </tr>
        @empty
        <tr id="emptyRow">
          <td colspan="8" style="text-align:center;padding:2rem;color:#888;">No batch records found.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

<script>
function filterBatches() {
  const q      = document.getElementById('batchSearch').value.toLowerCase();
  const status = document.getElementById('statusFilter').value;
  document.querySelectorAll('#batchesTableBody tr').forEach(row => {
    if (row.id === 'emptyRow') return;
    const matchSearch = row.textContent.toLowerCase().includes(q);
    const matchStatus = status === '' || row.dataset.status === status;
    row.style.display = matchSearch && matchStatus ? '' : 'none';
  });
}
</script>

</section>
@endsection