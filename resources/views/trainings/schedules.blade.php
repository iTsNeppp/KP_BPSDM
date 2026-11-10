@extends('layouts.master')

@section('title', 'Jadwal Pelatihan')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
@endpush

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <h4 class="fw-bold py-1 mb-0">
        <span class="text-muted fw-light">Pelatihan /</span> Jadwal: {{ $training->nama_pelatihan }}
    </h4>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('trainings.manage', $training->id) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bx bx-arrow-back me-1"></i> Kembali
        </a>
        <button type="button" class="btn btn-success btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#modalImportSchedule">
            <i class="bx bx-file me-1"></i> Import Excel
        </button>
        <a href="{{ route('schedules.pdf', $training->id) }}" class="btn btn-danger btn-sm shadow-sm" target="_blank">
            <i class="bx bxs-file-pdf me-1"></i> Unduh PDF
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success border-0 shadow-sm alert-dismissible" role="alert">
        <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm alert-dismissible" role="alert">
        <i class="bx bx-error-circle me-1"></i> <strong>Gagal:</strong> {{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row">
    <!-- KOLOM KIRI: FORM TAMBAH MANUAL -->
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm border-0">
            <h5 class="card-header border-bottom">Tambah Sesi Jadwal</h5>
            <div class="card-body pt-3">
                <form action="{{ route('schedules.store', $training->id) }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tanggal Sesi <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-control" value="{{ old('date', date('Y-m-d')) }}" required>
                    </div>

                    <!-- PENGATURAN WAKTU & AKUMULASI JP (1 JP = 45 MENIT) -->
                    <div class="row mb-3 g-2">
                        <div class="col-4">
                            <label class="form-label fw-bold">1. Mulai <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" id="add_start_time" class="form-control" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-bold text-primary">2. JP <small>(45m)</small></label>
                            <input type="number" name="jp" id="add_jp" class="form-control border-primary" placeholder="Jml JP" min="1">
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-bold">3. Selesai <span class="text-danger">*</span></label>
                            <input type="time" name="end_time" id="add_end_time" class="form-control bg-light" required title="Terhitung otomatis dari JP">
                        </div>
                        <div class="col-12">
                            <small class="text-muted" style="font-size: 11px;">
                                <i class="bx bx-info-circle me-1"></i> Jam selesai terisi otomatis sesuai JP (1 JP = 45 menit).
                            </small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Materi / Kegiatan <span class="text-danger">*</span></label>
                        <input type="text" name="activity" class="form-control" placeholder="Contoh: Pengantar Transformasi Digital" required>
                    </div>

                    <!-- INPUT LINK ZOOM -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-success"><i class="bx bx-video me-1"></i>Link Zoom / Virtual Meeting <small class="text-muted">(Opsional)</small></label>
                        <input type="url" name="link_zoom" class="form-control" placeholder="https://zoom.us/j/...">
                    </div>
                    
                    <!-- INPUT PILIH PENGAJAR -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-info"><i class="bx bx-chalkboard me-1"></i>Tenaga Pengajar / Fasilitator</label>
                        <select name="pengajar_id" class="form-select select2">
                            <option value="">-- Tanpa Pengajar / Sesi Mandiri --</option>
                            @foreach($pengajars as $p)
                                <option value="{{ $p->id }}">
                                    {{ $p->name }} {{ $p->nip_nik ? "({$p->nip_nik})" : '' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text small">Pilih pengajar agar sesi ini muncul di akun Pengajar.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-primary">Penanggung Jawab (PIC) <span class="text-danger">*</span></label>
                        <input type="text" name="pic" class="form-control" placeholder="Nama PIC Kelas / Panitia" value="{{ old('pic', auth()->user()->name) }}" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 shadow-sm">
                        <i class="bx bx-plus me-1"></i> Simpan Jadwal
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- KOLOM KANAN: TABEL DAFTAR JADWAL (TEMPLATE LAMA) -->
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Materi & Penanggung Jawab</th> 
                            <th width="150" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($schedules as $s)
                        <tr>
                            <td>
                                <span class="badge bg-label-secondary">
                                    {{ \Carbon\Carbon::parse($s->date)->format('d/m/Y') }}
                                </span>
                                @if($s->jp)
                                    <span class="badge bg-label-primary ms-1">{{ $s->jp }} JP</span>
                                @endif
                                <br>
                                <small class="fw-bold">{{ $s->start_time }} - {{ $s->end_time }}</small>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $s->activity }}</div>
                                
                                {{-- Keterangan Pengajar jika ada --}}
                                @if($s->pengajar)
                                    <small class="text-info d-block fw-semibold">
                                        <i class="bx bx-chalkboard me-1"></i>Pengajar: {{ $s->pengajar->name }}
                                    </small>
                                @endif

                                {{-- PIC & Link Zoom --}}
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <small class="text-primary"><i class="bx bx-user me-1"></i>{{ $s->pic }}</small>
                                    @if($s->link_zoom)
                                        <a href="{{ $s->link_zoom }}" target="_blank" class="badge bg-label-success text-decoration-none">
                                            <i class="bx bx-video me-1"></i> Buka Zoom
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <!-- TOMBOL EDIT -->
                                    <button class="btn btn-sm btn-icon btn-outline-warning" 
                                        onclick="editSchedule({{ json_encode($s) }})"
                                        data-bs-toggle="modal" data-bs-target="#modalEditSchedule" title="Edit Jadwal">
                                        <i class="bx bx-edit-alt"></i>
                                    </button>

                                    <!-- TOMBOL HAPUS -->
                                    <form action="{{ route('schedules.destroy', $s->id) }}" method="POST" onsubmit="return confirm('Hapus sesi jadwal ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Hapus Jadwal">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-muted">Belum ada jadwal.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL IMPORT JADWAL EXCEL -->
<div class="modal fade" id="modalImportSchedule" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('schedules.import', $training->id) }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header bg-success">
                <h5 class="modal-title text-white"><i class="bx bx-file me-1"></i> Import Jadwal dari Excel</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info py-2 small mb-3">
                    <i class="bx bx-info-circle me-1"></i> Template mendukung pengisian <strong>JP</strong>, <strong>Link Zoom</strong>, dan pencocokan <strong>Tenaga Pengajar</strong>.
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Pilih File Excel (.xlsx / .xls) <span class="text-danger">*</span></label>
                    <input type="file" name="file" class="form-control" accept=".xlsx, .xls" required>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-2">
                    <span class="small text-muted">Belum punya formatnya?</span>
                    <a href="{{ route('schedules.template') }}" class="btn btn-sm btn-outline-success">
                        <i class="bx bx-download me-1"></i> Unduh Template Excel
                    </a>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success">Mulai Import</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT JADWAL -->
<div class="modal fade" id="modalEditSchedule" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="" method="POST" id="formEditSchedule" class="modal-content">
            @csrf 
            @method('PUT')
            <div class="modal-header border-bottom">
                <h5 class="modal-title">Edit Sesi Jadwal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Tanggal Sesi <span class="text-danger">*</span></label>
                    <input type="date" name="date" id="edit_date" class="form-control" required>
                </div>

                <!-- PENGATURAN WAKTU & AKUMULASI JP MODAL EDIT -->
                <div class="row mb-3 g-2">
                    <div class="col-4">
                        <label class="form-label fw-bold">1. Mulai <span class="text-danger">*</span></label>
                        <input type="time" name="start_time" id="edit_start" class="form-control" required>
                    </div>
                    <div class="col-4">
                        <label class="form-label fw-bold text-primary">2. JP <small>(45m)</small></label>
                        <input type="number" name="jp" id="edit_jp" class="form-control border-primary" min="1">
                    </div>
                    <div class="col-4">
                        <label class="form-label fw-bold">3. Selesai <span class="text-danger">*</span></label>
                        <input type="time" name="end_time" id="edit_end" class="form-control bg-light" required title="Terhitung otomatis dari JP">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Materi / Kegiatan <span class="text-danger">*</span></label>
                    <input type="text" name="activity" id="edit_activity" class="form-control" required>
                </div>

                <!-- INPUT EDIT LINK ZOOM -->
                <div class="mb-3">
                    <label class="form-label fw-bold text-success"><i class="bx bx-video me-1"></i>Link Zoom / Virtual Meeting <small class="text-muted">(Opsional)</small></label>
                    <input type="url" name="link_zoom" id="edit_link_zoom" class="form-control" placeholder="https://zoom.us/j/...">
                </div>
                
                <!-- DROPDOWN PENGAJAR DI MODAL EDIT -->
                <div class="mb-3">
                    <label class="form-label fw-bold text-info"><i class="bx bx-chalkboard me-1"></i>Tenaga Pengajar / Fasilitator</label>
                    <select name="pengajar_id" id="edit_pengajar_id" class="form-select select2-modal">
                        <option value="">-- Tanpa Pengajar / Sesi Mandiri --</option>
                        @foreach($pengajars as $p)
                            <option value="{{ $p->id }}">
                                {{ $p->name }} {{ $p->nip_nik ? "({$p->nip_nik})" : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-primary">Penanggung Jawab (PIC) <span class="text-danger">*</span></label>
                    <input type="text" name="pic" id="edit_pic" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Select2 Inisialisasi
        $('.select2').select2({
            theme: 'bootstrap-5',
            placeholder: '-- Pilih Pengajar --',
            allowClear: true,
            width: '100%'
        });

        $('.select2-modal').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#modalEditSchedule'),
            placeholder: '-- Pilih Pengajar --',
            allowClear: true,
            width: '100%'
        });

        /**
         * FUNGSI HITUNG JAM SELESAI (1 JP = 45 MENIT)
         */
        function calculateEndTime(startTimeVal, jpVal) {
            if (!startTimeVal || !jpVal || parseInt(jpVal) <= 0) return null;

            const parts = startTimeVal.split(':');
            const hours = parseInt(parts[0], 10);
            const minutes = parseInt(parts[1], 10);

            // Total menit = Jumlah JP * 45 Menit
            const totalMinutesToAdd = parseInt(jpVal, 10) * 45;

            const date = new Date();
            date.setHours(hours, minutes, 0, 0);
            date.setMinutes(date.getMinutes() + totalMinutesToAdd);

            const endHours = String(date.getHours()).padStart(2, '0');
            const endMinutes = String(date.getMinutes()).padStart(2, '0');

            return `${endHours}:${endMinutes}`;
        }

        // 1. Event Auto Calculate pada Form Tambah
        $('#add_start_time, #add_jp').on('input change', function() {
            const startVal = $('#add_start_time').val();
            const jpVal = $('#add_jp').val();
            const calculatedEnd = calculateEndTime(startVal, jpVal);
            if (calculatedEnd) {
                $('#add_end_time').val(calculatedEnd);
            }
        });

        // 2. Event Auto Calculate pada Modal Edit
        $('#edit_start, #edit_jp').on('input change', function() {
            const startVal = $('#edit_start').val();
            const jpVal = $('#edit_jp').val();
            const calculatedEnd = calculateEndTime(startVal, jpVal);
            if (calculatedEnd) {
                $('#edit_end').val(calculatedEnd);
            }
        });
    });

    /**
     * Memasukkan Data ke Modal Edit
     */
    function editSchedule(data) {
        const url = "{{ url('schedules') }}/" + data.id;
        $('#formEditSchedule').attr('action', url);

        $('#edit_date').val(data.date);
        $('#edit_start').val(data.start_time);
        $('#edit_end').val(data.end_time);
        $('#edit_activity').val(data.activity);
        $('#edit_jp').val(data.jp);
        $('#edit_link_zoom').val(data.link_zoom);
        $('#edit_pic').val(data.pic);
        $('#edit_pengajar_id').val(data.pengajar_id).trigger('change');
    }
</script>
@endpush