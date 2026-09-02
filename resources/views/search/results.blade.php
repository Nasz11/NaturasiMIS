@extends('layouts.app')

@section('title', 'Search Results')
@section('page-title', 'Search Results')
@section('page-subtitle', "Showing results for \"{{ $q }}\"")

@section('content')
<section id="search-results" style="padding:1rem;">

  {{-- INVENTORY --}}
  <div style="margin-bottom:2rem;">
    <h3 style="color:#0e472d;margin-bottom:1rem;"><i class="fas fa-box"></i> Inventory ({{ $inventory->count() }})</h3>
    @forelse($inventory as $item)
    <div style="background:#fff;border:1px solid #f0f0f0;border-radius:10px;padding:1rem;margin-bottom:0.5rem;display:flex;justify-content:space-between;align-items:center;">
      <div>
        <p style="margin:0;font-weight:600;">{{ $item->product_name }}</p>
        <p style="margin:0;font-size:0.8rem;color:#888;">{{ $item->category }}</p>
      </div>
      <span style="font-weight:700;color:#1a6b47;">{{ $item->quantity }} {{ $item->unit }}</span>
    </div>
    @empty
    <p style="color:#888;">No inventory items found.</p>
    @endforelse
  </div>

  {{-- PRODUCTION BATCHES --}}
  <div style="margin-bottom:2rem;">
    <h3 style="color:#0e472d;margin-bottom:1rem;"><i class="fas fa-industry"></i> Production Batches ({{ $production->count() }})</h3>
    @forelse($production as $batch)
    <div style="background:#fff;border:1px solid #f0f0f0;border-radius:10px;padding:1rem;margin-bottom:0.5rem;display:flex;justify-content:space-between;align-items:center;">
      <div>
        <p style="margin:0;font-weight:600;">{{ $batch->batch_number }}</p>
        <p style="margin:0;font-size:0.8rem;color:#888;">{{ $batch->product_type }}</p>
      </div>
      <span style="font-weight:700;color:#1a6b47;">{{ $batch->quantity }} kg</span>
    </div>
    @empty
    <p style="color:#888;">No production batches found.</p>
    @endforelse
  </div>

  {{-- BATCHES --}}
  <div style="margin-bottom:2rem;">
    <h3 style="color:#0e472d;margin-bottom:1rem;"><i class="fas fa-cheese"></i> Batches ({{ $batches->count() }})</h3>
    @forelse($batches as $batch)
    <div style="background:#fff;border:1px solid #f0f0f0;border-radius:10px;padding:1rem;margin-bottom:0.5rem;display:flex;justify-content:space-between;align-items:center;">
      <div>
        <p style="margin:0;font-weight:600;">{{ $batch->batch_id }}</p>
        <p style="margin:0;font-size:0.8rem;color:#888;">{{ $batch->cheese_type }}</p>
      </div>
      <span style="background:#e8f5e9;color:#1a6b47;border-radius:20px;padding:0.3rem 0.8rem;font-size:0.75rem;font-weight:600;">{{ $batch->status }}</span>
    </div>
    @empty
    <p style="color:#888;">No batches found.</p>
    @endforelse
  </div>

</section>
@endsection