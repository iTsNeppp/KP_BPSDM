@extends('layouts.master')

@section('title', 'Pengaturan Profil')

@push('css')
<!-- Select2 CSS & Theme Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<style>
    .select2-container--bootstrap-5 .select2-selection {
        border-color: #d9dee3;
        border-radius: 0.375rem;
        min-height: 38px;
    }
    .select2-selection__clear {
        display: none !important;
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-1 mb-0"><span class="text-muted fw-light">Akun /</span> Pengaturan Profil</h4>
        
        {{-- TOMBOL BUKA MODAL JIKA ROLE ADALAH PENGAJAR --}}
        @if($user->role === 'pengajar')
            <button type="button" class="btn btn-info shadow-sm" data-bs-toggle="modal" data-bs-target="#modalEditPengajar">
                <i class="bx bx-edit me-1"></i> Edit Data Pengajar & Berkas
            </button>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm alert-dismissible" role="alert">
            <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm alert-dismissible" role="alert">
            <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ALERT ERROR VALIDASI --}}
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm alert-dismissible mb-4" role="alert">
            <h6 class="alert-heading fw-bold mb-1"><i class="bx bx-error-circle me-1"></i> Terjadi kesalahan:</h6>
            <ul class="mb-0 ps-3 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            
            <!-- ======================================================== -->
            <!-- KARTU KHUSUS PENGAJAR (DATA PROFIL, REKENING & BERKAS)   -->
            <!-- ======================================================== -->
            @if($user->role === 'pengajar')
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title text-info mb-0 fw-bold">
                        <i class="bx bx-wallet text-warning me-1"></i> Informasi Tenaga Pengajar & Rekening Keuangan
                    </h5>
                    @if($user->pengajar && $user->pengajar->nomor_rekening)
                        <span class="badge bg-label-success">Data Lengkap</span>
                    @else
                        <span class="badge bg-label-danger">Belum Lengkap</span>
                    @endif
                </div>

                <div class="card-body p-4">
                    <div class="row g-4 mb-3">
                        <!-- Kolom Kiri: Profil Kepegawaian -->
                        <div class="col-md-6 border-end">
                            <h6 class="fw-bold text-dark mb-3"><i class="bx bx-user me-1 text-primary"></i> Data Kepegawaian</h6>
                            <div class="bg-light p-3 rounded border">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="small text-muted">Jabatan Fungsional:</span>
                                    <span class="small fw-bold text-dark">{{ $user->jabatan ?? 'Tenaga Pengajar' }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="small text-muted">Pangkat / Golongan:</span>
                                    <span class="small fw-bold text-dark">{{ $user->pengajar->pangkat_golongan ?? '-' }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="small text-muted">Instansi / Unit Kerja:</span>
                                    <span class="small fw-bold text-dark">{{ $user->pengajar->instansi ?? $user->instansi ?? '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom Kanan: Data Rekening & Finansial -->
                        <div class="col-md-6">
                            <h6 class="fw-bold text-dark mb-3"><i class="bx bx-credit-card me-1 text-warning"></i> Data Rekening Honorarium</h6>
                            <div class="bg-light p-3 rounded border">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="small text-muted">Bank Asal Rekening:</span>
                                    <span class="small fw-bold text-dark">{{ $user->pengajar->nama_bank ?? '-' }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="small text-muted">Nomor Rekening:</span>
                                    <span class="small fw-bold text-primary">{{ $user->pengajar->nomor_rekening ?? '-' }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="small text-muted">Rekening Atas Nama:</span>
                                    <span class="small fw-bold text-dark">{{ $user->pengajar->rekening_atas_nama ?? $user->pengajar->nama_rekening ?? '-' }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="small text-muted">Nomor NPWP:</span>
                                    <span class="small fw-bold text-dark">{{ $user->pengajar->npwp ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status Berkas Dokumen -->
                    <div class="d-flex flex-wrap justify-content-between align-items-center pt-3 border-top gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="small fw-bold text-muted me-2">Status Berkas Terunggah:</span>
                            @php
                                $hasCv = $user->pengajar && $user->pengajar->cv_path;
                                $hasSertifikat = $user->pengajar && $user->pengajar->sertifikat_path;

                            @endphp
                            
                            @if($hasCv)
                                <a href="{{ route('pengajar.doc.view', 'cv') }}" target="_blank" class="badge bg-label-success text-decoration-none" title="Klik untuk melihat CV">
                                    <i class="bx bx-check me-1"></i> CV
                                </a>
                            @else
                                <span class="badge bg-label-secondary"><i class="bx bx-x me-1"></i> CV</span>
                            @endif

                            @if($hasSertifikat)
                                <a href="{{ route('pengajar.doc.view', 'sertifikat') }}" target="_blank" class="badge bg-label-success text-decoration-none" title="Klik untuk melihat Sertifikat">
                                    <i class="bx bx-check me-1"></i> Sertifikat
                                </a>
                            @else
                                <span class="badge bg-label-secondary"><i class="bx bx-x me-1"></i> Sertifikat</span>
                            @endif
                        </div>

                        <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#modalEditPengajar">
                            <i class="bx bx-edit-alt me-1"></i> Perbarui Data & Berkas Pengajar
                        </button>
                    </div>
                </div>
            </div>
            @endif

            <!-- ======================================================== -->
            <!-- CARD DETAIL AKUN UMUM                                    -->
            <!-- ======================================================== -->
            <div class="card mb-4 shadow-sm border-0">
                <h5 class="card-header border-bottom">Detail Akun</h5>
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="card-body">
                        <div class="d-flex align-items-start align-items-sm-center gap-4">
                            @if($user->profile_photo)
                                <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="user-avatar" class="d-block rounded shadow" height="100" width="100" id="uploadedAvatar" style="object-fit: cover;" />
                            @else
                                <div class="avatar avatar-xl me-2">
                                    <span class="avatar-initial rounded bg-label-primary" style="font-size: 40px;">{{ substr($user->name, 0, 1) }}</span>
                                </div>
                            @endif

                            <div class="button-wrapper">
                                <label for="upload" class="btn btn-primary me-2 mb-4" tabindex="0">
                                    <span class="d-none d-sm-block text-uppercase small fw-bold">Ganti Foto</span>
                                    <i class="bx bx-upload d-block d-sm-none"></i>
                                    <input type="file" id="upload" name="profile_photo" class="account-file-input" hidden accept="image/png, image/jpeg" />
                                </label>
                                <p class="text-muted mb-0 small">Hanya JPG atau PNG. Maksimal 2MB.</p>
                            </div>
                        </div>
                    </div>
                    <hr class="my-0" />
                    <div class="card-body">
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold">Nama Lengkap</label>
                                <input class="form-control" type="text" name="name" value="{{ $user->name }}" required />
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold">WhatsApp</label>
                                <input class="form-control" type="text" name="whatsapp" value="{{ $user->whatsapp }}" required />
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold text-muted">Username (Login)</label>
                                <input class="form-control bg-light" type="text" value="{{ $user->username }}" disabled />
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold text-muted">Role</label>
                                <input class="form-control bg-light" type="text" value="{{ strtoupper(str_replace('_', ' ', $user->role)) }}" disabled />
                            </div>
                        </div>
                        <div class="mt-2">
                            <button type="submit" class="btn btn-primary">Simpan Perubahan Akun</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- ======================================================== -->
            <!-- CARD GANTI PASSWORD                                      -->
            <!-- ======================================================== -->
            <div class="card shadow-sm border-0">
                <h5 class="card-header border-bottom mb-3">Keamanan Akun</h5>
                <div class="card-body">
                    <div class="alert alert-warning mb-4">
                        <i class="bx bx-info-circle me-1"></i>
                        Jika Anda ingin mengganti password, silakan masukkan password baru di bawah ini.
                    </div>
                    
                    <form action="{{ route('profile.password') }}" method="POST">
                        @csrf @method('PUT')
                        <div class="row">
                            <div class="mb-3 col-md-6 form-password-toggle">
                                <label class="form-label fw-bold">Password Baru</label>
                                <div class="input-group input-group-merge">
                                    <input class="form-control" type="password" name="new_password" placeholder="••••••••" required />
                                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                                </div>
                            </div>
                            <div class="mb-3 col-md-6 form-password-toggle">
                                <label class="form-label fw-bold">Konfirmasi Password Baru</label>
                                <div class="input-group input-group-merge">
                                    <input class="form-control" type="password" name="new_password_confirmation" placeholder="••••••••" required />
                                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <button type="submit" class="btn btn-warning fw-bold shadow-sm">
                                <i class="bx bx-lock-open-alt me-1"></i> PERBARUI PASSWORD
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL KHUSUS EDIT PENGAJAR (PROFIL, REKENING, & DOKUMEN)                 -->
<!-- ========================================================================= -->
@if($user->role === 'pengajar')
<div class="modal fade" id="modalEditPengajar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf @method('PUT')
            
            <input type="hidden" name="name" value="{{ $user->name }}">
            <input type="hidden" name="whatsapp" value="{{ $user->whatsapp }}">

            <div class="modal-header bg-info">
                <h5 class="modal-title text-white"><i class="bx bx-user-check me-1"></i> Edit Data Pengajar, Rekening & Berkas</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                
                <!-- 1. DATA KEPEGAWAIAN -->
                <h6 class="fw-bold text-info border-bottom pb-2 mb-3">
                    <i class="bx bx-id-card me-1"></i> 1. Profil & Kepegawaian
                </h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">NIP / NIK <span class="text-danger">*</span></label>
                        <input type="text" name="nip_nik" class="form-control" value="{{ old('nip_nik', $user->nip_nik) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Jabatan Fungsional <span class="text-danger">*</span></label>
                        <select name="jabatan" class="form-select" required>
                            <option value="">-- Pilih Jabatan Fungsional --</option>
                            <option value="Widyaiswara Ahli Pertama" {{ old('jabatan', $user->jabatan) == 'Widyaiswara Ahli Pertama' ? 'selected' : '' }}>
                                Widyaiswara Ahli Pertama
                            </option>
                            <option value="Widyaiswara Ahli Muda" {{ old('jabatan', $user->jabatan) == 'Widyaiswara Ahli Muda' ? 'selected' : '' }}>
                                Widyaiswara Ahli Muda
                            </option>
                            <option value="Widyaiswara Ahli Madya" {{ old('jabatan', $user->jabatan) == 'Widyaiswara Ahli Madya' ? 'selected' : '' }}>
                                Widyaiswara Ahli Madya
                            </option>
                            <option value="Widyaiswara Ahli Utama" {{ old('jabatan', $user->jabatan) == 'Widyaiswara Ahli Utama' ? 'selected' : '' }}>
                                Widyaiswara Ahli Utama
                            </option>
                        </select>
                    </div>

                    <!-- DROPDOWN PANGKAT / GOLONGAN MODAL (SELECT2 SEARCHABLE) -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Pangkat / Golongan <span class="text-danger">*</span></label>
                        <select name="pangkat_golongan" id="select_pangkat_golongan_modal" class="form-select" required>
                            <option value="{{ $user->pengajar->pangkat_golongan ?? '' }}" selected>
                                {{ $user->pengajar->pangkat_golongan ?? '-- Sedang Memuat Pangkat / Golongan... --' }}
                            </option>
                        </select>
                    </div>

                    <!-- DROPDOWN INSTANSI PEMERINTAHAN MODAL (SELECT2 SEARCHABLE) -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Instansi / Unit Kerja <span class="text-danger">*</span></label>
                        <select name="instansi" id="select_instansi_modal" class="form-select" required>
                            <option value="{{ $user->pengajar->instansi ?? $user->instansi ?? '' }}" selected>
                                {{ $user->pengajar->instansi ?? $user->instansi ?? '-- Pilih / Ketik Instansi --' }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- 2. INFORMASI REKENING -->
                <h6 class="fw-bold text-info border-bottom pb-2 mb-3">
                    <i class="bx bx-wallet me-1"></i> 2. Informasi Rekening & Honor
                </h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nomor NPWP</label>
                        <input type="text" name="npwp" class="form-control" value="{{ old('npwp', $user->pengajar->npwp ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nomor Rekening <span class="text-danger">*</span></label>
                        <input type="number" name="nomor_rekening" class="form-control" value="{{ old('nomor_rekening', $user->pengajar->nomor_rekening ?? '') }}" required>
                    </div>

                    <!-- DROPDOWN BANK MODAL (SELECT2 SEARCHABLE) -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Bank Asal Rekening <span class="text-danger">*</span></label>
                        <select name="nama_bank" id="select_bank_modal" class="form-select" required>
                            <option value="{{ $user->pengajar->nama_bank ?? '' }}" selected>
                                {{ $user->pengajar->nama_bank ?? '-- Pilih Bank --' }}
                            </option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Rekening Atas Nama <span class="text-danger">*</span></label>
                        <input type="text" name="rekening_atas_nama" class="form-control" value="{{ old('rekening_atas_nama', $user->pengajar->rekening_atas_nama ?? $user->pengajar->nama_rekening ?? '') }}" required>
                    </div>
                </div>

                <!-- 3. KELENGKAPAN DOKUMEN -->
                <h6 class="fw-bold text-info border-bottom pb-2 mb-3">
                    <i class="bx bx-file me-1"></i> 3. Berkas & Dokumen Pengajar (PDF Max 5MB)
                </h6>
                <div class="row g-3">
                    <!-- CV -->
                    <div class="col-md-12">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold mb-0">Curriculum Vitae (CV)</label>
                            @if($user->pengajar && $user->pengajar->cv_path)
                                <a href="{{ route('pengajar.doc.view', 'cv') }}" target="_blank" class="badge bg-label-primary text-decoration-none">
                                    <i class="bx bx-show me-1"></i> Lihat CV Saat Ini
                                </a>
                            @endif
                        </div>
                        <input type="file" name="file_cv" class="form-control" accept=".pdf">
                        <div class="form-text small">Kosongkan jika tidak ingin mengubah CV.</div>
                    </div>

                    <!-- Sertifikat -->
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold mb-0">Sertifikat ToT / Kompetensi</label>
                            @if($user->pengajar && $user->pengajar->sertifikat_path)
                                <a href="{{ route('pengajar.doc.view', 'sertifikat') }}" target="_blank" class="badge bg-label-success text-decoration-none">
                                    <i class="bx bx-show me-1"></i> Lihat Sertifikat
                                </a>
                            @endif
                        </div>
                        <input type="file" name="file_sertifikat" class="form-control" accept=".pdf">
                        <div class="form-text small">Format PDF maksimal 5MB.</div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-info text-white shadow-sm">
                    <i class="bx bx-save me-1"></i> Simpan Perubahan Data Pengajar
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@push('js')
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    const $modal = $('#modalEditPengajar');

    // ========================================================
    // 1. DATA PANGKAT / GOLONGAN (PNS & PPPK)
    // ========================================================
    const $pangkatSelect = $('#select_pangkat_golongan_modal');
    const selectedPangkat = "{{ old('pangkat_golongan', $user->pengajar->pangkat_golongan ?? '') }}";

    const masterPangkat = [
        { "label": "Juru Muda (I/a)", "kategori": "PNS" },
        { "label": "Juru Muda Tingkat I (I/b)", "kategori": "PNS" },
        { "label": "Juru (I/c)", "kategori": "PNS" },
        { "label": "Juru Tingkat I (I/d)", "kategori": "PNS" },
        { "label": "Pengatur Muda (II/a)", "kategori": "PNS" },
        { "label": "Pengatur Muda Tingkat I (II/b)", "kategori": "PNS" },
        { "label": "Pengatur (II/c)", "kategori": "PNS" },
        { "label": "Pengatur Tingkat I (II/d)", "kategori": "PNS" },
        { "label": "Penata Muda (III/a)", "kategori": "PNS" },
        { "label": "Penata Muda Tingkat I (III/b)", "kategori": "PNS" },
        { "label": "Penata (III/c)", "kategori": "PNS" },
        { "label": "Penata Tingkat I (III/d)", "kategori": "PNS" },
        { "label": "Pembina (IV/a)", "kategori": "PNS" },
        { "label": "Pembina Tingkat I (IV/b)", "kategori": "PNS" },
        { "label": "Pembina Utama Muda (IV/c)", "kategori": "PNS" },
        { "label": "Pembina Utama Madya (IV/d)", "kategori": "PNS" },
        { "label": "Pembina Utama (IV/e)", "kategori": "PNS" },
        { "label": "Golongan VII - PPPK (Setara III/a)", "kategori": "PPPK" },
        { "label": "Golongan IX - PPPK (Setara III/a)", "kategori": "PPPK" },
        { "label": "Golongan X - PPPK (Setara III/b)", "kategori": "PPPK" },
        { "label": "Golongan XI - PPPK (Setara III/c)", "kategori": "PPPK" },
        { "label": "Golongan XIII - PPPK (Setara IV/a)", "kategori": "PPPK" }
    ];

    let pangkatOptions = '<option value="">-- Pilih / Cari Pangkat & Golongan --</option>';
    const pnsList = masterPangkat.filter(item => item.kategori === 'PNS');
    const pppkList = masterPangkat.filter(item => item.kategori === 'PPPK');

    pangkatOptions += '<optgroup label="PNS (Pegawai Negeri Sipil)">';
    pnsList.forEach(item => {
        const isSelected = (selectedPangkat === item.label) ? 'selected' : '';
        pangkatOptions += `<option value="${item.label}" ${isSelected}>${item.label}</option>`;
    });
    pangkatOptions += '</optgroup>';

    pangkatOptions += '<optgroup label="PPPK (Pegawai Pemerintah dengan Perjanjian Kerja)">';
    pppkList.forEach(item => {
        const isSelected = (selectedPangkat === item.label) ? 'selected' : '';
        pangkatOptions += `<option value="${item.label}" ${isSelected}>${item.label}</option>`;
    });
    pangkatOptions += '</optgroup>';

    $pangkatSelect.html(pangkatOptions).select2({
        theme: 'bootstrap-5',
        dropdownParent: $modal,
        placeholder: '-- Ketik untuk mencari Golongan / Pangkat --',
        allowClear: false,
        width: '100%'
    });

    // ========================================================
    // 2. DATA INSTANSI PEMERINTAHAN (SEARCHABLE + TAGS)
    // ========================================================
    const $instansiSelect = $('#select_instansi_modal');
    const selectedInstansi = "{{ old('instansi', $user->pengajar->instansi ?? $user->instansi ?? '') }}";

    const masterInstansi = [
        "BPSDM Provinsi Jawa Barat",
        "Pemerintah Daerah Provinsi Jawa Barat",
        "Badan Kepegawaian Daerah Provinsi Jawa Barat",
        "Kementerian Dalam Negeri (Kemendagri)",
        "Kementerian Pendayagunaan Aparatur Negara dan Reformasi Birokrasi (KemenPAN-RB)",
        "Badan Kepegawaian Negara (BKN)",
        "Lembaga Administrasi Negara (LAN)",
        "Badan Pengawasan Keuangan dan Pembangunan (BPKP)",
        "Badan Pusat Statistik (BPS)",
        "Kementerian Pendidikan, Kebudayaan, Riset, dan Teknologi",
        "Kementerian Keuangan",
        "Kementerian Kesehatan",
        "Kementerian Agama",
        "Kementerian Hukum dan HAM",
        "Kementerian Komunikasi dan Informatika",
        "Kementerian Perhubungan",
        "Pemerintah Kota Bandung",
        "Pemerintah Kabupaten Bandung",
        "Pemerintah Kabupaten Bandung Barat",
        "Pemerintah Kota Cimahi",
        "Pemerintah Kabupaten Sumedang",
        "Pemerintah Kota Bogor",
        "Pemerintah Kabupaten Bogor",
        "Pemerintah Kota Bekasi",
        "Pemerintah Kabupaten Bekasi",
        "Pemerintah Kabupaten Cirebon",
        "Pemerintah Kota Cirebon",
        "Pemerintah Kabupaten Garut",
        "Pemerintah Kabupaten Tasikmalaya",
        "Pemerintah Kota Tasikmalaya",
        "Pemerintah Kabupaten Sukabumi",
        "Pemerintah Kota Sukabumi",
        "Pemerintah Kabupaten Cianjur",
        "Pemerintah Kabupaten Karawang",
        "Pemerintah Kabupaten Purwakarta",
        "Pemerintah Kabupaten Subang",
        "Pemerintah Kabupaten Indramayu",
        "Pemerintah Kabupaten Majalengka",
        "Pemerintah Kabupaten Kuningan",
        "Pemerintah Kabupaten Ciamis",
        "Pemerintah Kota Banjar",
        "Pemerintah Kabupaten Pangandaran"
    ];

    let instansiOptions = '<option value="">-- Pilih / Ketik Instansi Pemerintahan --</option>';
    if (selectedInstansi && !masterInstansi.includes(selectedInstansi)) {
        masterInstansi.unshift(selectedInstansi);
    }

    masterInstansi.forEach(name => {
        const isSelected = (selectedInstansi && selectedInstansi.toLowerCase() === name.toLowerCase()) ? 'selected' : '';
        instansiOptions += `<option value="${name}" ${isSelected}>${name}</option>`;
    });

    $instansiSelect.html(instansiOptions).select2({
        theme: 'bootstrap-5',
        dropdownParent: $modal,
        placeholder: '-- Ketik / Cari Instansi Pemerintahan --',
        allowClear: false,
        tags: true,
        width: '100%'
    });

    // ========================================================
    // 3. DATA BANK ASAL REKENING (SEARCHABLE)
    // ========================================================
    const $bankSelect = $('#select_bank_modal');
    const selectedBank = "{{ old('nama_bank', $user->pengajar->nama_bank ?? '') }}";

    const masterBank = [
        "BANK BJB", "BANK BJB SYARIAH", "BANK BRI", "BANK MANDIRI", "BANK BNI", "BANK BCA", 
        "BANK SYARIAH INDONESIA (BSI)", "BANK CIMB NIAGA", "BANK PERMATA", "BANK DANAMON", 
        "BANK TABUNGAN NEGARA (BTN)", "BANK PANIN", "BANK MEGA", "BANK DKI", "BANK JATENG", 
        "BANK JATIM", "BANK BUKOPIN", "BANK BTPN", "BANK OCBC NISP", "BANK MAYBANK INDONESIA"
    ];

    let bankOptions = '<option value="">-- Pilih / Cari Bank Asal Rekening --</option>';
    masterBank.forEach(name => {
        const isSelected = (selectedBank && selectedBank.toUpperCase() === name.toUpperCase()) ? 'selected' : '';
        bankOptions += `<option value="${name}" ${isSelected}>${name}</option>`;
    });

    $bankSelect.html(bankOptions).select2({
        theme: 'bootstrap-5',
        dropdownParent: $modal,
        placeholder: '-- Ketik untuk mencari Bank --',
        allowClear: false,
        tags: true,
        width: '100%'
    });
});
</script>
@endpush