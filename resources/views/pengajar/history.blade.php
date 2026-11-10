@extends('layouts.master')

@section('title', 'Riwayat Pelatihan Pengajar')

@push('css')
<style>
    .table-responsive-custom {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
        width: 100%;
    }
    .table-responsive-custom table {
        min-width: 850px;
    }
    .text-wrap-cell {
        white-space: normal !important;
        word-break: break-word;
    }
    .hover-shadow:hover { 
        transform: translateY(-3px); 
        box-shadow: 0 8px 18px rgba(105, 108, 255, 0.12) !important;
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <!-- Header Halaman -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold py-1 mb-1">
                <span class="text-muted fw-light">Portal Pengajar /</span> Riwayat Pelatihan
            </h4>
            <p class="text-muted small mb-0">
                Arsip seluruh pelatihan dan sesi materi yang telah selesai Anda ajarkan.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pengajar.schedule') }}" class="btn btn-outline-primary btn-sm shadow-sm">
                <i class="bx bx-calendar me-1"></i> Lihat Jadwal Aktif
            </a>
        </div>
    </div>

    <!-- Ringkasan Statistik Riwayat -->
    <div class="row mb-4">
        <div class="col-12 col-sm-6 col-md-6 mb-3">
            <div class="card shadow-sm border-0 hover-shadow transition-all">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="avatar bg-label-success p-2 rounded me-3">
                        <i class="bx bx-check-double text-success h4 mb-0"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Total Pelatihan Selesai</small>
                        <h4 class="mb-0 text-success fw-bold">{{ $trainings->count() }} <span class="fs-6 text-muted fw-normal">Pelatihan</span></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-6 mb-3">
            <div class="card shadow-sm border-0 hover-shadow transition-all">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="avatar bg-label-primary p-2 rounded me-3">
                        <i class="bx bx-time-five text-primary h4 mb-0"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Total Akumulasi JP Terlaksana</small>
                        <h4 class="mb-0 text-primary fw-bold">{{ $totalJpRiwayat ?? 0 }} <span class="fs-6 text-muted fw-normal">JP</span></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABEL RIWAYAT PELATIHAN -->
    <div class="card shadow-sm border-0 overflow-hidden">
        <div class="card-header border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="m-0 fw-bold text-dark">
                <i class="bx bx-history me-2 text-success"></i>Daftar Riwayat Pelatihan Selesai
            </h5>

            <!-- Form Pencarian Riwayat -->
            <form action="{{ route('pengajar.history') }}" method="GET" class="d-flex gap-2">
                <div class="input-group input-group-sm" style="max-width: 250px;">
                    <input type="text" name="search" class="form-control" placeholder="Cari pelatihan / bidang..." value="{{ $search ?? '' }}">
                    <button class="btn btn-outline-secondary" type="submit"><i class="bx bx-search"></i></button>
                </div>
                @if($search)
                    <a href="{{ route('pengajar.history') }}" class="btn btn-sm btn-outline-danger" title="Reset">
                        <i class="bx bx-x"></i>
                    </a>
                @endif
            </form>
        </div>
        
        <div class="table-responsive-custom">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="4%" class="text-center">No</th>
                        <th style="min-width: 280px;">Nama Pelatihan & Materi</th>
                        <th style="min-width: 150px;">Bidang</th>
                        <th style="min-width: 240px;">Sesi & Lokasi</th>
                        <th style="min-width: 100px;" class="text-center">Total JP</th>
                        <th style="min-width: 130px;" class="text-center">Dokumen</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($trainings as $t)
                    <tr>
                        <!-- 1. Kolom Nomor Urut -->
                        <td class="text-center fw-bold">{{ $loop->iteration }}</td>

                        <!-- 2. Kolom Nama Pelatihan & Materi -->
                        <td class="text-wrap-cell">
                            <strong class="text-dark d-block mb-1 fs-6">{{ $t->nama_pelatihan }}</strong>
                            <span class="badge bg-label-secondary mb-2">Angkatan {{ $t->angkatan }}</span>
                            
                            <div class="mt-1">
                                <ul class="list-unstyled mb-0 small ps-1">
                                    @forelse($t->schedules as $s)   
                                            <div>
                                                <span class="text-dark">{{ $s->activity }}</span>
                                                @if($s->jp)
                                                    <span class="badge bg-label-info ms-1" style="font-size: 0.65rem;">{{ $s->jp }} JP</span>
                                                @endif
                                            </div>
                                        </li>
                                    @empty
                                        <li class="text-muted fst-italic">Tidak ada catatan materi.</li>
                                    @endforelse
                                </ul>
                            </div>
                        </td>

                        <!-- 3. Kolom Bidang -->
                        <td class="text-wrap-cell">
                            <span class="badge bg-label-primary text-wrap mb-1">{{ $t->bidang }}</span>
                            <small class="text-muted d-block">Model: {{ ucfirst($t->model ?? 'Standar') }}</small>
                        </td>

                        <!-- 4. Kolom Sesi & Lokasi -->
                        <td class="text-wrap-cell">
                            <div class="mb-2">
                                <small class="text-muted d-block mb-1">
                                    <i class="bx bx-calendar me-1 text-secondary"></i>
                                    {{ \Carbon\Carbon::parse($t->tgl_mulai)->translatedFormat('d M Y') }} s.d {{ \Carbon\Carbon::parse($t->tgl_selesai)->translatedFormat('d M Y') }}
                                </small>
                                <small class="text-muted d-block">
                                    <i class="bx bx-map-pin me-1 text-danger"></i>{{ $t->lokasi ?? 'BPSDM Jabar' }}
                                </small>
                            </div>

    
                            <ul class="list-unstyled mb-0 small ps-1">
                                @forelse($t->schedules as $s)
                                    <li class="mb-1 text-muted">
                                        <strong>{{ \Carbon\Carbon::parse($s->date)->translatedFormat('d M Y') }}:</strong>
                                        <span>{{ $s->start_time }} - {{ $s->end_time }} WIB</span>
                                    </li>
                                @empty
                                    <li class="text-muted fst-italic">-</li>
                                @endforelse
                            </ul>
                        </td>

                        <!-- 5. Kolom Total JP -->
                        <td class="text-center">
                            <span class="badge bg-label-success fs-6 fw-bold">
                                {{ $t->schedules->sum('jp') }} JP
                            </span>
                        </td>

                        <!-- 6. Kolom Dokumen (Unduh PDF) -->
                        <td class="text-center">
                            <a href="{{ route('schedules.pdf', $t->id) }}" class="btn btn-sm btn-outline-danger shadow-sm" target="_blank" title="Unduh Arsip Jadwal PDF">
                                <i class="bx bxs-file-pdf me-1"></i> Unduh PDF
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bx bx-history fs-1 d-block mb-2 text-secondary"></i>
                            <h6 class="fw-bold mb-1">Belum Ada Riwayat Pelatihan</h6>
                            <p class="small text-muted mb-0">Pelatihan yang telah selesai Anda ajar akan otomatis terarsipkan di halaman ini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection