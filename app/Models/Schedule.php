<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'training_id',
        'date',
        'start_time',
        'end_time',
        'activity',
        'jp',
        'link_zoom',
        'pic',
        'pengajar_id',
        'surat_tugas'
         
    ];

    // Relasi ke User Pengajar
    public function pengajar()
    {
        return $this->belongsTo(User::class, 'pengajar_id');
    }

    // Relasi ke Pelatihan
    public function training()
    {
        return $this->belongsTo(Training::class);
    }
}