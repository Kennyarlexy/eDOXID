<ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item">
        <a class="nav-link {{ Route::is('panel.bagian-administrasi.pengajuan.required_files.show') ? 'active' : '' }}" id="berkas-tab" href="{{ route('panel.bagian-administrasi.pengajuan.required_files.show', ['type_slug' => $type->slug, 'id' => $submission->id]) }}">
            <i class="fas fa-folder-open me-2"></i> Berkas
        </a>
    </li>
    @if ($submission->format->is_need_trial_schedule)
        <li class="nav-item">
            <a class="nav-link {{ Route::is('panel.bagian-administrasi.pengajuan.schedule.show') ? 'active' : '' }}" id="sidang-tab" href="{{ route('panel.bagian-administrasi.pengajuan.schedule.show', ['type_slug' => $type->slug, 'id' => $submission->id]) }}">
                <i class="fas fa-calendar me-2"></i> Seminar
            </a>
        </li>
    @endif
    <li class="nav-item">
        <a class="nav-link {{ Route::is('panel.bagian-administrasi.pengajuan.evaluation.show') ? 'active' : '' }}" id="penilaian-tab" href="{{ route('panel.bagian-administrasi.pengajuan.evaluation.show', ['type_slug' => $type->slug, 'id' => $submission->id]) }}">
            <i class="fas fa-clipboard-check me-2"></i> Penilaian
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ Route::is('panel.bagian-administrasi.pengajuan.result.show') ? 'active' : '' }}" id="hasil-keputusan-tab" href="{{ route('panel.bagian-administrasi.pengajuan.result.show', ['type_slug' => $type->slug, 'id' => $submission->id]) }}">
            <i class="fa-solid fa-check me-2"></i> Hasil Keputusan
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ Route::is('panel.bagian-administrasi.pengajuan.revisi.show') ? 'active' : '' }}" id="hasil-keputusan-tab" href="{{ route('panel.bagian-administrasi.pengajuan.revisi.show', ['type_slug' => $type->slug, 'id' => $submission->id]) }}">
            <i class="fa-solid fa-check-double me-2"></i> Revisi
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ Route::is('panel.bagian-administrasi.pengajuan.discussions.show') ? 'active' : '' }}" id="diskusi-tab" href="{{ route('panel.bagian-administrasi.pengajuan.discussions.show', ['type_slug' => $type->slug, 'id' => $submission->id]) }}">
            <i class="fa-solid fa-comment me-2"></i> Diskusi
        </a>
    </li>
</ul>
