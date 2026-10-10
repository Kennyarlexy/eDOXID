<li class="sidebar-title">Pengajuan Berkas</li>
<li class="sidebar-item {{ Route::is('panel.admin.pengajuan.*') ? 'active' : '' }} has-sub">
    <a href="#" class="sidebar-link">
        <i class="fas fa-folder"></i>
        <span>Monitoring Pengajuan</span>
    </a>
    <ul class="submenu" style="display: {{ Route::is('panel.admin.pengajuan.*') || Route::is('panel.admin.tahapan-akhir.*') ? 'block' : 'none' }}">
        @foreach (\App\Models\Type::get() as $type)
            <li
                class="submenu-item {{ Route::is('panel.admin.pengajuan.*') && request()->route('type_slug') == $type->slug ? 'active' : '' }}">
                <a href="{{ route('panel.admin.pengajuan.index', $type->slug) }}">{{ $type->name }}</a>
            </li>
        @endforeach

        <li
            class="submenu-item {{ Route::is('panel.admin.tahapan-akhir.*') ? 'active' : '' }}">
            <a href="{{ route('panel.admin.tahapan-akhir.index') }}">Tahapan Akhir</a>
        </li>
    </ul>
</li>

<li class="sidebar-item {{ Route::is('panel.admin.type*') ? 'active' : '' }}">
    <a href="{{ route('panel.admin.type.index') }}" class='sidebar-link'>
        <i class="fas fa-folder"></i>
        <span>Tipe Pengajuan Berkas</span>
    </a>
</li>

<li class="sidebar-item {{ Route::is('panel.admin.kelompok-keahlian*') ? 'active' : '' }}">
    <a href="{{ route('panel.admin.kelompok-keahlian.index') }}" class='sidebar-link'>
        <i class="fa-solid fa-box-archive"></i>
        <span>Kelompok Keahlian</span>
    </a>
</li>

<li class="sidebar-title">Pengaturan</li>
<li
    class="sidebar-item {{ Route::is('panel.admin.users.dosen.*') ||Route::is('panel.admin.users.mahasiswa.*') ||Route::is('panel.admin.users.admin.*') ||Route::is('panel.admin.users.bagian-administrasi.*')? 'active': '' }} has-sub">
    <a href="#" class="sidebar-link">
        <i class="fas fa-user-tie"></i>
        <span>Daftar Pengguna</span>
    </a>
    <ul class="submenu {{ Route::is('panel.admin.users.dosen.*') ||Route::is('panel.admin.users.mahasiswa.*') ||Route::is('panel.admin.users.admin.*') ||Route::is('panel.admin.users.bagian-administrasi.*')? 'active': '' }}"
        style="display: {{ Route::is('panel.admin.users.dosen.*') ||Route::is('panel.admin.users.mahasiswa.*') ||Route::is('panel.admin.users.admin.*') ||Route::is('panel.admin.users.bagian-administrasi.*')? 'block': 'none' }};">
        <li class="submenu-item {{ Route::is('panel.admin.users.admin*') ? 'active' : '' }}">
            <a href="{{ route('panel.admin.users.admin.index') }}">Admin</a>
        </li>
        <li class="submenu-item {{ Route::is('panel.admin.users.bagian-administrasi*') ? 'active' : '' }}">
            <a href="{{ route('panel.admin.users.bagian-administrasi.index') }}">Bagian Administrasi</a>
        </li>
        <li class="submenu-item {{ Route::is('panel.admin.users.dosen*') ? 'active' : '' }}">
            <a href="{{ route('panel.admin.users.dosen.index') }}">Dosen</a>
        </li>
        <li class="submenu-item {{ Route::is('panel.admin.users.mahasiswa*') ? 'active' : '' }}">
            <a href="{{ route('panel.admin.users.mahasiswa.index') }}">Mahasiswa</a>
        </li>
    </ul>
</li>

<li
    class="sidebar-item {{ Route::is('panel.admin.setting.index') || Route::is('panel.admin.grading*') || Route::is('panel.admin.setting.google.drive*') || Route::is('panel.admin.setting.whatsapp*') ? 'active': '' }} has-sub">
    <a href="#" class="sidebar-link">
        <i class="fas fa-cogs"></i>
        <span>Pengaturan</span>
    </a>
    <ul class="submenu {{ Route::is('panel.admin.setting.index') || Route::is('panel.admin.grading*') || Route::is('panel.admin.setting.google.drive*') || Route::is('panel.admin.setting.whatsapp*') ? 'active': '' }}"
        style="display: {{ Route::is('panel.admin.setting.index') || Route::is('panel.admin.grading*') || Route::is('panel.admin.setting.google.drive*') || Route::is('panel.admin.setting.whatsapp*') ? 'block': 'none' }};">
        <li class="submenu-item {{ Route::is('panel.admin.setting.index') ? 'active' : '' }}">
            <a href="{{ route('panel.admin.setting.index') }}">Website</a>
        </li>
        <li class="submenu-item {{ Route::is('panel.admin.setting.google.drive.index') ? 'active' : '' }}">
            <a href="{{ route('panel.admin.setting.google.drive.index') }}">Google Drive</a>
        </li>
        <li class="submenu-item {{ Route::is('panel.admin.setting.whatsapp.index') ? 'active' : '' }}">
            <a href="{{ route('panel.admin.setting.whatsapp.index') }}">Whatsapp Notification</a>
        </li>
        <li class="submenu-item {{ Route::is('panel.admin.grading*') ? 'active' : '' }}">
            <a href="{{ route('panel.admin.grading.index') }}">Grading Penilaian</a>
        </li>
    </ul>
</li>
