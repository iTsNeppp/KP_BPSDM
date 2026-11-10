@extends('layouts.master')

@section('title', 'Jadwal Mengajar Saya')

@push('css')
<style>
    /* Styling Card Pelatihan Sesuai Template */
    .card-training {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #fff;
        transition: all 0.25s ease-in-out;
    }
    .card-training:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08) !important;
        border-color: #cbd5e1;
    }

    /* Badge Bidang Bagian Atas */
    .badge-bidang {
        background-color: #f1f5f9;
        color: #64748b;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        padding: 8px 12px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        line-height: 1.2;
    }

    /* Kotak Info Durasi & Angkatan */
    .box-info-training {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background-color: #ffffff;
        padding: 10px 0;
    }

    /* Tombol Aksi Seragam */
    .btn-action-training {
        background-color: #f1f5f9;
        color: #64748b;
        border: 1px solid #e2e8f0;
        font-weight: 700;
        font-size: 0.75rem;
        letter-spacing: 0.3px;
        border-radius: 8px;
        transition: all 0.2s ease;
    }
    .btn-action-training:hover {
        background-color: #e2e8f0;
        color: #334155;
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <!-- HEADER HALAMAN & PENCARIAN -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold py-1 mb-1">
                <span class="text-muted fw-light">Portal Pengajar /</span> Jadwal Mengajar Saya
            </h4>
            <p class="text-muted small mb-0">
                Daftar pelatihan, jadwal mengajar, surat tugas, dan akses ruang virtual meeting Anda.
            </p>
        </div>

        <!-- Form Pencarian -->
        <form action="{{ url()->current() }}" method="GET" class="d-flex align-items-center">
            <div class="input-group input-group-merge shadow-sm" style="min-width: 280px;">
                <span class="input-group-text bg-white border border-end-0 text-muted">
                    <i class="bx bx-search fs-5"></i>
                </span>
                <input 
                    type="text" 
                    name="search" 
                    class="form-control border border-start-0 shadow-none ps-0" 
                    placeholder="Cari Pelatihan atau Bidang..." 
                    value="{{ request('search') }}"
                />
            </div>
            @if(request('search'))
                <a href="{{ url()->current() }}" class="btn btn-outline-secondary btn-sm ms-2">Reset</a>
            @endif
        </form>
    </div>

    <!-- Alert Notifikasi -->
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm alert-dismissible mb-4" role="alert">
            <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm alert-dismissible mb-4" role="alert">
            <i class="bx bx-error-circle me-1"></i> <strong>Gagal:</strong> {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- GRID KARTU PELATIHAN -->
    <div class="row g-4">
        @forelse($myTrainings as $t)
        @php
            // 1. Ambil sesi dengan Link Zoom
            $zoomSchedules = $t->schedules->whereNotNull('link_zoom')->filter(function($s) {
                return !empty(trim($s->link_zoom));
            });

            // 2. Pengecekan Surat Tugas
            $scheduleWithSurat = $t->schedules->where('pengajar_id', auth()->id())->whereNotNull('surat_tugas')->first();
            $suratTugasPath = $scheduleWithSurat ? $scheduleWithSurat->surat_tugas : (auth()->user()->pengajar->surat_tugas_path ?? null);
            
            $hasSuratTugas = !empty($suratTugasPath);
        @endphp

        <div class="col-12 col-md-6 col-lg-4">
            <div class="card card-training h-100 shadow-sm">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    
                    <div>
                        <!-- 1. Header Bidang -->
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="badge-bidang text-uppercase">
                                <i class="bx bx-buildings fs-6 me-1 flex-shrink-0"></i>
                                <span class="text-truncate" style="max-width: 240px;" title="{{ $t->bidang }}">
                                    {{ $t->bidang }}
                                </span>
                            </div>
                            @if(now() > $t->tgl_selesai)
                                <span class="badge rounded-pill bg-light text-secondary border p-1" title="Pelatihan Selesai">
                                    <i class="bx bx-check"></i>
                                </span>
                            @endif
                        </div>

                        <!-- 2. Judul Pelatihan -->
                        <h5 class="fw-bold text-dark mb-3 text-truncate" title="{{ $t->nama_pelatihan }}">
                            {{ $t->nama_pelatihan }}
                        </h5>

                        <!-- 3. Box Info Durasi JP & Angkatan -->
                        <div class="box-info-training mb-3">
                            <div class="row text-center g-0 align-items-center">
                                <div class="col-6 border-end">
                                    <small class="text-muted text-uppercase d-block fw-semibold" style="font-size: 0.65rem; letter-spacing: 0.5px;">DURASI</small>
                                    <span class="fw-bold fs-6 text-dark">
                                        {{ abs((int)($t->schedules->sum('jp') ?: $t->jp)) }} JP
                                    </span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted text-uppercase d-block fw-semibold" style="font-size: 0.65rem; letter-spacing: 0.5px;">ANGKATAN</small>
                                    <span class="fw-bold fs-6 text-dark">{{ $t->angkatan ?? '1' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- 4. Tanggal Pelaksanaan -->
                        <div class="mb-3">
                            <small class="text-muted d-block mb-1">
                                <i class="bx bx-calendar me-1"></i> Jadwal:
                            </small>
                            <span class="small fw-semibold {{ now() > $t->tgl_selesai ? 'text-muted text-decoration-line-through' : 'text-dark' }}">
                                {{ \Carbon\Carbon::parse($t->tgl_mulai)->format('d M') }} - {{ \Carbon\Carbon::parse($t->tgl_selesai)->format('d M Y') }}
                            </span>
                        </div>

                        <!-- 5. Rincian Sesi Mengajar & Indikator Status Surat Tugas -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span>Sesi Mengajar Anda</span>
                                <span class="fw-bold text-dark">{{ $t->schedules->count() }} Sesi Ditugaskan</span>
                            </div>
                            <div class="progress mb-2" style="height: 6px;">
                                <div class="progress-bar bg-info" role="progressbar" style="width: 100%"></div>
                            </div>

                            <!-- INDIKATOR STATUS SURAT TUGAS DI KARTU -->
                            <div class="d-flex justify-content-between align-items-center p-2 rounded {{ $hasSuratTugas ? 'bg-label-success' : 'bg-label-warning' }}">
                                <small class="fw-semibold {{ $hasSuratTugas ? 'text-success' : 'text-warning' }}">
                                    <i class="bx {{ $hasSuratTugas ? 'bx-check-circle' : 'bx-time-five' }} me-1"></i>
                                    {{ $hasSuratTugas ? 'Surat Tugas: Terunggah' : 'Surat Tugas: Belum Diunggah' }}
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- 6. Tombol Aksi -->
                    <div>
                        <div class="row g-2 mb-3">
                            <!-- TOMBOL UPLOAD / EDIT SURAT TUGAS (UKURAN BOX SERAGAM) -->
                            <div class="col-6">
                                <button type="button" class="btn btn-action-training w-100 py-2 d-flex align-items-center justify-content-center shadow-none" data-bs-toggle="modal" data-bs-target="#modalUploadSuratTugas{{ $t->id }}">
                                    <i class="bx {{ $hasSuratTugas ? 'bx-edit text-info' : 'bx-upload text-primary' }} me-1"></i> 
                                    {{ $hasSuratTugas ? 'EDIT SURAT' : 'SURAT TUGAS' }}
                                </button>
                            </div>

                            <!-- Tombol Link Zoom Meeting -->
                            <div class="col-6">
                                @if($zoomSchedules->count() === 1)
                                    <a href="{{ $zoomSchedules->first()->link_zoom }}" target="_blank" class="btn btn-success w-100 py-2 fw-bold text-decoration-none shadow-sm d-flex align-items-center justify-content-center" style="font-size: 0.75rem;">
                                        <i class="bx bx-video me-1"></i> BUKA ZOOM
                                    </a>
                                @elseif($zoomSchedules->count() > 1)
                                    <button type="button" class="btn btn-success w-100 py-2 fw-bold shadow-sm d-flex align-items-center justify-content-center" style="font-size: 0.75rem;" data-bs-toggle="modal" data-bs-target="#modalZoom{{ $t->id }}">
                                        <i class="bx bx-video me-1"></i> PILIH ZOOM ({{ $zoomSchedules->count() }})
                                    </button>
                                @else
                                    <button class="btn btn-light border text-muted w-100 py-2 d-flex align-items-center justify-content-center" disabled style="font-size: 0.75rem;">
                                        <i class="bx bx-video-off me-1"></i> TATAP MUKA
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Footer Badge (Model & Status) -->
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <span class="badge bg-label-secondary text-uppercase fw-bold" style="font-size: 0.7rem;">
                                {{ $t->model ?? 'STANDAR' }}
                            </span>

                            @if(now() < $t->tgl_mulai)
                                <span class="badge bg-label-info fw-bold" style="font-size: 0.7rem;">MENDATANG</span>
                            @elseif(now() >= $t->tgl_mulai && now() <= $t->tgl_selesai)
                                <span class="badge bg-label-success fw-bold" style="font-size: 0.7rem;">SEDANG BERJALAN</span>
                            @else
                                <span class="badge bg-secondary text-white fw-bold" style="font-size: 0.7rem;">SELESAI</span>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- ==============================================
             MODAL UPLOAD / EDIT SURAT TUGAS PENGAJAR
             ============================================== -->
        <div class="modal fade" id="modalUploadSuratTugas{{ $t->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ route('pengajar.surat_tugas.upload', $t->id) }}" method="POST" enctype="multipart/form-data" class="modal-content">
                    @csrf
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title text-white">
                            <i class="bx bx-upload me-1"></i> {{ $hasSuratTugas ? 'Edit' : 'Unggah' }} Surat Tugas Mengajar
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label text-muted d-block small mb-1">Pelatihan:</label>
                            <h6 class="fw-bold text-dark mb-0">{{ $t->nama_pelatihan }}</h6>
                        </div>

                        {{-- TAMPILKAN JIKA FILE SUDAH PERNAH DIUNGGAH --}}
                        @if($hasSuratTugas)
                            <div class="mb-3 p-3 bg-light rounded border d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-success fw-bold d-block"><i class="bx bx-check-circle me-1"></i> Berkas Telah Terunggah</span>
                                    <small class="text-muted">Klik tombol di samping untuk mengecek isi file.</small>
                                </div>
                                <a href="{{ route('pengajar.surat_tugas.view', $t->id) }}" target="_blank" class="btn btn-sm btn-primary shadow-sm">
                                    <i class="bx bx-show me-1"></i> Lihat PDF
                                </a>
                            </div>
                        @endif

                        <div class="alert alert-info py-2 small mb-3">
                            <i class="bx bx-info-circle me-1"></i> Format berkas wajib berformat <strong>.PDF</strong> dengan ukuran maksimal <strong>5 MB</strong>.
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                {{ $hasSuratTugas ? 'Pilih Berkas PDF Baru (Untuk Mengganti)' : 'Pilih Berkas Surat Tugas (PDF)' }} 
                                <span class="text-danger">*</span>
                            </label>
                            <input type="file" name="surat_tugas" class="form-control" accept=".pdf" required>
                        </div>
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-upload me-1"></i> {{ $hasSuratTugas ? 'Simpan Perubahan' : 'Unggah Surat Tugas' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==============================================
             MODAL PILIH SESI ZOOM
             ============================================== -->
        @if($zoomSchedules->count() > 1)
        <div class="modal fade" id="modalZoom{{ $t->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-success">
                        <h5 class="modal-title text-white">
                            <i class="bx bx-video me-1"></i> Akses Zoom - {{ $t->nama_pelatihan }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-3">
                        <p class="small text-muted mb-3">
                            Terdapat beberapa sesi materi dengan link virtual meeting. Silakan klik sesi yang ingin Anda masuki:
                        </p>
                        <div class="list-group">
                            @foreach($zoomSchedules as $zs)
                            <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-3 mb-2 border rounded shadow-none">
                                <div class="pe-2">
                                    <h6 class="mb-1 fw-bold text-dark">{{ $zs->activity }}</h6>
                                    <small class="text-muted d-block">
                                        <i class="bx bx-calendar me-1"></i>
                                        {{ \Carbon\Carbon::parse($zs->date)->translatedFormat('d M Y') }} 
                                        ({{ $zs->start_time }} - {{ $zs->end_time }} WIB)
                                    </small>
                                </div>
                                <a href="{{ $zs->link_zoom }}" target="_blank" class="btn btn-sm btn-success flex-shrink-0 shadow-sm">
                                    <i class="bx bx-video me-1"></i> Masuk Zoom
                                </a>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer border-top py-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @empty
        <div class="col-12 text-center py-5">
            <div class="card border-0 shadow-sm p-5">
                <i class="bx bx-calendar-x fs-1 d-block mb-3 text-secondary"></i>
                <h5 class="fw-bold mb-1">Belum Ada Pelatihan Ditugaskan</h5>
                <p class="text-muted small mb-0">Anda belum memiliki jadwal mengajar pada pelatihan manapun saat ini.</p>
            </div>
        </div>
        @endforelse
    </div>

</div>
@endsection