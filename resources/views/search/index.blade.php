@extends('layouts.app')
@section('title', 'Search Results')
@section('page-title', 'Search Results')
@section('page-subtitle', 'Showing results for: ' . $q)

@section('content')
<section id="search-results">

  {{-- RESULT COUNTS SUMMARY --}}
  <div style="display:flex;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap;">
    <div style="background:#fff;border:1px solid #e0e0e0;border-radius:10px;padding:.6rem 1.2rem;font-size:.85rem;color:#555;">
      <i class="fas fa-industry" style="color:#1a6b47;margin-right:.4rem;"></i>
      Production Batches: <strong>{{ $production->count() }}</strong>
    </div>
    <div style="background:#fff;border:1px solid #e0e0e0;border-radius:10px;padding:.6rem 1.2rem;font-size:.85rem;color:#555;">
      <i class="fas fa-clipboard-list" style="color:#1a6b47;margin-right:.4rem;"></i>
      Batches: <strong>{{ $batches->count() }}</strong>
    </div>
    <div style="background:#fff;border:1px solid #e0e0e0;border-radius:10px;padding:.6rem 1.2rem;font-size:.85rem;color:#555;">
      <i class="fas fa-boxes" style="color:#1a6b47;margin-right:.4rem;"></i>
      Inventory: <strong>{{ $inventory->count() }}</strong>
    </div>
  </div>

  {{-- NO RESULTS --}}
  @if($production->isEmpty() && $batches->isEmpty() && $inventory->isEmpty())
  <div style="text-align:center;padding:4rem 2rem;background:#fff;border-radius:16px;border:1px solid #e0e0e0;">
    <i class="fas fa-search" style="font-size:3rem;color:#ccc;margin-bottom:1rem;display:block;"></i>
    <h3 style="color:#555;margin-bottom:.5rem;">No results found</h3>
    <p style="color:#888;">Try searching with a different batch number or keyword.</p>
  </div>
  @endif

  {{-- PRODUCTION BATCHES --}}
  @if($production->isNotEmpty())
  <div class="result-section">
    <h3 style="font-size:1rem;color:#0e472d;margin-bottom:.75rem;">
      <i class="fas fa-industry"></i> Production Batches ({{ $production->count() }})
    </h3>
    <div style="display:flex;flex-direction:column;gap:.6rem;">
      @foreach($production as $pb)
      <div class="result-card" style="cursor:pointer;"
        data-modal="searchModal"
        data-title="Production Batch"
        data-icon="fas fa-industry"
        data-fields='[
          {"label":"Batch Number","value":"{{ $pb->batch_number }}"},
          {"label":"Product Type","value":"{{ $pb->product_type }}"},
          {"label":"Quantity","value":"{{ $pb->quantity }} kg"},
          {"label":"Production Date","value":"{{ \Carbon\Carbon::parse($pb->production_date)->format('Y-m-d') }}"},
          {"label":"Status","value":"{{ $pb->status }}"},
          {"label":"Staff","value":"{{ $pb->staff?->username ?? 'N/A' }}"},
          {"label":"Remarks","value":"{{ $pb->remarks ?? 'N/A' }}"}
        ]'
        data-link="{{ route('production.index') }}">
        <div style="display:flex;align-items:center;justify-content:space-between;">
          <div style="display:flex;align-items:center;gap:.75rem;">
            <div style="background:#e8f5e9;border-radius:8px;padding:.5rem .65rem;">
              <i class="fas fa-industry" style="color:#1a6b47;"></i>
            </div>
            <div>
              <p style="margin:0;font-weight:600;color:#1a1a1a;">{{ $pb->batch_number }}</p>
              <p style="margin:0;font-size:.8rem;color:#888;">{{ $pb->product_type }}</p>
            </div>
          </div>
          <div style="display:flex;align-items:center;gap:1rem;">
            <span style="font-weight:600;color:#1a6b47;">{{ $pb->quantity }} kg</span>
            <span style="background:{{ $pb->status === 'Completed' ? '#e8f5e9' : '#fff3e0' }};
              color:{{ $pb->status === 'Completed' ? '#1a6b47' : '#e65100' }};
              border-radius:20px;padding:.25rem .8rem;font-size:.75rem;font-weight:600;">
              {{ $pb->status }}
            </span>
            <i class="fas fa-chevron-right" style="color:#ccc;font-size:.8rem;"></i>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
  @endif

  {{-- BATCHES --}}
  @if($batches->isNotEmpty())
  <div class="result-section" style="margin-top:1.5rem;">
    <h3 style="font-size:1rem;color:#0e472d;margin-bottom:.75rem;">
      <i class="fas fa-clipboard-list"></i> Batches ({{ $batches->count() }})
    </h3>
    <div style="display:flex;flex-direction:column;gap:.6rem;">
      @foreach($batches as $batch)
      <div class="result-card" style="cursor:pointer;"
        data-modal="searchModal"
        data-title="Batch Record"
        data-icon="fas fa-clipboard-list"
        data-fields='[
          {"label":"Batch ID","value":"{{ $batch->batch_id }}"},
          {"label":"Cheese Type","value":"{{ $batch->cheese_type }}"},
          {"label":"Quantity","value":"{{ $batch->quantity }} kg"},
          {"label":"Start Date","value":"{{ \Carbon\Carbon::parse($batch->start_date)->format('Y-m-d') }}"},
          {"label":"Completion Date","value":"{{ \Carbon\Carbon::parse($batch->completion_date)->format('Y-m-d') }}"},
          {"label":"Status","value":"{{ $batch->status }}"},
          {"label":"Staff","value":"{{ $batch->staff?->username ?? 'N/A' }}"},
          {"label":"Remarks","value":"{{ $batch->remarks ?? 'N/A' }}"}
        ]'
        data-link="{{ route('batches.index') }}">
        <div style="display:flex;align-items:center;justify-content:space-between;">
          <div style="display:flex;align-items:center;gap:.75rem;">
            <div style="background:#e8f5e9;border-radius:8px;padding:.5rem .65rem;">
              <i class="fas fa-clipboard-list" style="color:#1a6b47;"></i>
            </div>
            <div>
              <p style="margin:0;font-weight:600;color:#1a1a1a;">{{ $batch->batch_id }}</p>
              <p style="margin:0;font-size:.8rem;color:#888;">{{ $batch->cheese_type }}</p>
            </div>
          </div>
          <div style="display:flex;align-items:center;gap:1rem;">
            <span style="font-weight:600;color:#1a6b47;">{{ $batch->quantity }} kg</span>
            <span style="background:{{ $batch->status === 'Completed' ? '#e8f5e9' : '#fff3e0' }};
              color:{{ $batch->status === 'Completed' ? '#1a6b47' : '#e65100' }};
              border-radius:20px;padding:.25rem .8rem;font-size:.75rem;font-weight:600;">
              {{ $batch->status }}
            </span>
            <i class="fas fa-chevron-right" style="color:#ccc;font-size:.8rem;"></i>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
  @endif

  {{-- INVENTORY --}}
  @if($inventory->isNotEmpty())
  <div class="result-section" style="margin-top:1.5rem;">
    <h3 style="font-size:1rem;color:#0e472d;margin-bottom:.75rem;">
      <i class="fas fa-boxes"></i> Inventory ({{ $inventory->count() }})
    </h3>
    <div style="display:flex;flex-direction:column;gap:.6rem;">
      @foreach($inventory as $item)
      <div class="result-card" style="cursor:pointer;"
        data-modal="searchModal"
        data-title="Inventory Item"
        data-icon="fas fa-boxes"
        data-fields='[
          {"label":"Product Name","value":"{{ $item->product_name }}"},
          {"label":"Category","value":"{{ $item->category }}"},
          {"label":"Quantity","value":"{{ $item->quantity }} {{ $item->unit }}"},
          {"label":"Reorder Level","value":"{{ $item->reorder_level }} {{ $item->unit }}"},
          {"label":"Status","value":"{{ $item->status }}"},
          {"label":"Last Updated By","value":"{{ $item->updatedBy?->username ?? 'N/A' }}"}
        ]'
        data-link="{{ route('inventory.index') }}">
        <div style="display:flex;align-items:center;justify-content:space-between;">
          <div style="display:flex;align-items:center;gap:.75rem;">
            <div style="background:#e8f5e9;border-radius:8px;padding:.5rem .65rem;">
              <i class="fas fa-boxes" style="color:#1a6b47;"></i>
            </div>
            <div>
              <p style="margin:0;font-weight:600;color:#1a1a1a;">{{ $item->product_name }}</p>
              <p style="margin:0;font-size:.8rem;color:#888;">{{ $item->category }}</p>
            </div>
          </div>
          <div style="display:flex;align-items:center;gap:1rem;">
            <span style="font-weight:600;color:#1a6b47;">{{ $item->quantity }} {{ $item->unit }}</span>
            <span style="background:{{ $item->status === 'In Stock' ? '#e8f5e9' : ($item->status === 'Low Stock' ? '#fff3e0' : '#ffebee') }};
              color:{{ $item->status === 'In Stock' ? '#1a6b47' : ($item->status === 'Low Stock' ? '#e65100' : '#c62828') }};
              border-radius:20px;padding:.25rem .8rem;font-size:.75rem;font-weight:600;">
              {{ $item->status }}
            </span>
            <i class="fas fa-chevron-right" style="color:#ccc;font-size:.8rem;"></i>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
  @endif

</section>

{{-- QUICK VIEW MODAL --}}
<div id="searchModal" class="custom-modal">
  <div class="modal-box" style="width:520px;max-width:95vw;border-radius:16px;padding:2rem;box-shadow:0 20px 60px rgba(0,0,0,0.15);">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:2px solid #f0f0f0;">
      <div style="display:flex;align-items:center;gap:.75rem;">
        <div style="background:#e8f5e9;border-radius:10px;padding:.6rem;">
          <i id="modalIcon" class="fas fa-industry" style="color:#1a6b47;font-size:1.2rem;"></i>
        </div>
        <div>
          <h3 id="modalTitle" style="margin:0;font-size:1.15rem;color:#0e472d;font-weight:700;"></h3>
          <p style="margin:0;font-size:.8rem;color:#888;">Quick View</p>
        </div>
      </div>
      <button id="closeSearchModal" style="background:none;border:none;cursor:pointer;color:#999;font-size:1.2rem;">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <div id="modalFields" style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem 1.5rem;margin-bottom:1.5rem;"></div>
    <div style="display:flex;justify-content:flex-end;gap:.75rem;padding-top:1rem;border-top:1px solid #f0f0f0;">
      <button id="closeSearchModalBtn"
        style="padding:.55rem 1.2rem;border-radius:8px;border:1px solid #ccc;background:#fff;color:#555;cursor:pointer;font-size:.9rem;">
        Close
      </button>
      <a id="modalViewFullBtn" href="#"
        style="padding:.55rem 1.4rem;border-radius:8px;background:#1a6b47;color:#fff;text-decoration:none;font-size:.9rem;font-weight:600;display:flex;align-items:center;gap:.4rem;">
        <i class="fas fa-external-link-alt"></i> View Full Page
      </a>
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
.result-card {
  background: #fff;
  border: 1px solid #e0e0e0;
  border-radius: 12px;
  padding: .9rem 1.2rem;
  transition: border-color .2s, box-shadow .2s, transform .15s;
}
.result-card:hover {
  border-color: #1a6b47;
  box-shadow: 0 4px 16px rgba(26,107,71,.1);
  transform: translateY(-1px);
}
</style>
@endpush

@push('scripts')
<script>
document.querySelectorAll('.result-card').forEach(card => {
  card.addEventListener('click', () => {
    const fields = JSON.parse(card.dataset.fields);
    document.getElementById('modalTitle').textContent = card.dataset.title;
    document.getElementById('modalIcon').className = card.dataset.icon;
    document.getElementById('modalIcon').style.cssText = 'color:#1a6b47;font-size:1.2rem;';
    const container = document.getElementById('modalFields');
    container.innerHTML = '';
    fields.forEach(f => {
      container.innerHTML += `
        <div style="background:#fafafa;border-radius:8px;padding:.65rem .9rem;">
          <p style="margin:0;font-size:.72rem;color:#999;text-transform:uppercase;letter-spacing:.05em;">${f.label}</p>
          <p style="margin:.2rem 0 0;font-weight:600;color:#1a1a1a;font-size:.92rem;">${f.value}</p>
        </div>`;
    });
    document.getElementById('modalViewFullBtn').href = card.dataset.link;
    document.getElementById('searchModal').classList.add('active');
  });
});
document.getElementById('closeSearchModal')?.addEventListener('click', () => document.getElementById('searchModal').classList.remove('active'));
document.getElementById('closeSearchModalBtn')?.addEventListener('click', () => document.getElementById('searchModal').classList.remove('active'));
document.getElementById('searchModal')?.addEventListener('click', function(e) {
  if (e.target === this) this.classList.remove('active');
});
</script>
@endpush