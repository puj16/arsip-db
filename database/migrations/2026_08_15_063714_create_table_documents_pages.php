<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->unsignedInteger('page_number');
            $table->longText('extracted_text')->nullable();
            $table->timestamps();

            $table->unique(['document_id', 'page_number']);
        });

        DB::statement('ALTER TABLE document_pages ADD FULLTEXT ft_extracted_text (extracted_text)');
    }

    public function down(): void
    {
        Schema::dropIfExists('document_pages');
    }
};
