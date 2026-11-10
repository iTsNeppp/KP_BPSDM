@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Sistem /</span> Kelola Pengguna
        </h4>

        <div class="d-flex flex-wrap gap-2">
            <!-- FORM PENCARIAN USER -->
            <form action="{{ route('users.index') }}" method="GET" style="min-width: 300px;">
                <div class="input-group input-group-merge shadow-sm">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Cari Nama, NIP, atau Bidang..." 
                           value="{{ $search ?? '' }}">
                    @if($search)
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary px-2">
                            <i class="bx bx-x"></i>
                        </a>
                    @endif
                </div>
            </form>

            <!-- TOMBOL TAMBAH PENGAJAR -->
            <button type="button" class="btn btn-info shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddPengajar">
                <i class="bx bx-chalkboard me-1"></i> Tambah Pengajar
            </button>

            <!-- TOMBOL TAMBAH USER (ADMIN/SUPERADMIN) -->
            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddUser">
                <i class="bx bx-user-plus me-1"></i> Tambah Admin
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm alert-dismissible" role="alert">
            <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm alert-dismissible" role="alert">
            <i class="bx bx-x-circle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($search)
        <div class="mb-3 animate__animated animate__fadeIn">
            <small class="text-muted">Hasil pencarian untuk: <span class="fw-bold text-primary">"{{ $search }}"</span></small>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Nama & Role</th>
                        <th>Bidang / Penyelenggara</th>
                        <th>Username / NIP</th>
                        <th>WhatsApp</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="d-flex justify-content-start align-items-center">
                                <div class="avatar avatar-sm me-2">
                                    <span class="avatar-initial rounded-circle bg-label-{{ $user->role == 'superadmin' ? 'danger' : ($user->role == 'admin_bidang' ? 'primary' : ($user->role == 'pengajar' ? 'info' : 'success')) }}">
                                        {{ substr($user->name, 0, 1) }}
                                    </span>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-dark">{{ $user->name }}</span>
                                    <small class="text-muted" style="font-size: 10px;">{{ strtoupper(str_replace('_', ' ', $user->role)) }}</small>
                                </div>
                            </div>
                        </td>
                        <td class="text-wrap">
                            <small class="text-muted" style="font-size: 11px; line-height: 1.2; display: block; max-width: 250px;">
                                {{ $user->bidang ?? 'Tidak Terikat Bidang (Luar)' }}
                            </small>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <code class="text-primary fw-bold" style="font-size: 12px;">{{ $user->username }}</code>
                                @if($user->nip_nik)
                                    <small class="text-danger" style="font-size: 10px;">NIP: {{ $user->nip_nik }}</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <a href="https://wa.me/{{ $user->whatsapp }}" target="_blank" class="text-body small">
                                <i class="bx bxl-whatsapp text-success me-1"></i>{{ $user->whatsapp }}
                            </a>
                        </td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="javascript:void(0);" onclick="editUser({{ json_encode($user) }})" data-bs-toggle="modal" data-bs-target="#modalEditUser">
                                        <i class="bx bx-edit-alt me-1"></i> Edit User
                                    </a>
                                    <form action="{{ route('users.reset-password', $user->id) }}" method="POST">
                                        @csrf @method('PUT')
                                        <button class="dropdown-item" onclick="return confirm('Reset password menjadi password123?')"><i class="bx bx-refresh me-1"></i> Reset Password</button>
                                    </form>
                                    <div class="dropdown-divider"></div>
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Hapus user ini beserta seluruh datanya?')">
                                        @csrf @method('DELETE')
                                        <button class="dropdown-item text-danger"><i class="bx bx-trash me-1"></i> Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bx bx-user-x h1 d-block mb-2"></i>
                            User tidak ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==============================================
     MODAL KHUSUS TAMBAH PENGAJAR 
     ============================================== -->
<div class="modal fade" id="modalAddPengajar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <!-- Role diset otomatis ke pengajar -->
                <input type="hidden" name="role" value="pengajar">
                
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white">Tambah Akun Pengajar Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 small mb-4">
                        <i class="bx bx-info-circle me-1"></i> Pengajar akan diminta melengkapi Profil Keuangan & mengubah password saat pertama kali login.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap (Beserta Gelar) <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required />
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">NIP / NIK <span class="text-danger">*</span></label>
                            <input type="text" name="nip_nik" class="form-control" required />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nomor WA <span class="text-danger">*</span></label>
                            <input type="number" name="whatsapp" class="form-control" placeholder="628..." required />
                        </div>
                    </div>
                    

                    <hr class="my-4">
                    <h6 class="fw-bold text-info"><i class="bx bx-lock-alt me-1"></i> Data Login Default</h6>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" required />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="text" name="password" class="form-control" value="password123" required />
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info text-white w-100">Buatkan Akun Pengajar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==============================================
     MODAL TAMBAH USER (ADMIN/SUPERADMIN) 
     ============================================== -->
<div class="modal fade" id="modalAddUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Admin / Superadmin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap PIC</label>
                        <input type="text" name="name" class="form-control" required />
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select" required>
                                <option value="admin_bidang">Admin Bidang</option>
                                <option value="superadmin">Superadmin</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nomor WA</label>
                            <input type="number" name="whatsapp" class="form-control" placeholder="628..." required />
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bidang</label>
                        <select name="bidang" class="form-select" required>
                            @foreach($listBidang as $b)
                                <option value="{{ $b }}">{{ $b }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required />
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100">Simpan Akun</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==============================================
     MODAL EDIT USER (GLOBAL)
     ============================================== -->
<div class="modal fade" id="modalEditUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="" method="POST" id="formEditUser" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header border-bottom">
                <h5 class="modal-title">Edit Data Pengguna</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">NAMA LENGKAP</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required />
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">USERNAME</label>
                        <input type="text" name="username" id="edit_username" class="form-control" required />
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">NIP / NIK</label>
                        <input type="text" name="nip_nik" id="edit_nip_nik" class="form-control" />
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">ROLE / HAK AKSES</label>
                        <select name="role" id="edit_role" class="form-select" required>
                            <option value="participant">Participant (Peserta)</option>
                            <option value="pengajar">Pengajar</option>
                            <option value="admin_bidang">Admin Bidang</option>
                            <option value="superadmin">Superadmin</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">NOMOR WA</label>
                        <input type="number" name="whatsapp" id="edit_whatsapp" class="form-control" required />
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">PENYELENGGARA / BIDANG</label>
                    <select name="bidang" id="edit_bidang" class="form-select">
                        <option value="">Tidak Terikat Bidang (Luar BPSDM)</option>
                        @foreach($listBidang as $b)
                            <option value="{{ $b }}">{{ $b }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="alert alert-info py-2 mb-0">
                    <small><i class="bx bx-info-circle me-1"></i> Gunakan fitur tombol Dropdown -> <strong>Reset Password</strong> pada tabel jika pengguna lupa passwordnya.</small>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Update Data User</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script>
    /**
     * Fungsi untuk mengisi data ke Modal Edit secara Dinamis
     */
    function editUser(data) {
        // Set URL Form Action untuk metode PUT
        const url = "{{ url('users') }}/" + data.id;
        $('#formEditUser').attr('action', url);

        // Isi field input dengan data dari database
        $('#edit_name').val(data.name);
        $('#edit_username').val(data.username);
        $('#edit_nip_nik').val(data.nip_nik);
        $('#edit_role').val(data.role);
        $('#edit_whatsapp').val(data.whatsapp);
        
        // Handle select bidang (jika null, pilih yang value-nya kosong)
        if(data.bidang) {
            $('#edit_bidang').val(data.bidang);
        } else {
            $('#edit_bidang').val('');
        }
    }
</script>
@endpush