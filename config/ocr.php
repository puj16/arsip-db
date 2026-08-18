<?php

/**
 * Path ke binary OCR — dibutuhkan khusus di Windows karena ocrmypdf,
 * tesseract, dan poppler (pdftotext/pdfinfo) biasanya tidak ter-daftar
 * di PATH sistem seperti di Linux/WSL2.
 *
 * Isi .env sesuai lokasi instalasi masing-masing:
 *   OCR_MYPDF="C:/ocrmyPDF/.venv/Scripts/ocrmypdf.exe"
 *   OCR_TESSERACT="C:/Program Files/Tesseract-OCR/tesseract.exe"
 *   PDFTOTEXT_PATH="C:/poppler/Library/bin/pdftotext.exe"
 *   PDFINFO_PATH="C:/poppler/Library/bin/pdfinfo.exe"
 *   OCR_LANGS=ind+eng+nld
 *
 * Kalau nanti pindah ke server Linux/WSL2, cukup ganti isi .env jadi
 * nama command polos (ocrmypdf, tesseract, pdftotext, pdfinfo) tanpa
 * perlu ubah kode job sama sekali.
 */

return [
    'ocrmypdf' => env('OCR_MYPDF', 'ocrmypdf'),
    'tesseract' => env('OCR_TESSERACT', 'tesseract'),
    'pdftotext' => env('PDFTOTEXT_PATH', 'pdftotext'),
    'pdfinfo' => env('PDFINFO_PATH', 'pdfinfo'),
    'langs' => env('OCR_LANGS', 'ind'),
];
