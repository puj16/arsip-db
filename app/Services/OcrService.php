<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;

class OcrService
{
    /**
     * Jalankan OCR terhadap satu PDF.
     */
    public function process(
        string $inputPdf,
        string $outputPdf
    ): int {

        $mypdf = config('ocr.mypdf');
        $language = config('ocr.language');

        $process = new Process([
            $mypdf,
            '-m',
            'ocrmypdf',

            '--skip-text',
            '--deskew',
            '--rotate-pages',

            '-l',
            $language,

            $inputPdf,
            $outputPdf,
        ]);

        $process->setTimeout(3600);

        $process->run();

        if (!$process->isSuccessful()) {

            throw new RuntimeException(
                "OCR gagal:\n" .
                $process->getErrorOutput()
            );
        }

        return $this->getPageCount($outputPdf);
    }

    /**
     * Ambil text dari SATU halaman.
     */
    public function extractPageText(
        string $pdf,
        int $page
    ): string {

        $pdftotext = config('ocr.pdftotext');

        $process = new Process([
            $pdftotext,

            '-f',
            (string) $page,

            '-l',
            (string) $page,

            '-layout',

            $pdf,

            '-',
        ]);

        $process->setTimeout(120);

        $process->run();

        if (!$process->isSuccessful()) {

            throw new RuntimeException(
                "Gagal membaca halaman {$page}:\n" .
                $process->getErrorOutput()
            );
        }

        return trim(
            $process->getOutput()
        );
    }

    /**
     * Ambil jumlah halaman PDF.
     */
    private function getPageCount(
        string $pdf
    ): int {

        $pdfinfo = config('ocr.pdfinfo');

        $process = new Process([
            $pdfinfo,
            $pdf,
        ]);

        $process->setTimeout(60);

        $process->run();

        if (!$process->isSuccessful()) {

            throw new RuntimeException(
                "Gagal membaca jumlah halaman PDF:\n" .
                $process->getErrorOutput()
            );
        }

        if (
            preg_match(
                '/Pages:\s+(\d+)/i',
                $process->getOutput(),
                $matches
            )
        ) {

            return (int) $matches[1];
        }

        throw new RuntimeException(
            'Jumlah halaman PDF tidak ditemukan.'
        );
    }
}