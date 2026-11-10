<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Surat Keterangan Mengajar - {{ $user->name }}</title>
    <style>
        @page {
            margin: 1.5cm 1.5cm 1.5cm 1.5cm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9.5pt;
            line-height: 1.4;
            color: #222;
        }

        /* KOP SURAT */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .header-title {
            text-align: center;
        }
        .header-title h3 {
            margin: 0;
            font-size: 13pt;
            text-transform: uppercase;
            font-weight: bold;
            color: #111;
        }
        .header-title p {
            margin: 3px 0 0 0;
            font-size: 9pt;
            color: #555;
        }

        /* TABEL IDENTITAS */
        .meta-table {
            width: 100%;
            margin-bottom: 15px;
            font-size: 9pt;
        }
        .meta-table td {
            padding: 3px 4px;
            vertical-align: top;
        }
        .meta-label {
            font-weight: bold;
            width: 28%;
        }

        /* TABEL RINCIAN */
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 8.5pt;
        }
        .schedule-table th, 
        .schedule-table td {
            border: 1px solid #444;
            padding: 6px 6px;
            vertical-align: top;
        }
        .schedule-table th {
            background-color: #2d3748;
            color: #ffffff;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8pt;
        }
        .schedule-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }

        /* TANDA TANGAN */
        .signature-table {
            width: 100%;
            margin-top: 30px;
            page-break-inside: avoid;
            font-size: 9pt;
        }
    </style>
</head>
<body>

    <!-- KOP HEADER -->
    <table class="header-table">
        <tr>
            <td class="header-title">
                <h3>SURAT KETERANGAN PENUGASAN MENGAJAR</h3>
                <p>INTEGRAL - Sistem Informasi Pengelolaan Pelatihan Terintegrasi BPSDM Jawa Barat</p>
            </td>
        </tr>
    </table>

    <p style="margin-bottom: 10px;">
        Menerangkan bahwa Tenaga Pengajar / Widyaiswara di bawah ini telah menyelesaikan sesi penugasan materi pembelajaran dengan rincian sebagai berikut:
    </p>

    <!-- DATA IDENTITAS PENGAJAR -->
    <table class="meta-table">
        <tr>
            <td class="meta-label">Nama Lengkap</td>
            <td width="1%">:</td>
            <td class="text-bold">{{ $user->name }}</td>
        </tr>
        <tr>
            <td class="meta-label">NIP / NIK</td>
            <td>:</td>
            <td>{{ $user->nip_nik ?? '-' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Jabatan Fungsional</td>
            <td>:</td>
            <td>{{ $user->jabatan ?? 'Tenaga Pengajar / Widyaiswara' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Pangkat / Golongan</td>
            <td>:</td>
            <td>{{ $user->pengajar->pangkat_golongan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Instansi / Unit Kerja</td>
            <td>:</td>
            <td>{{ $user->pengajar->instansi ?? $user->instansi ?? 'BPSDM Provinsi Jawa Barat' }}</td>
        </tr>
        <!-- PERIODE PENUGASAN DI KOP -->
        <tr style="background-color: #eff6ff;">
            <td class="meta-label" style="color: #1e40af;">Periode Penugasan</td>
            <td style="color: #1e40af;">:</td>
            <td class="text-bold" style="color: #1e40af;">{{ $periodeLabel }}</td>
        </tr>
    </table>

    <p style="margin-bottom: 6px; font-weight: bold;">Rincian Pelatihan & Materi yang Diajarkan:</p>

    <!-- TABEL RINCIAN MATERI & PELATIHAN -->
    <table class="schedule-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="28%">Nama Pelatihan</th>
                <th width="16%">Hari / Tanggal</th>
                <th width="14%">Waktu (WIB)</th>
                <th width="30%">Materi / Kegiatan</th>
                <th width="7%">JP</th>
            </tr>
        </thead>
        <tbody>
            @forelse($schedules as $index => $s)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    <span class="text-bold">{{ $s->training->nama_pelatihan ?? '-' }}</span><br>
                    <small style="font-size: 7.5pt; color: #555;">Angkatan {{ $s->training->angkatan ?? '-' }} ({{ $s->training->bidang ?? '-' }})</small>
                </td>
                <td>
                    {{ \Carbon\Carbon::parse($s->date)->translatedFormat('l') }}<br>
                    <span class="text-bold">{{ \Carbon\Carbon::parse($s->date)->translatedFormat('d M Y') }}</span>
                </td>
                <td class="text-center">
                    {{ $s->start_time }} - {{ $s->end_time }}
                </td>
                <td>
                    {{ $s->activity }}
                </td>
                <td class="text-center text-bold">
                    {{ $s->jp ?? '-' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center" style="padding: 20px; color: #777; font-style: italic;">
                    Tidak ada jadwal mengajar pada periode {{ $periodeLabel }}.
                </td>
            </tr>
            @endforelse
        </tbody>
        @if($schedules->isNotEmpty())
        <tfoot>
            <tr style="background-color: #edf2f7; font-weight: bold;">
                <td colspan="5" style="text-align: right; padding-right: 10px;">TOTAL JAM PELAJARAN (JP) PERIODE INI:</td>
                <td class="text-center">{{ $totalJp }} JP</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <p style="margin-top: 15px; font-size: 8.5pt; color: #555;">
        * Keterangan penugasan ini di-generate otomatis berdasarkan data sistem INTEGRAL untuk keperluan pelaporan capaian JP periode <strong>{{ $periodeLabel }}</strong>.
    </p>

    <!-- LEMBAR PENGESAHAN -->
    <table class="signature-table">
        <tr>
            <td width="55%"></td>
            <td width="45%" style="text-align: center;">
                <p style="margin-bottom: 55px;">
                    Bandung, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                    <strong>Pengelola Pelatihan / PIC,</strong>
                </p>
                <p style="margin: 0; font-weight: bold; text-decoration: underline;">
                    {{ auth()->user()->name }}
                </p>
                <p style="margin: 0; font-size: 8pt; color: #555;">
                    NIP: {{ auth()->user()->nip_nik ?? '..................................' }}
                </p>
            </td>
        </tr>
    </table>

</body>
</html>