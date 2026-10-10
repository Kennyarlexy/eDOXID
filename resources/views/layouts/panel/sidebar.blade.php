<div id="sidebar" class="active">
    <div class="sidebar-wrapper active">
        <div class="sidebar-header">
            <div class="d-flex justify-content-between">
                <div class="logo">
                    <a href="{{ route('panel.dashboard') }}">
                        <img src="{{ \App\Models\Setting::where('key', 'site_logo')->first() ? asset('storage/settings/' . \App\Models\Setting::where('key', 'site_logo')->first()->value) : asset('/img/logo/logo.png') }}" alt="Logo" srcset="">
                    </a>
                </div>
                <div class="toggler">
                    <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
                </div>
            </div>
        </div>
        <div class="sidebar-menu">
            <ul class="menu">
                <li class="sidebar-title">Halo, Selamat Datang</li>
                <li class="sidebar-item ">
                    <a class='sidebar-link'>
                        <div class="avatar">
                            <img src="{{ Auth::user()->profile_image == null || Auth::user()->profile_image == '//spota.untan.ac.id/img/' ? asset('/img/user.png') : Auth::user()->profile_image }}" alt="" srcset="">
                        </div>
                        <span>
                            {{ Auth::user()->name }}
                            <br/>
                            <span style="font-size:11px" class="text-muted text-capitalize ms-0">{{ Auth::user()->role }} {{ Auth::user()->position ? '| ' . Auth::user()->position : '' }}</span>
                        </span>
                    </a>
                </li>

                <li class="sidebar-title">Menu</li>

                <li class="sidebar-item {{ Route::is('panel.dashboard') ? 'active' : '' }} ">
                    <a href="{{ route('panel.dashboard') }}" class='sidebar-link'>
                        <i class="bi bi-grid-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                @if (Auth::user()->role === \App\Models\User::ROLE_ADMIN)
                    @include('layouts.panel.menu.admin')
                @endif

                @if (Auth::user()->role === \App\Models\User::ROLE_DOSEN)
                    @include('layouts.panel.menu.dosen')
                @endif

                @if (Auth::user()->role === \App\Models\User::ROLE_BAGIAN_ADMINISTRASI)
                    @include('layouts.panel.menu.bagian-administrasi')
                @endif

                @if (Auth::user()->role === \App\Models\User::ROLE_MAHASISWA)
                    @include('layouts.panel.menu.mahasiswa')
                @endif

                <li class="sidebar-item {{ Route::is('panel.profile*') ? 'active' : '' }}">
                    <a href="{{ route('panel.profile') }}" class='sidebar-link'>
                        <i class="fas fa-id-card"></i>
                        <span>Profil</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a class='sidebar-link' style="cursor:pointer" onclick="logoutAction()">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>

                    <form action="{{ route('logout') }}" method="POST" style="display:none">
                        @csrf
                        <button type="submit" id="logout" style="display:none"></button>
                    </form>
                </li>

            </ul>
        </div>
        <button class="sidebar-toggler btn x"><i data-feather="x"></i></button>
    </div>
</div>


