<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme shadow-sm">
    <!-- Logo & Brand -->
    <div class="app-brand demo" style="height: 75px;">
        <a href="{{ route('dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                {{-- LOGO INTEGRAL --}}
                <img src="https://res.cloudinary.com/dnwyqw6gn/image/upload/v1786770700/Integral_1_ykmzxx.png" 
                     alt="Integral Logo" 
                     style="width: 32px; filter: drop-shadow(0px 2px 4px rgba(105, 108, 255, 0.3));">
            </span>
            <span class="app-brand-text demo menu-text fw-bolder ms-2 text-uppercase" style="letter-spacing: 1px; font-size: 1.25rem;">Integral</span>
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-3">
        
        <!-- ========================================================= -->
        <!-- 1. MENU KHUSUS ADMIN BIDANG & SUPERADMIN                  -->
        <!-- ========================================================= -->
        @if(Auth::user()->role === 'superadmin' || Auth::user()->role === 'admin_bidang')
            <li class="menu-item {{ request()->is('dashboard*') || request()->is('/') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-home-circle"></i>
                    <div class="fw-bold">Dashboard Admin</div>
                </a>
            </li>

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Manajemen Pelatihan</span>
            </li>
            
            <li class="menu-item {{ request()->is('trainings*') && !request()->is('*attendance*') && !request()->is('*monitoring*') && !request()->is('*evaluasi*') ? 'active' : '' }}">
                <a href="{{ route('trainings.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-collection"></i>
                    <div>Daftar Pelatihan</div>
                </a>
            </li>

            <!-- MENU MONITORING PENGAJAR -->
            <li class="menu-item {{ request()->routeIs('monitoring.pengajar.*') ? 'active' : '' }}">
                <a href="{{ route('monitoring.pengajar.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-chalkboard"></i>
                    <div>Monitoring Pengajar</div>
                </a>
            </li>

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Kelola Instrumen</span>
            </li>

            <li class="menu-item {{ request()->is('monitoring-indicators*') || request()->is('questions*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-book-content"></i>
                    <div>Bank Instrumen</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item {{ request()->is('monitoring-indicators*') ? 'active' : '' }}">
                        <a href="{{ route('indicators.index') }}" class="menu-link">
                            <div>Indikator Monitoring</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('questions*') ? 'active' : '' }}">
                        <a href="{{ route('questions.index') }}" class="menu-link">
                            <div>Soal Evaluasi L1-L4</div>
                        </a>
                    </li>
                </ul>
            </li>

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Kelola Dokumen</span>
            </li>

            <li class="menu-item {{ request()->is('documents*') ? 'active' : '' }}">
                <a href="{{ route('documents.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-folder"></i>
                    <div>Manajemen Dokumen</div>
                </a>
            </li>

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Manajemen Alumni</span>
            </li>

            <li class="menu-item {{ request()->routeIs('alumni.index') ? 'active' : '' }}">
                <a href="{{ route('alumni.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-user-pin"></i>
                    <div>Kelola Alumni</div>
                </a>
            </li>

            @if(Auth::user()->role === 'superadmin')
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Pengaturan Sistem</span>
            </li>
            <li class="menu-item {{ request()->is('users*') ? 'active' : '' }}">
                <a href="{{ route('users.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-shield-quarter"></i>
                    <div>Kelola User PIC</div>
                </a>
            </li>
            @endif

            <!-- TOMBOL LOGOUT ADMIN -->
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Sesi Akun</span>
            </li>
            <li class="menu-item">
                <a href="{{ route('logout') }}" class="menu-link text-danger" 
                   onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();">
                    <i class="menu-icon tf-icons bx bx-power-off text-danger"></i>
                    <div class="fw-bold text-danger">Keluar / Logout</div>
                </a>
            </li>
        @endif

        <!-- ========================================================= -->
        <!-- 2. MENU KHUSUS TENAGA PENGAJAR                            -->
        <!-- ========================================================= -->
        @if(Auth::user()->role === 'pengajar')
            <li class="menu-item {{ request()->is('dashboard*') || request()->is('/') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-home-circle"></i>
                    <div class="fw-bold">Dashboard Pengajar</div>
                </a>
            </li>

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Portal Pengajar</span>
            </li>

            <li class="menu-item {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                <a href="{{ route('profile.edit') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-id-card"></i>
                    <div>Profil & Keuangan</div>
                </a>
            </li>

            <li class="menu-item {{ request()->routeIs('pengajar.schedules') || request()->routeIs('pengajar.schedule') ? 'active' : '' }}">
                <a href="{{ route('pengajar.schedule') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-chalkboard"></i>
                    <div>Daftar Pelatihan Saya</div>
                </a>
            </li>

            <li class="menu-item {{ request()->routeIs('pengajar.history') ? 'active' : '' }}">
                <a href="{{ route('pengajar.history') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-history"></i>
                    <div>Riwayat Pelatihan</div>
                </a>
            </li>

            <!-- TOMBOL LOGOUT PENGAJAR -->
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Sesi Akun</span>
            </li>
            <li class="menu-item">
                <a href="{{ route('logout') }}" class="menu-link text-danger" 
                   onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();">
                    <i class="menu-icon tf-icons bx bx-power-off text-danger"></i>
                    <div class="fw-bold text-danger">Keluar / Logout</div>
                </a>
            </li>
        @endif

        <!-- ========================================================= -->
        <!-- 3. MENU KHUSUS PESERTA (PARTICIPANT)                      -->
        <!-- ========================================================= -->
        @if(Auth::user()->role === 'participant')
            <li class="menu-item {{ request()->routeIs('participant.dashboard') ? 'active' : '' }}">
                <a href="{{ route('participant.dashboard') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-home-alt"></i>
                    <div class="fw-bold">Dashboard Saya</div>
                </a>
            </li>

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Portal Peserta</span>
            </li>

            <li class="menu-item {{ request()->routeIs('participant.trainings') ? 'active' : '' }}">
                <a href="{{ route('participant.trainings') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-list-ul"></i>
                    <div>Daftar Pelatihan</div>
                </a>
            </li>

            <li class="menu-item {{ request()->routeIs('participant.history') ? 'active' : '' }}">
                <a href="{{ route('participant.history') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-history"></i>
                    <div>Riwayat Pelatihan</div>
                </a>
            </li>

            <!-- TOMBOL LOGOUT PESERTA -->
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Sesi Akun</span>
            </li>
            <li class="menu-item">
                <a href="{{ route('logout') }}" class="menu-link text-danger" 
                   onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();">
                    <i class="menu-icon tf-icons bx bx-power-off text-danger"></i>
                    <div class="fw-bold text-danger">Keluar / Logout</div>
                </a>
            </li>
        @endif

    </ul>

    <!-- FORM LOGOUT GLOBAL UNTUK SIDEBAR (TERSEMBUNYI) -->
    <form id="logout-form-sidebar" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>
</aside>

@push('css')
<style>
    /* Transisi menu */
    .menu-vertical .menu-item .menu-link {
        transition: all 0.2s ease-in-out;
        border-radius: 0.375rem;
        margin: 0.15rem 1rem;
        padding-left: 1rem;
    }

    /* Efek Hover Menu Standar */
    .menu-vertical .menu-item:not(.active):not(.open) .menu-link:hover {
        background-color: rgba(105, 108, 255, 0.08) !important;
        transform: translateX(5px);
        color: #696cff !important;
    }

    .menu-vertical .menu-item:not(.active):not(.open) .menu-link:hover i {
        color: #696cff !important;
        transform: scale(1.1);
    }

    /* Efek Hover Khusus Tombol Logout */
    .menu-vertical .menu-item .menu-link.text-danger:hover {
        background-color: rgba(255, 62, 29, 0.1) !important;
        color: #ff3e1d !important;
    }
    .menu-vertical .menu-item .menu-link.text-danger:hover i {
        color: #ff3e1d !important;
    }

    /* Mempercantik Menu Header */
    .menu-header {
        margin: 1.25rem 0 0.5rem 0 !important;
        padding-left: 1.5rem;
    }

    .menu-header-text {
        color: #a1acb8 !important;
        font-weight: 700 !important;
        letter-spacing: 1px;
    }

    /* Badge Active Glow */
    .menu-vertical .menu-item.active > .menu-link {
        box-shadow: 0px 4px 8px rgba(105, 108, 255, 0.25);
        background: linear-gradient(72.47deg, #696cff 22.16%, rgba(105, 108, 255, 0.7) 76.47%) !important;
        color: #fff !important;
    }

    .menu-vertical .menu-item.active > .menu-link i {
        color: #fff !important;
    }
</style>
@endpush