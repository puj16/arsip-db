<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Arsip extends Model
{
    protected $table = 'arsip';

    protected $fillable = [
        'nomor_urut',
        'nomor_lama_cf',
        'tanggal_bast',
        'tahun',
        'nomor_berita_acara1',
        'nomor_berita_acara2',
        'nama_pencipta_arsip',
        'penyingkatan_pencipta_arsip',
        'klasifikasi_pencipta_arsip',
        'kelengkapan_berkas',
        'jenis_berkas',
        'keterangan_kelengkapan',
        'lokasi_rak',
        'lokasi_baris',
        'lokasi_boks',
        'status_pemindaian_bast',
        'status_pemindaian_daftar',
        'kelengkapan_dipindai',
        'kategori_arsip',
        'tahun_arsip_mulai',
        'tahun_arsip_selesai',
        'jumlah_arsip_diserahkan',
        'ringkasan_arsip',
    ];

    protected $casts = [
        'tanggal_bast' => 'date',
        'status_pemindaian_bast' => 'boolean',
        'status_pemindaian_daftar' => 'boolean',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
