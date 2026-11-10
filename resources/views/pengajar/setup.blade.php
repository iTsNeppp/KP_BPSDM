@extends('layouts.form')

{{-- Konfigurasi Header & Form --}}
@section('form_title', 'Setup Profil Pengajar')
@section('module_name', 'Tenaga Pengajar')
@section('page_title', 'Lengkapi Profil & Keamanan Akun')
@section('page_description', 'Silakan perbarui password default dan lengkapi data profil, rekening honor, serta dokumen pengajar Anda.')
@section('form_action', route('pengajar.setup.store'))
@section('form_enctype', 'multipart/form-data') {{-- Wajib untuk upload file --}}
@section('submit_text', 'Simpan Profil & Masuk Dashboard')

{{-- ISI KONTEN INPUT FORM --}}
@section('form_content')

    <!-- KARTU 1: KEAMANAN AKUN -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="form-section-title">
                <i class="bx bx-lock-alt fs-4 me-2"></i> 1. Pembaruan Password
            </div>
            <div class="alert alert-warning py-2 small mb-4">
                <i class="bx bx-info-circle me-1"></i> Anda login menggunakan password default dari Superadmin. Wajib ubah password demi keamanan akun Anda.
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Password Baru <span class="required-star">*</span></label>
                    <input type="password" name="new_password" class="form-control" placeholder="Minimal 6 karakter" required minlength="6">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Ulangi Password Baru <span class="required-star">*</span></label>
                    <input type="password" name="new_password_confirmation" class="form-control" placeholder="Ulangi password baru" required minlength="6">
                </div>
            </div>
        </div>
    </div>

    <!-- KARTU 2: PROFIL UTAMA -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="form-section-title">
                <i class="bx bx-user fs-4 me-2"></i> 2. Profil Utama Kepegawaian
            </div>
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Nama Lengkap (Beserta Gelar) <span class="required-star">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', auth()->user()->name) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">NIP / NIK <span class="required-star">*</span></label>
                    <input type="text" name="nip_nik" class="form-control" value="{{ old('nip_nik', auth()->user()->nip_nik) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Jabatan Fungsional <span class="required-star">*</span></label>
                    <select name="jabatan" class="form-select" required>
                        <option value="">-- Pilih Jabatan Fungsional --</option>
                        <option value="Widyaiswara Ahli Pertama" {{ old('jabatan', auth()->user()->jabatan) == 'Widyaiswara Ahli Pertama' ? 'selected' : '' }}>
                            Widyaiswara Ahli Pertama
                        </option>
                        <option value="Widyaiswara Ahli Muda" {{ old('jabatan', auth()->user()->jabatan) == 'Widyaiswara Ahli Muda' ? 'selected' : '' }}>
                            Widyaiswara Ahli Muda
                        </option>
                        <option value="Widyaiswara Ahli Madya" {{ old('jabatan', auth()->user()->jabatan) == 'Widyaiswara Ahli Madya' ? 'selected' : '' }}>
                            Widyaiswara Ahli Madya
                        </option>
                        <option value="Widyaiswara Ahli Utama" {{ old('jabatan', auth()->user()->jabatan) == 'Widyaiswara Ahli Utama' ? 'selected' : '' }}>
                            Widyaiswara Ahli Utama
                        </option>
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                     <label class="form-label fw-bold">Pangkat / Golongan <span class="required-star">*</span></label>
                        <select name="pangkat_golongan" id="select_pangkat_golongan" class="form-select select2" required>
                            <option value="">-- Sedang Memuat Pangkat / Golongan... --</option>
                        </select>
                </div>

                <!-- DROPDOWN INSTANSI PEMERINTAHAN (SEARCHABLE) -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Instansi / Unit Kerja <span class="required-star">*</span></label>
                    <select name="instansi" id="select_instansi" class="form-select select2" required>
                        <option value="">-- Pilih / Ketik Instansi --</option>
                    </select>
                    <div class="form-text small">Ketik nama kementerian/dinas untuk mencari, atau ketik langsung jika tidak ada di daftar.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- KARTU 3: PROFILE KEUANGAN -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="form-section-title">
                <i class="bx bx-wallet fs-4 me-2"></i> 3. Profile Keuangan <small class="text-muted text-lowercase fw-normal">(Untuk keperluan pembayaran honor)</small>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nomor NPWP</label>
                    <input type="text" name="npwp" class="form-control" placeholder="Masukkan nomor NPWP" value="{{ old('npwp') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nomor Rekening <span class="required-star">*</span></label>
                    <input type="number" name="nomor_rekening" class="form-control" placeholder="Contoh: 1234567890" value="{{ old('nomor_rekening') }}" required>
                </div>

                <!-- DROPDOWN BANK (SEARCHABLE) -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Bank Asal Rekening <span class="required-star">*</span></label>
                    <select name="nama_bank" id="nama_bank" class="form-select select2" required>
                        <option value="">-- Pilih / Ketik Bank --</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Rekening Atas Nama <span class="required-star">*</span></label>
                    <input type="text" name="nama_rekening" class="form-control" placeholder="Sesuai buku tabungan" value="{{ old('nama_rekening') }}" required>
                </div>
            </div>
        </div>
    </div>

    <!-- KARTU 4: DOKUMEN ADMINISTRASI PENGAJAR -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="form-section-title">
                <i class="bx bx-file fs-4 me-2"></i> 4. Dokumen & Berkas Pengajar
            </div>
            <div class="alert alert-info py-2 small mb-4">
                <i class="bx bx-info-circle me-1"></i> Semua berkas wajib dalam format <strong>.PDF</strong> dengan ukuran maksimal <strong>5 MB</strong> per file.
            </div>

            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Curriculum Vitae (CV) Pengajar</label>
                    <input type="file" name="file_cv" class="form-control" accept=".pdf">
                    <div class="form-text small">Unggah CV terbaru Anda dalam format PDF.</div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Sertifikat ToT / Sertifikat Kompetensi</label>
                    <input type="file" name="file_sertifikat" class="form-control" accept=".pdf">
                    <div class="form-text small">Sertifikat Training of Trainer atau bukti keahlian mengajar.</div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('form_js')
<script>
$(document).ready(function() {
    const oldInstansi = "{{ old('instansi', auth()->user()->instansi ?? '') }}";
    const oldBank = "{{ old('nama_bank') }}";

    // 1. DAFTAR MASTER INSTANSI UTAMA (Langsung terpasang seketika, no delay/no loading)
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

    // Inisialisasi awal dropdown Instansi
    function renderInstansi(list) {
        let html = '<option value="">-- Pilih / Ketik Instansi Pemerintahan --</option>';
        
        // Sisipkan nilai lama jika ada
        if (oldInstansi && !list.includes(oldInstansi)) {
            list.unshift(oldInstansi);
        }

        list.forEach(name => {
            let selected = (oldInstansi && oldInstansi.toLowerCase() === name.toLowerCase()) ? 'selected' : '';
            html += `<option value="${name}" ${selected}>${name}</option>`;
        });

        $('#select_instansi').html(html).select2({
            theme: 'bootstrap-5',
            placeholder: '-- Ketik / Cari Instansi Pemerintahan --',
            tags: true, // Memungkinkan pengajar mengetik nama dinas/instansi sendiri
            width: '100%'
        });
    }

    // Render instansi seketika (tidak menunggu loading API yang lama)
    renderInstansi(masterInstansi);

    // 2. DAFTAR MASTER BANK INDONESIA (Langsung terpasang instan)
    const masterBank = [
        "BANK BJB", "BANK BJB SYARIAH", "BANK BRI", "BANK MANDIRI", "BANK BNI", "BANK BCA", 
        "BANK SYARIAH INDONESIA (BSI)", "BANK CIMB NIAGA", "BANK PERMATA", "BANK DANAMON", 
        "BANK TABUNGAN NEGARA (BTN)", "BANK PANIN", "BANK MEGA", "BANK DKI", "BANK JATENG", 
        "BANK JATIM", "BANK BUKOPIN", "BANK BTPN", "BANK OCBC NISP", "BANK MAYBANK INDONESIA"
    ];

    let bankHtml = '<option value="">-- Pilih / Cari Bank Asal Rekening --</option>';
    masterBank.forEach(name => {
        let selected = (oldBank && oldBank.toUpperCase() === name.toUpperCase()) ? 'selected' : '';
        bankHtml += `<option value="${name}" ${selected}>${name}</option>`;
    });

    $('#nama_bank').html(bankHtml).select2({
        theme: 'bootstrap-5',
        placeholder: '-- Ketik untuk mencari Bank --',
        tags: true,
        width: '100%'
    });
});
document.addEventListener('DOMContentLoaded', function() {
    const $pangkatSelect = $('#select_pangkat_golongan');
    const selectedPangkat = "{{ old('pangkat_golongan', auth()->user()->pengajar->pangkat_golongan ?? '') }}";

    // Ambil data JSON lokal aplikasi
    fetch("{{ asset('data/pangkat_golongan.json') }}")
        .then(response => response.json())
        .then(data => {
            let options = '<option value="">-- Pilih / Cari Pangkat & Golongan --</option>';

            // Kelompokkan berdasarkan Golongan PNS dan PPPK
            const pnsData = data.filter(item => item.kategori === 'PNS');
            const pppkData = data.filter(item => item.kategori === 'PPPK');

            options += '<optgroup label="PNS (Pegawai Negeri Sipil)">';
            pnsData.forEach(item => {
                const isSelected = (selectedPangkat === item.label) ? 'selected' : '';
                options += `<option value="${item.label}" ${isSelected}>${item.label}</option>`;
            });
            options += '</optgroup>';

            options += '<optgroup label="PPPK (Pegawai Pemerintah dengan Perjanjian Kerja)">';
            pppkData.forEach(item => {
                const isSelected = (selectedPangkat === item.label) ? 'selected' : '';
                options += `<option value="${item.label}" ${isSelected}>${item.label}</option>`;
            });
            options += '</optgroup>';

            $pangkatSelect.html(options);

            // Inisialisasi Select2 pencarian
            $pangkatSelect.select2({
                theme: 'bootstrap-5',
                placeholder: '-- Ketik untuk mencari Golongan / Pangkat --',
                width: '100%'
            });
        })
        .catch(err => {
            console.error('Gagal memuat dataset pangkat golongan:', err);
        });
});
</script>

@endpush