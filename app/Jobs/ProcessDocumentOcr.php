<?php

namespace App\Jobs;

use App\Models\Document;
use App\Models\DocumentPage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Pipeline OCR sepenuhnya lokal, tidak ada API eksternal:
 *
 *   1. Ambil file PDF dari storage lokal (storage/app/public/{storage_path})
 *   2. OCRmyPDF + Tesseract -> hasilkan PDF dengan text layer (untuk halaman scan)
 *   3. pdfinfo (Poppler) -> hitung jumlah halaman
 *   4. pdftotext (Poppler), dipanggil per halaman -> ekstrak teks per halaman
 *   5. Simpan ke document_pages
 *
 * Dependency yang harus terinstall di server/WSL2:
 *   sudo apt install ocrmypdf tesseract-ocr tesseract-ocr-ind poppler-utils ghostscript
 *
 * Dispatch dari controller:
 *   ProcessDocumentOcr::dispatch($document);
 * Worker harus jalan:
 *   php artisan queue:work
 */
class ProcessDocumentOcr implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 1800; // 30 menit, dokumen tebal bisa lama

    public function __construct(public Document $document)
    {
    }

    public function handle(): void
    {
        $this->document->update(['read_status' => 'PROCESSING']);

        $workDir = storage_path('app/ocr_tmp/' . $this->document->id);
        if (!is_dir($workDir)) {
            mkdir($workDir, 0775, true);
        }

        $outputPath = $workDir . '/output_ocr.pdf';

        try {
            // 1. Path file asli di storage lokal (disk 'public')
            $inputPath = Storage::disk('public')->path($this->document->storage_path);

            if (!file_exists($inputPath)) {
                throw new \RuntimeException("File tidak ditemukan di storage: {$inputPath}");
            }

            // 2. OCRmyPDF: tambahkan text layer hasil OCR ke PDF
            //    --skip-text: kalau sebagian halaman sudah ada text layer, jangan di-OCR ulang
            //    Kalau semua dokumen dipastikan hasil scan murni, ganti ke --force-ocr
            //
            // Path binary diambil dari config/ocr.php (env OCR_MYPDF dst) karena
            // di Windows, ocrmypdf/tesseract/poppler biasanya tidak ada di PATH
            // sistem seperti di Linux. $subprocessEnv menambahkan folder tesseract
            // & poppler ke PATH khusus untuk subprocess ini, supaya ocrmypdf bisa
            // menemukan `tesseract` saat dia panggil secara internal.
            $subprocessEnv = [
                'PATH' => implode(PATH_SEPARATOR, array_filter([
                    dirname(config('ocr.tesseract')),
                    dirname(config('ocr.pdftotext')),
                    getenv('PATH') ?: '',
                ])),
            ];

            $ocrProcess = new Process([
                config('ocr.ocrmypdf'),
                '--language', config('ocr.langs'),
                '--skip-text',
                '--optimize', '1',
                $inputPath,
                $outputPath,
            ], null, $subprocessEnv);
            $ocrProcess->setTimeout($this->timeout);
            $ocrProcess->run();

            if (!$ocrProcess->isSuccessful()) {
                throw new ProcessFailedException($ocrProcess);
            }

            // 3. Hitung jumlah halaman pakai pdfinfo (Poppler)
            $pdfinfo = new Process([config('ocr.pdfinfo'), $outputPath]);
            $pdfinfo->run();
            $pageCount = 0;
            if ($pdfinfo->isSuccessful()) {
                if (preg_match('/Pages:\s+(\d+)/', $pdfinfo->getOutput(), $m)) {
                    $pageCount = (int) $m[1];
                }
            }

            if ($pageCount === 0) {
                throw new \RuntimeException('Gagal membaca jumlah halaman PDF (pdfinfo).');
            }

            // 4. Ekstrak teks per halaman pakai pdftotext (Poppler), dipanggil satu-satu
            for ($page = 1; $page <= $pageCount; $page++) {
                $pdftotext = new Process([
                    config('ocr.pdftotext'),
                    '-f', (string) $page,
                    '-l', (string) $page,
                    '-layout',
                    $outputPath,
                    '-', // output ke stdout
                ]);
                $pdftotext->run();

                $text = $pdftotext->isSuccessful() ? $pdftotext->getOutput() : '';

                DocumentPage::updateOrCreate(
                    [
                        'document_id' => $this->document->id,
                        'page_number' => $page,
                    ],
                    [
                        'extracted_text' => trim($text),
                    ]
                );
            }

            $this->document->update([
                'read_status' => 'DONE',
                'page_count' => $pageCount,
                'read_error' => null,
            ]);

            // 6. Ganti file asli di storage dengan versi hasil OCR (sudah ada
            // text layer, sehingga saat dibuka tulisannya bisa di-select/copy).
            // File asli (scan mentah tanpa text layer) tidak lagi disimpan.
            $finalPath = Storage::disk('public')->path($this->document->storage_path);
            copy($outputPath, $finalPath);
        } catch (\Throwable $e) {
            Log::error('OCR gagal untuk document #' . $this->document->id . ': ' . $e->getMessage());

            $this->document->update([
                'read_status' => 'FAILED',
                'read_error' => substr($e->getMessage(), 0, 1000),
            ]);
        } finally {
            @unlink($outputPath);
            @rmdir($workDir);
        }
    }
}
