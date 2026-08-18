<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('arsip_id')->constrained('arsip')->cascadeOnDelete();

            // Storage lokal (Laravel Storage disk 'public') — sumber utama sekarang.
            $table->string('storage_path')->nullable(); // path relatif di storage/app/public

            // Kolom Drive dipertahankan sesuai model kamu, nullable, untuk
            // jaga-jaga kalau nanti sebagian dokumen tetap ada yang di Drive.
            $table->string('drive_file_id')->nullable();
            $table->string('drive_url')->nullable();

            $table->string('file_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->enum('source_type', ['upload_lokal', 'drive'])->default('upload_lokal');
            $table->enum('read_status', ['PENDING', 'PROCESSING', 'DONE', 'FAILED'])->default('PENDING');
            $table->text('read_error')->nullable();
            $table->unsignedInteger('page_count')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
