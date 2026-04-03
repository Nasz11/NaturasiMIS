@extends('layouts.app')
@section('title', 'Batch Tracking')
@section('page-title', 'Batch Tracking')
@section('page-subtitle', 'Monitor all cheese production batches.')

@section('content')
<section id="batches">

  <div class="module-header">
    <h2><i class="fas fa-clipboard-list"></i> Batch Records</h2>
    <div class="actions">
   <form method="GET" action="{{ route('batches.index') }}" style="display:flex;gap:.75rem;">
        <select name="status" onchange="this.form.submit()" style="padding:.5rem .75rem;border:1.5px solid #ccc;border-radius:8px;font-size:.93rem;outline:none;background:#fff;">
          <option value="">All Statuses</option>
          <option value="In Production" {{ ($status ?? '') === 'In Production' ? 'selected' : '' }}>In Production</option>
          <option value="Curing" {{ ($status ?? '') === 'Curing' ? 'selected' : '' }}>Curing</option>
          <option value="Completed" {{ ($status ?? '') === 'Completed' ? 'selected' : '' }}>Completed</option>
        </select>
        <div class="search-wrapper">
          <i class="fas fa-search"></i>
          <input type="text" name="search" placeholder="Search by batch or cheese type..." class="input-search" value="{{ $search ?? '' }}" />
        </div>
      </form>
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
       <tr>
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
</section>
@endsection