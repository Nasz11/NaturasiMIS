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

    {{-- System Info Display --}}
    <div style="margin:1rem 0; background:#f8faf9; border:1px solid #e0ece6; border-radius:10px; padding:1rem; display:flex; flex-direction:column; gap:8px;">
      <div style="display:flex; align-items:center; gap:10px;">
        <span style="font-size:0.78rem; color:#888; min-width:140px;">Company Name</span>
        <span style="font-size:0.88rem; font-weight:600; color:#1a3a2a;">{{ $settings->company_name ?? 'NaturasiMIS' }}</span>
      </div>
      <div style="display:flex; align-items:center; gap:10px;">
        <span style="font-size:0.78rem; color:#888; min-width:140px;">System Version</span>
        <span style="font-size:0.88rem; font-weight:600; color:#1a3a2a;">1.0.0</span>
      </div>
      <div style="display:flex; align-items:center; gap:10px;">
        <span style="font-size:0.78rem; color:#888; min-width:140px;">Description</span>
        <span style="font-size:0.88rem; color:#555;">{{ $settings->company_description ?? '—' }}</span>
      </div>
    </div>

    <div style="margin-top: 0.5rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
      <button class="btn-primary system-config-btn" id="editSystemInfoBtn">
        <i class="fas fa-pen"></i> Edit System Info
      </button>
    </div>
  </div>

  {{-- BACKUP & MAINTENANCE --}}
  <div class="chart-card system-config-btn" style="margin-bottom: 2rem;">
    <h2><i class="fas fa-database"></i> Backup & Maintenance</h2>
    <p>Backup, restore, or reset the system data (Admin only).</p>

    {{-- Last Backup Status --}}
    <div style="margin:1rem 0; background:#f8faf9; border:1px solid #e0ece6; border-radius:10px; padding:1rem; display:flex; align-items:center; justify-content:space-between;">
      <div style="display:flex; align-items:center; gap:10px;">
        <span style="font-size:0.78rem; color:#888;">Last Backup</span>
        <span style="font-size:0.88rem; font-weight:600; color:#1a3a2a;">
          @if(session('last_backup'))
            {{ session('last_backup') }}
          @else
            <span style="color:#aaa;">No backup recorded yet</span>
          @endif
        </span>
      </div>
      @if(session('last_backup'))
        <span style="background:#e8f5e9; color:#1a6b47; font-size:0.75rem; font-weight:600; padding:3px 10px; border-radius:99px;">✓ Up to date</span>
      @else
        <span style="background:#fff3e0; color:#e65100; font-size:0.75rem; font-weight:600; padding:3px 10px; border-radius:99px;">⚠ No backup</span>
      @endif
    </div>

    <div style="margin-top: 0.5rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
      <a href="{{ route('settings.backup') }}" class="btn-primary system-config-btn">
        <i class="fas fa-download"></i> Backup Data
      </a>
      <button class="btn-reset system-config-btn" id="restoreBtn">
        <i class="fas fa-undo"></i> Restore Backup
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

{{-- RESTORE MODAL --}}
<div id="restoreModal" class="modal">
  <div class="modal-content small-modal">
    <h2><i class="fas fa-undo"></i> Restore Backup</h2>
    <div style="background:#fff3e0; border:1px solid #ffcc80; border-radius:8px; padding:10px 14px; margin-bottom:1rem; display:flex; align-items:center; gap:8px;">
      <i class="fas fa-exclamation-triangle" style="color:#e65100;"></i>
      <span style="font-size:0.85rem; color:#bf360c; font-weight:500;">Restoring a backup will overwrite all current data. This cannot be undone.</span>
    </div>
    <p>Upload a <strong>.sql</strong> backup file to restore the database.</p>
    <form action="{{ route('settings.restore') }}" method="POST" enctype="multipart/form-data" class="form-grid">
      @csrf
      <div class="form-group" style="grid-column:span 2;">
        <label>Select Backup File (.sql)</label>
        <input type="file" name="backup_file" accept=".sql,.txt" required />
      </div>
      <div class="modal-buttons">
        <button type="button" id="closeRestore" class="btn-cancel">Cancel</button>
        <button type="submit" class="btn-save"><i class="fas fa-undo"></i> Restore</button>
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
document.getElementById('restoreBtn')?.addEventListener('click', () => openModal(document.getElementById('restoreModal')));
document.getElementById('closeRestore')?.addEventListener('click', () => closeModal(document.getElementById('restoreModal')));
</script>
@endpush