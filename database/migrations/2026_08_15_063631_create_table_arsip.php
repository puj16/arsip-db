<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('arsip', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('nomor_urut');
            $table->string('nomor_lama_cf')->nullable();
            $table->date('tanggal_bast')->nullable();
            $table->unsignedSmallInteger('tahun')->nullable();
            $table->string('nomor_berita_acara1')->nullable();
            $table->string('nomor_berita_acara2')->nullable();
            $table->string('nama_pencipta_arsip');
            $table->string('penyingkatan_pencipta_arsip')->nullable();
            $table->enum('klasifikasi_pencipta_arsip', ['Lembaga Negara', 'Ormas', 'Lain-Lain'])->default('Lain-Lain');
            $table->string('kelengkapan_berkas')->nullable();
            $table->enum('jenis_berkas', ['Asli', 'Kopi'])->nullable();
            $table->text('keterangan_kelengkapan')->nullable();
            $table->string('lokasi_rak')->nullable();
            $table->string('lokasi_baris')->nullable();
            $table->string('lokasi_boks')->nullable();
            $table->boolean('status_pemindaian_bast')->default(false);
            $table->boolean('status_pemindaian_daftar')->default(false);
            $table->string('kelengkapan_dipindai')->nullable();
            $table->enum('kategori_arsip', ['Statis', 'Dinamis'])->default('Statis');
            $table->unsignedSmallInteger('tahun_arsip_mulai')->nullable();
            $table->unsignedSmallInteger('tahun_arsip_selesai')->nullable();
            $table->text('jumlah_arsip_diserahkan')->nullable();
            $table->text('ringkasan_arsip')->nullable();
            $table->timestamps();

            $table->index(['tahun']);
            $table->index(['kategori_arsip']);
            $table->index(['nama_pencipta_arsip']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arsip');
    }
};
