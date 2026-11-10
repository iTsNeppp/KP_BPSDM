@extends('layouts.master')

@section('title', 'Dashboard Pengajar')

@push('css')
<style>
    .transition-all { 
        transition: all 0.3s ease; 
    }
    .hover-shadow:hover { 
        transform: translateY(-3px); 
        box-shadow: 0 8px 18px rgba(105, 108, 255, 0.12) !important;
    }
    .rounded-circle {
        object-fit: cover;
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <!-- ======================================================== -->
    <!-- 1. BANNER SAMBUTAN (FULL-WIDTH)                          -->
    <!-- ======================================================== -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-label-info border-0 shadow-sm">
                <div class="d-flex align-items-end row g-0">
                    <div class="col-12 col-md-8">
                        <div class="card-body p-4">
                            <h4 class="card-title text-info mb-1 fw-bold">Selamat Datang, {{ $user->name }}! 👨‍🏫</h4>
                            <p class="text-muted small mb-2">
                                <span class="badge bg-info text-white me-1">PENGAJAR / FASILITATOR</span>
                                {{ $user->jabatan ?? 'Tenaga Pengajar' }}
                            </p>
                            <p class="mb-4 text-dark small">
                                NIP/NIK: <span class="fw-bold">{{ $user->nip_nik ?? '-' }}</span><br>
                                Instansi: <span class="fw-bold">{{ $user->pengajar->instansi ?? $user->instansi ?? '-' }}</span><br>
                                Kelola administrasi pengajaran, bahan tayang, dan capaian jam pelajaran Anda di sini.
                            </p>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-info text-white shadow-sm">
                                    <i class="bx bx-user me-1"></i> Pengaturan Profil & Keuangan
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 text-center text-md-end pe-md-4 pb-3 pb-md-0">
                        <div class="card-body pb-0 px-0 px-md-2 text-center">
                            <div class="mb-3">
                                @if($user->profile_photo)
                                    <img src="{{ asset('storage/' . $user->profile_photo) }}" 
                                         alt="Avatar" 
                                         class="rounded-circle shadow border border-3 border-white" 
                                         style="width: 115px; height: 115px; object-fit: cover;" />
                                @elseif($user->avatar)
                                    <img src="{{ $user->avatar }}" 
                                         alt="Avatar" 
                                         class="rounded-circle shadow border border-3 border-white" 
                                         style="width: 115px; height: 115px; object-fit: cover;" />
                                @else
                                    <img src="{{ asset('assets/img/illustrations/man-with-laptop-light.png') }}" 
                                         height="125" alt="Illustration" />
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- 2. SECTION CAPAIANKU (DESAIN TEMPLATE SNEAT)             -->
    <!-- ======================================================== -->
    <h5 class="fw-bold mb-3 text-dark">
        <i class="bx bx-chart me-1 text-primary"></i> Capaianku
    </h5>

    <div class="row">
        <!-- 1. Total JP -->
        <div class="col-12 col-sm-6 col-lg-4 mb-4">
            <div class="card shadow-sm border-0 hover-shadow transition-all h-100">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="avatar bg-label-primary p-2 rounded me-3">
                        <i class="bx bx-time-five text-primary h4 mb-0"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block fw-semibold">Total JP</small>
                        <h4 class="mb-0 text-primary fw-bold">{{ $totalJp ?? 0 }} <span class="fs-6 text-muted fw-normal">JP</span></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. JP Tahun Ini -->
        <div class="col-12 col-sm-6 col-lg-4 mb-4">
            <div class="card shadow-sm border-0 hover-shadow transition-all h-100">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="avatar bg-label-success p-2 rounded me-3">
                        <i class="bx bx-calendar text-success h4 mb-0"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block fw-semibold">JP Tahun Ini</small>
                        <h4 class="mb-0 text-success fw-bold">{{ $jpTahunIni ?? 0 }} <span class="fs-6 text-muted fw-normal">JP</span></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Jumlah Pelatihan Diajarkan -->
        <div class="col-12 col-sm-6 col-lg-4 mb-4">
            <div class="card shadow-sm border-0 hover-shadow transition-all h-100">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="avatar bg-label-info p-2 rounded me-3">
                        <i class="bx bx-chalkboard text-info h4 mb-0"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block fw-semibold">Jumlah Pelatihan Diajarkan</small>
                        <h4 class="mb-0 text-info fw-bold">{{ $totalPelatihan ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Pelatihan Diajarkan Tahun Ini -->
        <div class="col-12 col-sm-6 col-lg-6 mb-4">
            <div class="card shadow-sm border-0 hover-shadow transition-all h-100">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="avatar bg-label-warning p-2 rounded me-3">
                        <i class="bx bx-calendar-check text-warning h4 mb-0"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block fw-semibold">Pelatihan Diajarkan Tahun Ini</small>
                        <h4 class="mb-0 text-warning fw-bold">{{ $pelatihanTahunIni ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. Persentase Capaian JP -->
        <div class="col-12 col-sm-12 col-lg-6 mb-4">
            <div class="card shadow-sm border-0 hover-shadow transition-all h-100">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="avatar bg-label-danger p-2 rounded me-3">
                        <i class="bx bx-tachometer text-danger h4 mb-0"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block fw-semibold">Persentase Capaian JP</small>
                        <h4 class="mb-0 text-danger fw-bold">{{ $persentaseJp ?? 0 }}%</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection