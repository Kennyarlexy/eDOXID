<li class="sidebar-item {{ Route::is('panel.dosen.pengajuan.*') ? 'active' : '' }} has-sub">
    <a href="#" class="sidebar-link">
        <i class="fas fa-folder"></i>
        <span>Monitoring Pengajuan</span>
    </a>
    <ul class="submenu" style="display: {{ Route::is('panel.dosen.pengajuan.*') || Route::is('panel.dosen.tahapan-akhir.*') ? 'block' : 'none' }}">
        @php
            $types = \App\Models\Type::get();
            $dosen = Auth::user();
        @endphp

        @foreach ($types as $type)
            @php
                $submissions = [];
                if ($dosen->position == 'Ketua Jurusan' || $dosen->position == 'Ketua Prodi') {
                    $submissions = \App\Models\Submission::where('is_ready_to_be_processed', 1)
                        ->where('type_id', $type->id)
                        ->get();
                } else {
                    $submissions = \App\Models\Submission::where('is_ready_to_be_processed', 1)
                        ->where('type_id', $type->id)
                        ->whereHas('verificators', function ($q) use ($dosen) {
                            $q->where('user_id', $dosen->id);
                        })
                        ->get();
                }
                $submission_is_not_readed_count = \App\Models\Submission::where('is_ready_to_be_processed', 1)
                    ->where('type_id', $type->id)
                    ->whereHas('verificators', function ($q) use ($dosen) {
                        $q->where('user_id', $dosen->id);
                        $q->where('is_read_by_dosen', 0);
                    })
                    ->count();
            @endphp

            <li
                class="submenu-item {{ Route::is('panel.dosen.pengajuan*') && request()->route('type_slug') == $type->slug ? 'active' : '' }}">
                <a href="{{ route('panel.dosen.pengajuan.index', [
                    'type_slug' => $type->slug,
                    'status_pengajuan' => 'processed'
                ]) }}">{{ $type->name }} <span
                        class="badge bg-{{ $submission_is_not_readed_count == 0 ? 'success' : 'danger' }}">{{ $submission_is_not_readed_count }}</span></a>
            </li>
        @endforeach

        <li
            class="submenu-item {{ Route::is('panel.dosen.tahapan-akhir.*') ? 'active' : '' }}">
            <a href="{{ route('panel.dosen.tahapan-akhir.index') }}">Tahapan Akhir</a>
        </li>
    </ul>
</li>