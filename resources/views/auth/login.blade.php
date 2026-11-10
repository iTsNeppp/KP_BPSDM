@extends('layouts.auth')

@section('content')
<div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner">
        
        <!-- Tombol Kembali ke Beranda -->
        <div class="mb-3 animate__animated animate__fadeInLeft">
            <a href="{{ url('/') }}" class="d-flex align-items-center text-muted fw-semibold">
                <i class="bx bx-chevron-left fs-4"></i>
                <span>Kembali ke Beranda</span>
            </a>
        </div>

        <!-- Login Card -->
        <div class="card shadow-lg border-0 animate__animated animate__fadeInUp" style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-radius: 20px;">
            <div class="card-body p-3 p-sm-4 p-md-5">
                <!-- Branding Logo -->
                <div class="app-brand justify-content-center mb-4">
                    <a href="/" class="app-brand-link gap-2">
                        <span class="app-brand-logo demo">
                            <img src="https://res.cloudinary.com/dnwyqw6gn/image/upload/v1786770700/Integral_1_ykmzxx.png" alt="Integral Logo" style="width: 55px; filter: drop-shadow(0px 4px 10px rgba(105, 108, 255, 0.3));">
                        </span>
                    </a>
                </div>
                
                <div class="text-center mb-4">
                    <h4 class="mb-1 fw-bold text-dark" style="letter-spacing: -0.5px;">Integral <span class="text-primary">Technology</span></h4>
                    <p class="text-muted small text-uppercase fw-semibold" style="letter-spacing: 1px;">Sistem Pengelolaan Terintegrasi</p>
                </div>
                
                {{-- Alert Error --}}
                @if(session('error'))
                    <div class="alert alert-danger border-0 small d-flex align-items-center animate__animated animate__shakeX" role="alert">
                        <i class="bx bx-error-circle me-2"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif

                <form id="formAuthentication" class="mb-3" action="{{ route('login') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="username" class="form-label fw-bold">Username / NIP</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="bx bx-user"></i></span>
                            <input type="text" class="form-control @error('username') is-invalid @enderror" name="username" placeholder="Masukkan ID Anda" autofocus value="{{ old('username') }}" style="border-left: none;" />
                        </div>
                        @error('username')
                            <small class="text-danger mt-1 d-block">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Input Password dengan Toggle Show/Hide -->
                    <div class="mb-3 form-password-toggle">
                        <div class="d-flex justify-content-between">
                            <label class="form-label fw-bold" for="password">Password</label>
                            <a href="#">
                                <small class="text-primary fw-semibold">Lupa Password?</small>
                            </a>
                        </div>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="bx bx-lock-alt"></i></span>
                            <input type="password" id="passwordInput" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="••••••••" style="border-left: none; border-right: none;" />
                            <span class="input-group-text cursor-pointer" id="togglePassword">
                                <i class="bx bx-hide" id="togglePasswordIcon"></i>
                            </span>
                        </div>
                        @error('password')
                            <small class="text-danger mt-1 d-block">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <button class="btn btn-primary d-grid w-100 py-2 fw-bold shadow-sm btn-login" type="submit">
                            MASUK KE SISTEM
                        </button>
                    </div>
                </form>

                <div class="divider my-4">
                    <div class="divider-text text-muted small text-uppercase">Atau akses cepat</div>
                </div>

                {{-- TOMBOL GOOGLE MODERN --}}
                <div class="d-flex justify-content-center">
                    <a href="{{ route('auth.google') }}" class="btn btn-outline-white w-100 d-flex align-items-center justify-content-center py-2 google-btn shadow-xs">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" alt="Google Logo" style="width: 18px;" class="me-2">
                        <span class="fw-bold text-dark" style="font-size: 0.9rem;">Google Account</span>
                    </a>
                </div>

                <div class="text-center mt-5">
                    <p class="mb-0 small text-muted">
                        BPSDM Provinsi Jawa Barat &copy; {{ date('Y') }}
                    </p>
                    <small class="fw-bold text-primary" style="font-size: 10px; letter-spacing: 2px;">INTEGRAL TECH</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Background Elements -->
<div class="bg-shape-1 d-none d-sm-block"></div>
<div class="bg-shape-2 d-none d-sm-block"></div>

<style>
    body {
        background-color: #f4f7ff;
        background-image: radial-gradient(#696cff 0.5px, transparent 0.5px);
        background-size: 30px 30px;
        min-height: 100vh;
        position: relative;
        overflow-x: hidden;
    }

    /* Responsivitas Kontainer Utama */
    .authentication-inner {
        max-width: 450px !important;
        width: 100%;
        margin: 0 auto;
        padding: 0 1.2rem;
        position: relative;
        z-index: 10;
    }

    @media (max-width: 576px) {
        .hero-title { font-size: 1.5rem; }
        .card-body { padding: 1.5rem !important; }
        .authentication-wrapper.authentication-basic .authentication-inner { padding: 0 1rem; }
    }

    .bg-shape-1 {
        position: fixed;
        top: -100px;
        right: -100px;
        width: 400px;
        height: 400px;
        background: linear-gradient(135deg, rgba(105, 108, 255, 0.2) 0%, rgba(105, 108, 255, 0) 100%);
        border-radius: 50%;
        z-index: -1;
    }

    .bg-shape-2 {
        position: fixed;
        bottom: -150px;
        left: -150px;
        width: 500px;
        height: 500px;
        background: linear-gradient(135deg, rgba(3, 195, 236, 0.15) 0%, rgba(3, 195, 236, 0) 100%);
        border-radius: 50%;
        z-index: -1;
    }

    .input-group-text {
        background-color: #fcfcff;
        border-color: #d9dee3;
        color: #696cff;
    }
    .form-control:focus, .form-control:focus + .input-group-text {
        border-color: #696cff !important;
    }

    .btn-login {
        background: linear-gradient(45deg, #696cff, #43459d);
        border: none;
        transition: all 0.3s;
    }
    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(105, 108, 255, 0.3) !important;
    }

    .google-btn {
        border: 1px solid #d9dee3;
        background: white;
        transition: all 0.2s;
    }
    .google-btn:hover {
        background-color: #f8f9fa !important;
        border-color: #696cff !important;
        transform: scale(1.01);
    }

    .divider-text {
        background-color: transparent !important;
        font-weight: 700;
        letter-spacing: 1px;
    }
    .divider::before, .divider::after {
        border-color: #d9dee3 !important;
    }
    
    .shadow-xs {
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .cursor-pointer {
        cursor: pointer;
    }
</style>

<!-- SCRIPT UNTUK TOGGLE LIHAT PASSWORD -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('passwordInput');
    const icon = document.getElementById('togglePasswordIcon');

    if (toggleBtn && passwordInput && icon) {
        toggleBtn.addEventListener('click', function () {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bx-hide');
                icon.classList.add('bx-show');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bx-show');
                icon.classList.add('bx-hide');
            }
        });
    }
});
</script>
@endsection