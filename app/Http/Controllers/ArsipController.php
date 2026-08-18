<?php

namespace App\Http\Controllers;

use App\Models\Arsip;
use App\Models\DocumentPage;
use Illuminate\Http\Request;

class ArsipController extends Controller
{
    /**
     * Halaman utama: search bar di atas + tabel metadata arsip di bawah.
     * Search mencakup metadata (LIKE) DAN isi dokumen hasil OCR (FULLTEXT).
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $query = Arsip::query()->with(['documents' => function ($rel) {
            $rel->select('id', 'arsip_id', 'read_status', 'page_count');
        }]);

        $contentSnippets = collect();

        if ($q !== '') {
            // 1. Cocok berdasarkan metadata
            $metadataIds = Arsip::query()
                ->where('nama_pencipta_arsip', 'like', "%{$q}%")
                ->orWhere('ringkasan_arsip', 'like', "%{$q}%")
                ->orWhere('nomor_berita_acara1', 'like', "%{$q}%")
                ->orWhere('nomor_berita_acara2', 'like', "%{$q}%")
                ->pluck('id');

            // 2. Cocok berdasarkan isi dokumen (FULLTEXT di document_pages)
            $matchingPages = DocumentPage::query()
                ->select('document_pages.*')
                ->selectRaw('MATCH(extracted_text) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance', [$q])
                ->whereRaw('MATCH(extracted_text) AGAINST(? IN NATURAL LANGUAGE MODE)', [$q])
                ->with('document')
                ->orderByDesc('relevance')
                ->limit(50)
                ->get();

            $contentArsipIds = $matchingPages->pluck('document.arsip_id')->filter();

            // Simpan snippet per arsip_id untuk ditampilkan di tabel (opsional, 1 snippet per arsip)
            foreach ($matchingPages as $page) {
                $arsipId = $page->document->arsip_id ?? null;
                if ($arsipId && !$contentSnippets->has($arsipId)) {
                    $contentSnippets->put($arsipId, [
                        'page_number' => $page->page_number,
                        'snippet' => $this->buildSnippet($page->extracted_text, $q),
                    ]);
                }
            }

            $allIds = $metadataIds->merge($contentArsipIds)->unique();
            $query->whereIn('id', $allIds);
        }

        $arsip = $query->orderBy('nomor_urut')->paginate(15)->withQueryString();

        return view('arsip.index', [
            'arsip' => $arsip,
            'q' => $q,
            'contentSnippets' => $contentSnippets,
        ]);
    }

    public function create()
    {
        return view('arsip.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nomor_urut' => 'required|integer',
            'nomor_lama_cf' => 'nullable|string',
            'tanggal_bast' => 'nullable|date',
            'tahun' => 'nullable|integer',
            'nomor_berita_acara1' => 'nullable|string',
            'nomor_berita_acara2' => 'nullable|string',
            'nama_pencipta_arsip' => 'required|string',
            'penyingkatan_pencipta_arsip' => 'nullable|string',
            'klasifikasi_pencipta_arsip' => 'nullable|in:Lembaga Negara,Ormas,Lain-Lain,BUMN',
            'kelengkapan_berkas' => 'nullable|string',
            'jenis_berkas' => 'nullable|in:Asli,Kopi',
            'keterangan_kelengkapan' => 'nullable|string',
            'lokasi_rak' => 'nullable|string',
            'lokasi_baris' => 'nullable|string',
            'lokasi_boks' => 'nullable|string',
            'status_pemindaian_bast' => 'boolean',
            'status_pemindaian_daftar' => 'boolean',
            'kelengkapan_dipindai' => 'nullable|string',
            'kategori_arsip' => 'nullable|in:Statis,Dinamis',
            'tahun_arsip_mulai' => 'nullable|integer',
            'tahun_arsip_selesai' => 'nullable|integer',
            'jumlah_arsip_diserahkan' => 'nullable|string',
            'ringkasan_arsip' => 'nullable|string',
            // dokumen bersifat opsional saat pertama kali submit
            'file' => 'nullable|file|mimes:pdf|max:51200',
        ]);

        $arsip = Arsip::create(collect($data)->except('file')->toArray());

        if ($request->hasFile('file')) {
            app(DocumentController::class)->storeForArsip($request, $arsip);
        }

        return redirect()->route('arsip.index')->with('success', 'Arsip berhasil ditambahkan.');
    }

    /**
     * Endpoint JSON untuk search modal (live search, dipanggil via fetch()).
     * Return grouped result: metadata arsip & isi dokumen (halaman).
     */
    public function liveSearch(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 3) {
            return response()->json(['metadata' => [], 'content' => []]);
        }

        $metadata = Arsip::query()
            ->where('nama_pencipta_arsip', 'like', "%{$q}%")
            ->orWhere('ringkasan_arsip', 'like', "%{$q}%")
            ->orWhere('nomor_berita_acara1', 'like', "%{$q}%")
            ->orWhere('nomor_berita_acara2', 'like', "%{$q}%")
            ->limit(8)
            ->get(['id', 'nama_pencipta_arsip', 'ringkasan_arsip']);

        $pages = DocumentPage::query()
            ->select('document_pages.*')
            ->selectRaw('MATCH(extracted_text) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance', [$q])
            ->whereRaw('MATCH(extracted_text) AGAINST(? IN NATURAL LANGUAGE MODE)', [$q])
            ->with('document.arsip')
            ->orderByDesc('relevance')
            ->limit(8)
            ->get();

        return response()->json([
            'metadata' => $metadata->map(fn ($a) => [
                'id' => $a->id,
                'title' => $a->nama_pencipta_arsip,
                'subtitle' => \Illuminate\Support\Str::limit($a->ringkasan_arsip ?? '', 80),
            ]),
            'content' => $pages->map(fn ($p) => [
                'document_id' => $p->document_id,
                'arsip_title' => $p->document->arsip->nama_pencipta_arsip ?? '-',
                'page_number' => $p->page_number,
                'snippet' => $this->buildSnippet($p->extracted_text, $q),
            ]),
        ]);
    }

    protected function buildSnippet(?string $text, string $keyword, int $context = 80): string
    {
        if (!$text) {
            return '';
        }

        $pos = mb_stripos($text, $keyword);
        if ($pos === false) {
            return mb_substr($text, 0, $context * 2) . '...';
        }

        $start = max(0, $pos - $context);
        $length = $context * 2 + mb_strlen($keyword);
        $snippet = mb_substr($text, $start, $length);

        return ($start > 0 ? '...' : '') . trim($snippet) . '...';
    }
}
