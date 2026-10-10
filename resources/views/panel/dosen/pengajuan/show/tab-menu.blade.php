<?php
    $fromUrl = '';
    if (isset($_GET['from'])) {
        $fromUrl = '?from='.$_GET['from'];
    }
?>

<ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item">
        <a class="nav-link {{ Route::is('panel.dosen.pengajuan.show') ? 'active' : '' }}" id="deskripsi-tab" href="{{ route('panel.dosen.pengajuan.show', ['type_slug' => $type->slug, 'id' => $submission->id]) }}<?php echo $fromUrl; ?>">
            <i class="fas fa-unlock-alt me-2"></i> Detail
        </a>
    </li>

    @if ($verificator != null)
        <li class="nav-item">
            <a class="nav-link {{ Route::is('panel.dosen.pengajuan.required_files.show') ? 'active' : '' }}" id="berkas-tab" href="{{ route('panel.dosen.pengajuan.required_files.show', ['type_slug' => $type->slug, 'id' => $submission->id]) }}<?php echo $fromUrl; ?>">
                <i class="fas fa-folder-open me-2"></i> Berkas
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ Route::is('panel.dosen.pengajuan.presences.index') ? 'active' : '' }}" id="berkas-tab" href="{{ route('panel.dosen.pengajuan.presences.index', ['type_slug' => $type->slug, 'submission_id' => $submission->id]) }}<?php echo $fromUrl; ?>">
                <i class="fas fa-users me-2"></i> Daftar Kehadiran
            </a>
        </li>

        @if ($verificator->status == 'approved')
            <li class="nav-item">
                <a class="nav-link {{ Route::is('panel.dosen.pengajuan.evaluation.show') ? 'active' : '' }}" id="berkas-tab" href="{{ route('panel.dosen.pengajuan.evaluation.show', ['type_slug' => $type->slug, 'id' => $submission->id]) }}<?php echo $fromUrl; ?>">
                    <i class="fas fa-unlock me-2"></i> Penilaian
                </a>
            </li>
        @else
            <li class="nav-item">
                <a class="nav-link disabled">
                    <i class="fas fa-lock me-2"></i> Penilaian
                </a>
            </li>
        @endif

        <li class="nav-item">
            <a class="nav-link {{ Route::is('panel.dosen.pengajuan.make-result.show') ? 'active' : '' }}" id="diskusi-tab" href="{{ route('panel.dosen.pengajuan.make-result.show', ['type_slug' => $type->slug, 'id' => $submission->id]) }}<?php echo $fromUrl; ?>">
                <i class="fa-solid fa-square-pen me-2"></i> Memutuskan Hasil
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ Route::is('panel.dosen.pengajuan.result.show') ? 'active' : '' }}" id="diskusi-tab" href="{{ route('panel.dosen.pengajuan.result.show', ['type_slug' => $type->slug, 'id' => $submission->id]) }}<?php echo $fromUrl; ?>">
                <i class="fa-solid fa-square-pen me-2"></i> Hasil Keputusan
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ Route::is('panel.dosen.pengajuan.revision.show') ? 'active' : '' }}" id="diskusi-tab" href="{{ route('panel.dosen.pengajuan.revision.show', ['type_slug' => $type->slug, 'id' => $submission->id]) }}<?php echo $fromUrl; ?>">
                <i class="fa-solid fa-check-double me-2"></i> Revisi
            </a>
        </li>
    @endif

    @if ($auth_dosen->position == 'Ketua Jurusan' || $auth_dosen->position == 'Ketua Prodi')
        <li class="nav-item">
            <a class="nav-link {{ Route::is('panel.dosen.pengajuan.verifikators.show') ? 'active' : '' }}" id="pemilihan-verifikator-tab" href="{{ route('panel.dosen.pengajuan.verifikators.show', ['type_slug' => $type->slug, 'id' => $submission->id]) }}<?php echo $fromUrl; ?>">
                <i class="fas fa-unlock-alt me-2"></i> Pemilihan Verifikator
            </a>
        </li>
    @endif

    <li class="nav-item">
        <a class="nav-link {{ Route::is('panel.dosen.pengajuan.discussions.show') ? 'active' : '' }}" id="diskusi-tab" href="{{ route('panel.dosen.pengajuan.discussions.show', ['type_slug' => $type->slug, 'id' => $submission->id]) }}<?php echo $fromUrl; ?>">
            <i class="fa-solid fa-comment me-2"></i> Diskusi
        </a>
    </li>
</ul>
