@extends('layouts.master')

@section('title', 'Monitoring Pengajar & Keuangan')

@push('css')
<style>
    .table-responsive-custom {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
        width: 100%;
    }
    .table-responsive-custom table {
        min-width: 950px;
    }
    .text-wrap-cell {
        white-space: normal !important;
        word-break: break-word;
    }
    
    /* Desain Input Group Filter Modern */
    .filter-input-group {
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #d9dee3;
        transition: all 0.2s ease;
    }
    .filter-input-group:focus-within {
        border-color: #696cff !important;
        box-shadow: 0 0 0 0.15rem rgba(105, 108, 255, 0.15);
    }
    .filter-input-group .input-group-text {
        background-color: #fcfcff;
        border: none;
    }
    .filter-input-group .form-select,
    .filter-input-group .form-control {
        border: none;
        box-shadow: none !important;
    }
    .shadow-xs {
        box-shadow: 0 2px 4px rgba(0,0,0,0.03);
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <!-- Header Halaman & Tombol Ekspor Semua Data -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold py-1 mb-1">
                <span class="text-muted fw-light">Monitoring /</span> Data Pengajar & Keuangan
            </h4>
            <p class="text-muted small mb-0">
                Laporan penugasan jam pelajaran (JP), informasi rekening honorarium, dan kelengkapan dokumen pengajar.
            </p>
        </div>
        <!-- TOMBOL EKSPOR SEMUA DATA -->
        <div class="d-flex gap-2">
            <a href="{{ route('monitoring.pengajar.export', ['search' => $search, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-success btn-sm shadow-sm d-flex align-items-center">
                <i class="bx bx-spreadsheet me-1 fs-5"></i> Ekspor Laporan Keuangan (Excel)
            </a>
        </div>
    </div>

    <!-- Ringkasan Statistik -->
    <div class="row mb-4">
        <div class="col-12 col-sm-6 col-lg-3 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="avatar bg-label-primary p-2 rounded me-3">
                        <i class="bx bx-user text-primary h4 mb-0"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block fw-semibold">Total Pengajar</small>
                        <h4 class="mb-0 text-primary fw-bold">{{ $stats['total_pengajar'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="avatar bg-label-success p-2 rounded me-3">
                        <i class="bx bx-time-five text-success h4 mb-0"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block fw-semibold">Total JP Periode Ini</small>
                        <h4 class="mb-0 text-success fw-bold">{{ $stats['total_jp_global'] }} <span class="fs-6 text-muted fw-normal">JP</span></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="avatar bg-label-info p-2 rounded me-3">
                        <i class="bx bx-calendar-check text-info h4 mb-0"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block fw-semibold">Total Sesi Mengajar</small>
                        <h4 class="mb-0 text-info fw-bold">{{ $stats['total_sesi_global'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="avatar bg-label-warning p-2 rounded me-3">
                        <i class="bx bx-wallet text-warning h4 mb-0"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block fw-semibold">Rekening Terdata</small>
                        <h4 class="mb-0 text-warning fw-bold">{{ $stats['rekening_lengkap'] }} / {{ $stats['total_pengajar'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABEL DAFTAR PENGAJAR & KEUANGAN -->
    <div class="card shadow-sm border-0 overflow-hidden">
        
        <!-- HEADER KARTU & FILTER PERIODE DENGAN DESAIN MODERN -->
        <div class="card-header border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-3 bg-white">
            <div>
                <h5 class="m-0 fw-bold text-dark d-flex align-items-center">
                    <i class="bx bx-chalkboard me-2 text-primary fs-4"></i>Daftar Pengajar & Laporan Keuangan
                </h5>

                @php
                    $bulanList = [
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                    ];
                @endphp

                <!-- Indikator Filter Aktif -->
                @if($bulan || $tahun || $search)
                    <div class="mt-1 d-flex flex-wrap align-items-center gap-1">
                        <small class="text-muted me-1" style="font-size: 11px;">Filter aktif:</small>
                        @if($bulan)
                            <span class="badge bg-label-info py-1" style="font-size: 10px;">
                                <i class="bx bx-calendar me-1"></i>{{ $bulanList[(int)$bulan] ?? 'Bulan '.$bulan }}
                            </span>
                        @endif
                        @if($tahun)
                            <span class="badge bg-label-primary py-1" style="font-size: 10px;">
                                <i class="bx bx-time me-1"></i>Thn {{ $tahun }}
                            </span>
                        @endif
                        @if($search)
                            <span class="badge bg-label-secondary py-1" style="font-size: 10px;">
                                <i class="bx bx-search me-1"></i>"{{ $search }}"
                            </span>
                        @endif
                    </div>
                @endif
            </div>

            <!-- FORM FILTER BAR MODERN -->
            <form action="{{ route('monitoring.pengajar.index') }}" method="GET" class="d-flex flex-wrap align-items-center gap-2">
                
                <!-- 1. Dropdown Bulan Bergaya Modern -->
                <div class="input-group input-group-sm input-group-merge filter-input-group shadow-xs">
                    <span class="input-group-text text-primary pe-1">
                        <i class="bx bx-calendar fs-6"></i>
                    </span>
                    <select name="bulan" class="form-select ps-1 fw-semibold text-secondary" style="min-width: 145px;" onchange="this.form.submit()">
                        <option value="">-- Semua Bulan --</option>
                        @foreach($bulanList as $k => $v)
                            <option value="{{ $k }}" {{ ($bulan == $k) ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- 2. Dropdown Tahun Bergaya Modern -->
                <div class="input-group input-group-sm input-group-merge filter-input-group shadow-xs">
                    <span class="input-group-text text-info pe-1">
                        <i class="bx bx-calendar-event fs-6"></i>
                    </span>
                    <select name="tahun" class="form-select ps-1 fw-semibold text-secondary" style="min-width: 115px;" onchange="this.form.submit()">
                        <option value="">-- Semua Thn --</option>
                        @for($y = date('Y') + 1; $y >= date('Y') - 3; $y--)
                            <option value="{{ $y }}" {{ ($tahun == $y) ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <!-- 3. Input Pencarian -->
                <div class="input-group input-group-sm input-group-merge filter-input-group shadow-xs" style="max-width: 200px;">
                    <span class="input-group-text text-muted pe-1">
                        <i class="bx bx-search fs-6"></i>
                    </span>
                    <input type="text" name="search" class="form-control ps-1" placeholder="Nama / NIP..." value="{{ $search ?? '' }}">
                    <button class="btn btn-primary px-2 shadow-none" type="submit"><i class="bx bx-right-arrow-alt"></i></button>
                </div>

                <!-- Tombol Reset Filter -->
                @if($search || $bulan || $tahun)
                    <a href="{{ route('monitoring.pengajar.index') }}" class="btn btn-sm btn-outline-danger d-flex align-items-center shadow-xs" title="Reset Semua Filter">
                        <i class="bx bx-reset me-1"></i> Reset
                    </a>
                @endif
            </form>
        </div>
        
        <div class="table-responsive-custom">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="4%">No</th>
                        <th style="min-width: 220px;">Data Pengajar & NIP</th>
                        <th style="min-width: 160px;">Jabatan & Pangkat</th>
                        <th style="min-width: 150px;">Laporan JP</th>
                        <th style="min-width: 240px;">Data Keuangan / Rekening</th>
                        <th style="min-width: 160px;" class="text-center">Ekspor & Dokumen</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($pengajars as $index => $p)
                    @php
                        $pj = $p->pengajar;
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>

                        <!-- Nama & NIP -->
                        <td class="text-wrap-cell">
                            <strong class="text-dark fs-6 d-block">{{ $p->name }}</strong>
                            <small class="text-muted d-block">
                                <i class="bx bx-id-card me-1"></i>NIP: {{ $p->nip_nik ?? '-' }}
                            </small>
                            <small class="text-muted d-block">
                                <i class="bx bx-building me-1"></i>{{ $pj->instansi ?? $p->instansi ?? '-' }}
                            </small>
                        </td>

                        <!-- Jabatan & Pangkat -->
                        <td class="text-wrap-cell">
                            <span class="badge bg-label-info mb-1 d-inline-block">{{ $p->jabatan ?? 'Tenaga Pengajar' }}</span><br>
                            <small class="text-muted">{{ $pj->pangkat_golongan ?? '-' }}</small>
                        </td>

                        <!-- Laporan JP & Tombol Cetak Detail JP -->
                        <td>
                            <div class="d-flex flex-column align-items-start">
                                <span class="badge bg-label-success fs-6 fw-bold mb-1">
                                    {{ $p->total_jp }} JP
                                </span>
                                <small class="text-muted mb-2">{{ $p->total_sessions }} Sesi Materi</small>
                                
                                <a href="{{ route('monitoring.pengajar.keterangan.pdf', ['user_id' => $p->id, 'bulan' => $bulan, 'tahun' => $tahun]) }}" target="_blank" class="btn btn-xs btn-outline-primary d-inline-flex align-items-center shadow-none py-1" style="font-size: 0.72rem;" title="Unduh Detail JP Periode Ini">
                                    <i class="bx bxs-file-pdf me-1"></i> Detail JP (PDF)
                                </a>
                            </div>
                        </td>

                        <!-- Data Keuangan -->
                        <td class="text-wrap-cell">
                            @if($pj && $pj->nomor_rekening)
                                <div class="bg-light p-2 rounded border small">
                                    <div><strong>Bank:</strong> {{ $pj->nama_bank ?? '-' }}</div>
                                    <div><strong>No. Rek:</strong> <code class="text-primary">{{ $pj->nomor_rekening ?? '-' }}</code></div>
                                    <div><strong>A/N:</strong> {{ $pj->rekening_atas_nama ?? $pj->nama_rekening ?? '-' }}</div>
                                    <div><strong>NPWP:</strong> {{ $pj->npwp ?? '-' }}</div>
                                </div>
                            @else
                                <span class="badge bg-label-danger"><i class="bx bx-x me-1"></i>Belum Mengisi Rekening</span>
                            @endif
                        </td>

                        <!-- Kolom Export & Berkas Dokumen -->
                        <td class="text-center">
                            <div class="d-flex flex-column gap-1 align-items-center">
                                <!-- Tombol Ekspor Excel Keuangan Pengajar Ini -->
                                <a href="{{ route('monitoring.pengajar.export', ['search' => $p->nip_nik ?? $p->name, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-success text-white w-100 shadow-sm mb-1 py-1" style="font-size: 0.75rem;" title="Unduh Data Keuangan Excel">
                                    <i class="bx bx-spreadsheet me-1"></i> Excel Keuangan
                                </a>

                                <!-- Berkas PDF Pengajar -->
                                @if($pj && $pj->cv_path)
                                    <a href="{{ route('monitoring.pengajar.file.view', ['user_id' => $p->id, 'type' => 'cv']) }}" target="_blank" class="badge bg-label-primary text-decoration-none w-100">
                                        <i class="bx bx-file me-1"></i> CV
                                    </a>
                                @endif
                                @if($pj && $pj->sertifikat_path)
                                    <a href="{{ route('monitoring.pengajar.file.view', ['user_id' => $p->id, 'type' => 'sertifikat']) }}" target="_blank" class="badge bg-label-success text-decoration-none w-100">
                                        <i class="bx bx-award me-1"></i> Sertifikat
                                    </a>
                                @endif
                                @if($pj && $pj->surat_tugas_path)
                                    <a href="{{ route('monitoring.pengajar.file.view', ['user_id' => $p->id, 'type' => 'surat_tugas']) }}" target="_blank" class="badge bg-label-warning text-decoration-none w-100">
                                        <i class="bx bx-file-blank me-1"></i> Surat Tugas
                                    </a>
                                @endif
                                @if(!$pj || (!$pj->cv_path && !$pj->sertifikat_path && !$pj->surat_tugas_path))
                                    <span class="text-muted small fst-italic">-</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bx bx-user-x fs-1 d-block mb-2 text-secondary"></i>
                            <h6 class="fw-bold mb-1">Data Pengajar Tidak Ditemukan</h6>
                            <p class="small text-muted mb-0">Tidak ada penugasan mengajar pada periode / kriteria pencarian ini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection