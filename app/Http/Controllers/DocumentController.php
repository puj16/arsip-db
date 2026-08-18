<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessDocumentOcr;
use App\Models\Arsip;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Upload dokumen PDF baru untuk arsip yang sudah ada, simpan ke storage
     * lokal (disk 'public'), lalu dispatch job OCR.
     *
     * Dipanggil dari route terpisah (mis. tombol "Tambah Dokumen" di
     * halaman detail arsip) ATAU dipanggil langsung dari ArsipController@store
     * lewat method storeForArsip() di bawah kalau upload dilakukan
     * bersamaan dengan input metadata.
     */
    public function store(Request $request, Arsip $arsip)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:51200', // max 50MB
        ]);

        $this->storeForArsip($request, $arsip);

        return redirect()
            ->route('arsip.index')
            ->with('success', 'Dokumen berhasil diunggah, sedang diproses.');
    }

    /**
     * Logic inti upload — dipisah supaya bisa dipanggil dari controller lain
     * (ArsipController@store) tanpa duplikasi kode.
     */
    public function storeForArsip(Request $request, Arsip $arsip): Document
    {
        $file = $request->file('file');

        // Simpan ke storage/app/public/arsip-documents/{arsip_id}/namafile.pdf
        // Pastikan sudah jalankan: php artisan storage:link
        $path = $file->store("arsip-documents/{$arsip->id}", 'public');

        $document = Document::create([
            'arsip_id' => $arsip->id,
            'storage_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'source_type' => 'upload_lokal',
            'read_status' => 'PENDING',
        ]);

        ProcessDocumentOcr::dispatch($document);

        return $document;
    }

    /**
     * Endpoint JSON untuk polling status OCR dari halaman (dipanggil via fetch()).
     */
    public function status(Document $document)
    {
        return response()->json([
            'id' => $document->id,
            'read_status' => $document->read_status,
            'page_count' => $document->page_count,
            'read_error' => $document->read_error,
        ]);
    }

    /**
     * Proses ulang dokumen yang gagal/pending.
     */
    public function reprocess(Document $document)
    {
        $document->update(['read_status' => 'PENDING']);
        ProcessDocumentOcr::dispatch($document);

        return back()->with('success', 'Dokumen diproses ulang.');
    }

    /**
     * Buka/unduh file PDF asli dari storage lokal.
     */
    public function view(Document $document)
    {
        if (!Storage::disk('public')->exists($document->storage_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk('public')->response($document->storage_path);
    }
}
