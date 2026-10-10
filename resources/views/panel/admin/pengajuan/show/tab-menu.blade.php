<ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item">
        <a class="nav-link {{ Route::is('panel.admin.pengajuan.show') ? 'active' : '' }}" id="deskripsi-tab" href="{{ route('panel.admin.pengajuan.show', ['type_slug' => $type->slug, 'id' => $submission->id]) }}">
            <i class="fas fa-sticky-note me-2"></i> Detail
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ Route::is('panel.admin.pengajuan.required_files.show') ? 'active' : '' }}" id="berkas-tab" href="{{ route('panel.admin.pengajuan.required_files.show', ['type_slug' => $type->slug, 'id' => $submission->id]) }}">
            <i class="fas fa-folder-open me-2"></i> Berkas
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ Route::is('panel.admin.pengajuan.presences.index') ? 'active' : '' }}" id="berkas-tab" href="{{ route('panel.admin.pengajuan.presences.index', ['type_slug' => $type->slug, 'submission_id' => $submission->id]) }}">
            <i class="fas fa-users me-2"></i> Daftar Kehadiran
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ Route::is('panel.admin.pengajuan.evaluation.show') ? 'active' : '' }}" id="penilaian-tab" href="{{ route('panel.admin.pengajuan.evaluation.show', ['type_slug' => $type->slug, 'id' => $submission->id]) }}">
            <i class="fas fa-clipboard-check me-2"></i> Penilaian
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ Route::is('panel.admin.pengajuan.make-result.show') ? 'active' : '' }}" id="diskusi-tab" href="{{ route('panel.admin.pengajuan.make-result.show', ['type_slug' => $type->slug, 'id' => $submission->id]) }}">
            <i class="fa-solid fa-check me-2"></i> Memutuskan Hasil
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ Route::is('panel.admin.pengajuan.result.show') ? 'active' : '' }}" id="diskusi-tab" href="{{ route('panel.admin.pengajuan.result.show', ['type_slug' => $type->slug, 'id' => $submission->id]) }}">
            <i class="fa-solid fa-check me-2"></i> Hasil Keputusan
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ Route::is('panel.admin.pengajuan.verifikators.show') ? 'active' : '' }}" id="pemilihan-verifikator-tab" href="{{ route('panel.admin.pengajuan.verifikators.show', ['type_slug' => $type->slug, 'id' => $submission->id]) }}">
            <i class="fas fa-user-check me-2"></i> Pemilihan Verifikator
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ Route::is('panel.admin.pengajuan.discussions.show') ? 'active' : '' }}" id="diskusi-tab" href="{{ route('panel.admin.pengajuan.discussions.show', ['type_slug' => $type->slug, 'id' => $submission->id]) }}">
            <i class="fa-solid fa-comment me-2"></i> Diskusi
        </a>
    </li>
</ul>
