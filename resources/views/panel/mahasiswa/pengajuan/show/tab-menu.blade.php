<ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item">
        <a class="nav-link {{ Route::is('panel.mahasiswa.pengajuan.required_files.show') ? 'active' : '' }}" id="berkas-tab" href="{{ route('panel.mahasiswa.pengajuan.required_files.show', ['type_slug' => $type->slug, 'id' => $submission->id]) }}">
            <i class="fas fa-folder-open me-2"></i> Berkas
        </a>
    </li>

    @if (
        \App\Models\SubmissionRequiredFile::where('submission_id', $submission->id)->where('url', '!=', null)->count() === \App\Models\SubmissionRequiredFile::where('submission_id', $submission->id)->count()
        && $submission->student->signature_image !== null
    )
        <li class="nav-item">
            <a class="nav-link {{ Route::is('panel.mahasiswa.pengajuan.finish.show') ? 'active' : '' }}" id="diskusi-tab" href="{{ route('panel.mahasiswa.pengajuan.finish.show', ['type_slug' => $type->slug, 'id' => $submission->id]) }}">
                <i class="fa-solid fa-check-to-slot me-2"></i> Ajukan Proses
            </a>
        </li>
    @else
        <a class="nav-link disabled">
            <i class="fas fa-lock me-2"></i> Ajukan Proses
        </a>
    @endif

    @if (
        $submission->is_ready_to_be_processed &&
        \App\Models\Verificator::where('submission_id', $submission->id)->where('status', 'approved')->count() == \App\Models\Verificator::where('submission_id', $submission->id)->count()
    )
        <li class="nav-item">
            <a class="nav-link {{ Route::is('panel.mahasiswa.pengajuan.result.show') ? 'active' : '' }}" id="diskusi-tab" href="{{ route('panel.mahasiswa.pengajuan.result.show', ['type_slug' => $type->slug, 'id' => $submission->id]) }}">
                <i class="fa-solid fa-check me-2"></i> Hasil Keputusan
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ Route::is('panel.mahasiswa.pengajuan.revisi.show') ? 'active' : '' }}" id="diskusi-tab" href="{{ route('panel.mahasiswa.pengajuan.revisi.show', ['type_slug' => $type->slug, 'id' => $submission->id]) }}">
                <i class="fa-solid fa-check-double me-2"></i> Revisi
            </a>
        </li>
    @else
        <a class="nav-link disabled">
            <i class="fas fa-lock me-2"></i> Hasil Keputusan
        </a>
        <a class="nav-link disabled">
            <i class="fas fa-lock me-2"></i> Revisi
        </a>
    @endif

    @if ($submission->is_ready_to_be_processed)
        <li class="nav-item">
            <a class="nav-link {{ Route::is('panel.mahasiswa.pengajuan.discussions.show') ? 'active' : '' }}" id="diskusi-tab" href="{{ route('panel.mahasiswa.pengajuan.discussions.show', ['type_slug' => $type->slug, 'id' => $submission->id]) }}">
                <i class="fa-solid fa-comment me-2"></i> Diskusi
            </a>
        </li>
    @else
        <a class="nav-link disabled">
            <i class="fas fa-lock me-2"></i> Diskusi
        </a>
    @endif
</ul>
