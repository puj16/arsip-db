<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    protected $fillable = [
        'arsip_id',
        'drive_file_id',
        'drive_url',
        'file_name',
        'mime_type',
        'source_type',
        'read_status',
        'read_error',
        'page_count',
        'storage_path',
    ];

    public function arsip(): BelongsTo
    {
        return $this->belongsTo(Arsip::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(DocumentPage::class)->orderBy('page_number');
    }
}
