<li class="sidebar-item {{ Route::is('panel.mahasiswa.pengajuan.*') ? 'active' : '' }} has-sub">
    <a href="#" class="sidebar-link">
        <i class="fas fa-folder"></i>
        <span>Pengajuan</span>
    </a>
    @php
        $types = \App\Models\Type::whereHas('mahasiswaTypeAccessPermissions', function ($query) {
            $query->where('mahasiswa_id', Auth::user()->id);
        })->get();
        $sidang_akhir_type = \App\Models\Type::where('name', 'Sidang Akhir')->first();
        $student_has_sidang_akhir = null;
        if ($sidang_akhir_type) {
            $student_has_submission_sidang_akhir = \App\Models\Submission::where('student_id', Auth::user()->id)
                ->where('type_id', $sidang_akhir_type->id)
                ->where('status', 'approved')
                ->first();
        }
    @endphp

    <ul class="submenu" style="display: {{ Route::is('panel.mahasiswa.pengajuan.*') || Route::is('panel.mahasiswa.tahapan-akhir.*') ? 'block' : 'none' }}">
        @foreach ($types as $type)
            <li
                class="submenu-item {{ Route::is('panel.mahasiswa.pengajuan.*') && request()->route('type_slug') == $type->slug ? 'active' : '' }}">
                <a href="{{ route('panel.mahasiswa.pengajuan.index', $type->slug) }}">{{ $type->name }}</a>
            </li>
        @endforeach

        @if ($student_has_submission_sidang_akhir)
            <li
                class="submenu-item {{ Route::is('panel.mahasiswa.tahapan-akhir.*') ? 'active' : '' }}">
                <a href="{{ route('panel.mahasiswa.tahapan-akhir.index') }}">Tahapan Akhir</a>
            </li>
        @endif
    </ul>
</li>