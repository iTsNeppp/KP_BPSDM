<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Schedule;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PengajarFinanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $search;
    protected $bulan;
    protected $tahun;

    public function __construct($search = null, $bulan = null, $tahun = null)
    {
        $this->search = $search;
        $this->bulan  = $bulan;
        $this->tahun  = $tahun;
    }

    public function collection()
    {
        $bulan = $this->bulan;
        $tahun = $this->tahun;

        return User::where('role', 'pengajar')
            ->with('pengajar')
            ->when($this->search, function($q) {
                $q->where('name', 'LIKE', "%{$this->search}%")
                  ->orWhere('nip_nik', 'LIKE', "%{$this->search}%");
            })
            ->get()
            ->map(function($user) use ($bulan, $tahun) {
                $scheduleQuery = Schedule::where('pengajar_id', $user->id)
                    ->when($bulan, function($q) use ($bulan) {
                        $q->whereMonth('date', $bulan);
                    })
                    ->when($tahun, function($q) use ($tahun) {
                        $q->whereYear('date', $tahun);
                    });

                $user->total_jp = (int) $scheduleQuery->sum('jp');
                $user->total_sessions = $scheduleQuery->count();
                return $user;
            });
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Lengkap',
            'NIP / NIK',
            'Jabatan Fungsional',
            'Pangkat / Golongan',
            'Instansi / Unit Kerja',
            'Nomor NPWP',
            'Bank Asal Rekening',
            'Nomor Rekening',
            'Rekening Atas Nama',
            'Total Sesi Mengajar',
            'Total JP Mengajar'
        ];
    }

    public function map($user): array
    {
        static $no = 1;
        $pengajar = $user->pengajar;

        return [
            $no++,
            $user->name,
            "'" . ($user->nip_nik ?? '-'),
            $user->jabatan ?? '-',
            $pengajar->pangkat_golongan ?? '-',
            $pengajar->instansi ?? $user->instansi ?? '-',
            "'" . ($pengajar->npwp ?? '-'),
            $pengajar->nama_bank ?? '-',
            "'" . ($pengajar->nomor_rekening ?? '-'),
            $pengajar->nama_rekening ?? '-',
            $user->total_sessions . ' Sesi',
            $user->total_jp . ' JP'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1E3A8A']
                ]
            ],
        ];
    }
}