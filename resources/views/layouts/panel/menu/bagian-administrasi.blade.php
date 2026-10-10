<li class="sidebar-item {{ Route::is('panel.bagian-administrasi.pengajuan*') ? 'active' : '' }} has-sub">
    <a href="#" class="sidebar-link">
        <i class="fas fa-folder"></i>
        <span>Monitoring Pengajuan</span>
    </a>
    <ul class="submenu"
        style="display: {{ Route::is('panel.bagian-administrasi.pengajuan*') || Route::is('panel.bagian-administrasi.tahapan-akhir.*') ? 'block' : 'none' }}">
        @php
            $types = \App\Models\Type::get();
        @endphp
        @foreach ($types as $type)
            @php
                $submission_is_not_readed_count = \App\Models\Submission::where('is_ready_to_be_processed', 1)
                    ->where('type_id', $type->id)
                    ->whereHas('format', function ($q) {
                        $q->where('is_tata_usaha_required', 1);
                    })
                    ->where('is_read_by_tata_usaha', 0)
                    ->count();
            @endphp

            <li
                class="submenu-item {{ Route::is('panel.bagian-administrasi.pengajuan*') && request()->route('type_slug') == $type->slug? 'active': '' }}">
                <a href="{{ route('panel.bagian-administrasi.pengajuan.index', $type->slug) }}">
                    {{ $type->name }} <span class="badge bg-{{ $submission_is_not_readed_count == 0 ? 'success' : 'danger' }}">{{ $submission_is_not_readed_count }}</span></a>
                </a>
            </li>
        @endforeach

        <li
            class="submenu-item {{ Route::is('panel.bagian-administrasi.tahapan-akhir.*') ? 'active' : '' }}">
            <a href="{{ route('panel.bagian-administrasi.tahapan-akhir.index') }}">Tahapan Akhir</a>
        </li>
    </ul>
</li>