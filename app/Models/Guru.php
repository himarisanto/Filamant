<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    use HasFactory;

    protected $table = 'gurus';

    protected $fillable = [
        'nip',
        'nama',
        'email',
        'no_hp',
        'alamat',
    ];

    public function kelas()
    {
        return $this->belongsToMany(Kelas::class, 'guru_kelas', 'guru_id', 'kelas_id');
    }

    public function siswas()
    {
        return $this->hasManyThrough(
            Siswa::class,
            Kelas::class,
            'id',
            'kelas_id',
            'id',
            'id' 
        )->whereHas('kelas', function ($query) {
            $query->whereHas('gurus', function ($q) {
                $q->where('gurus.id', $this->id);
            });
        });
    }
}
