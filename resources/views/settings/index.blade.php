@extends('layouts.app')
@section('title', 'System Settings')
@section('page-title', 'System Settings')
@section('page-subtitle', 'Configure and manage NaturasiMIS system preferences and user access.')

@section('content')
<section id="settings" class="settings-section">

  {{-- Success/Error Messages --}}
  @if(session('success'))
  <div class="floating-alert show" style="position:relative;margin-bottom:1rem;background:#1a6b47;color:#fff;padding:1rem;border-radius:8px;">
    ✔ {{ session('success') }}
  </div>
  @endif
  @if($errors->any())
  <div class="floating-alert show" style="position:relative;margin-bottom:1rem;background:#c62828;color:#fff;padding:1rem;border-radius:8px;">
    ✖ {{ $errors->first() }}
  </div>
  @endif

  {{-- SYSTEM PREFERENCES --}}
  @if(auth()->user()->role === 'admin')
  <div class="chart-card system-config-btn" style="margin-bottom: 2rem;">
    <h2><i class="fas fa-sliders-h"></i> System Preferences</h2>
    <p>Modify system information, logo, or company name (Admin only).</p>
    <div style="margin-top: 1rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
      <button class="btn-primary system-config-btn" id="editSystemInfoBtn">
        <i class="fas fa-pen"></i> Edit System Info
      </button>
    </div>
  </div>

  {{-- BACKUP & MAINTENANCE --}}
  <div class="chart-card system-config-btn">
    <h2><i class="fas fa-database"></i> Backup & Maintenance</h2>
    <p>Backup, restore, or reset the system data (Admin only).</p>
    <div style="margin-top: 1rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
      <button class="btn-primary system-config-btn" id="backupBtn">
        <i class="fas fa-download"></i> Backup Data
      </button>
      <button class="btn-reset system-config-btn" id="restoreBtn">
        <i class="fas fa-undo"></i> Restore Backup
      </button>
      <button class="btn-delete system-config-btn" id="resetSystemBtn">
        <i class="fas fa-trash"></i> Reset System
      </button>
    </div>
  </div>
  @endif
</section>

{{-- EDIT SYSTEM INFO MODAL --}}
<div id="editSystemInfoModal" class="modal">
  <div class="modal-content">
    <h2>Edit System Information</h2>
    <form action="{{ route('settings.system') }}" method="POST" class="form-grid">
      @csrf
      <div class="form-group">
        <label>Company Name</label>
        <input type="text" name="company_name" value="{{ $settings->company_name }}" required />
      </div>
      <div class="form-group" style="grid-column: span 2;">
        <label>Company Description</label>
        <textarea name="company_description" rows="3" style="width:100%;padding:0.8rem;border-radius:8px;border:1px solid #ccc;">{{ $settings->company_description }}</textarea>
      </div>
      <div class="modal-buttons">
        <button type="button" id="closeEditSystem" class="btn-cancel">Cancel</button>
        <button type="submit" class="btn-save"><i class="fas fa-save"></i> Save</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
const openModal  = (m) => { m?.classList.add('active'); document.body.classList.add('modal-open'); };
const closeModal = (m) => { m?.classList.remove('active'); document.body.classList.remove('modal-open'); };

document.getElementById('editSystemInfoBtn')?.addEventListener('click', () => openModal(document.getElementById('editSystemInfoModal')));
document.getElementById('closeEditSystem')?.addEventListener('click', () => closeModal(document.getElementById('editSystemInfoModal')));
</script>
@endpush