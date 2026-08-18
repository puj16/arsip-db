@extends('layouts.app')

@section('title', 'Cari Arsip')

@section('content')

    {{-- Search bar tengah, lebar ke samping, mirip search Laravel docs --}}
    <div class="flex flex-col items-center mb-10">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6">Sistem Arsip Nasional</h1>

        <div class="w-full flex items-center gap-3">
            <button type="button" id="search-trigger"
                    class="flex-1 flex items-center gap-3 rounded-xl border border-gray-300 bg-white pl-4 pr-4 py-3 text-sm shadow-sm hover:border-gray-400 transition text-left">
                <svg class="h-5 w-5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <span class="flex-1 text-gray-400">Cari nama pencipta, ringkasan, atau isi dokumen...</span>
                <kbd class="hidden sm:inline-flex items-center gap-0.5 rounded border border-gray-200 bg-gray-50 px-1.5 py-0.5 text-xs text-gray-400 font-sans">
                    ⌘K
                </kbd>
            </button>

            <a href="{{ route('arsip.create') }}"
               class="shrink-0 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-medium text-white shadow-sm hover:bg-blue-700 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Arsip
            </a>
        </div>

        @if ($q !== '')
            <p class="text-xs text-gray-400 mt-3">
                Menampilkan hasil untuk "<span class="font-medium text-gray-600">{{ $q }}</span>"
                — termasuk pencarian di dalam isi dokumen hasil OCR.
            </p>
        @endif
    </div>

    {{-- Tabel metadata arsip — header 2 baris (grouped), mengikuti struktur spreadsheet asli --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-x-auto">
        <table class="min-w-[1900px] w-full text-xs">
            <thead class="bg-gray-50 text-gray-500">
                <tr class="border-b border-gray-200">
                    <th rowspan="2" class="px-3 py-2 font-medium text-left align-bottom whitespace-nowrap">No. Urut</th>
                    <th rowspan="2" class="px-3 py-2 font-medium text-left align-bottom whitespace-nowrap">Nomor Lama (CF)</th>
                    <th rowspan="2" class="px-3 py-2 font-medium text-left align-bottom whitespace-nowrap">Tanggal BAST</th>
                    <th rowspan="2" class="px-3 py-2 font-medium text-left align-bottom whitespace-nowrap">Tahun</th>
                    <th rowspan="2" class="px-3 py-2 font-medium text-left align-bottom whitespace-nowrap">Nomor Berita Acara</th>
                    <th rowspan="2" class="px-3 py-2 font-medium text-left align-bottom whitespace-nowrap">Nama Pencipta Arsip</th>
                    <th rowspan="2" class="px-3 py-2 font-medium text-left align-bottom whitespace-nowrap">Penyingkatan Pencipta</th>
                    <th rowspan="2" class="px-3 py-2 font-medium text-left align-bottom whitespace-nowrap">Klasifikasi Pencipta</th>
                    <th colspan="3" class="px-3 py-2 font-medium text-center border-l border-gray-200">Kelengkapan Berkas</th>
                    <th colspan="3" class="px-3 py-2 font-medium text-center border-l border-gray-200">Lokasi Simpan</th>
                    <th colspan="2" class="px-3 py-2 font-medium text-center border-l border-gray-200">Status Pemindaian</th>
                    <th rowspan="2" class="px-3 py-2 font-medium text-left align-bottom whitespace-nowrap border-l border-gray-200">Kelengkapan yang Dipindai</th>
                    <th rowspan="2" class="px-3 py-2 font-medium text-left align-bottom whitespace-nowrap">Kategori Arsip</th>
                    <th rowspan="2" class="px-3 py-2 font-medium text-left align-bottom whitespace-nowrap">Tahun Arsip</th>
                    <th rowspan="2" class="px-3 py-2 font-medium text-left align-bottom whitespace-nowrap">Jumlah Arsip Diserahkan</th>
                    <th rowspan="2" class="px-3 py-2 font-medium text-left align-bottom whitespace-nowrap">Ringkasan Arsip</th>
                    <th rowspan="2" class="px-3 py-2 font-medium text-left align-bottom whitespace-nowrap">Keterangan Tambahan</th>
                    <th rowspan="2" class="px-3 py-2 font-medium text-left align-bottom whitespace-nowrap border-l border-gray-200">Status Dokumen</th>
                </tr>
                <tr class="border-b border-gray-200">
                    <th class="px-3 py-2 font-medium text-center border-l border-gray-200 whitespace-nowrap">Jumlah</th>
                    <th class="px-3 py-2 font-medium text-center whitespace-nowrap">Jenis</th>
                    <th class="px-3 py-2 font-medium text-center whitespace-nowrap">Keterangan</th>
                    <th class="px-3 py-2 font-medium text-center border-l border-gray-200 whitespace-nowrap">Rak</th>
                    <th class="px-3 py-2 font-medium text-center whitespace-nowrap">Baris</th>
                    <th class="px-3 py-2 font-medium text-center whitespace-nowrap">Boks</th>
                    <th class="px-3 py-2 font-medium text-center border-l border-gray-200 whitespace-nowrap">BAST</th>
                    <th class="px-3 py-2 font-medium text-center whitespace-nowrap">Daftar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($arsip as $item)
                    <tr id="arsip-row-{{ $item->id }}" class="hover:bg-gray-50 scroll-mt-24">
                        <td class="px-3 py-2 text-gray-700 whitespace-nowrap">{{ $item->nomor_urut }}</td>
                        <td class="px-3 py-2 text-gray-700 whitespace-nowrap">{{ $item->nomor_lama_cf ?? '-' }}</td>
                        <td class="px-3 py-2 text-gray-700 whitespace-nowrap">{{ $item->tanggal_bast?->format('d-m-Y') ?? '-' }}</td>
                        <td class="px-3 py-2 text-gray-700 whitespace-nowrap">{{ $item->tahun ?? '-' }}</td>
                        <td class="px-3 py-2 text-gray-700 whitespace-nowrap">
                            {{ $item->nomor_berita_acara1 ?? '-' }}
                            @if ($item->nomor_berita_acara2)
                                / {{ $item->nomor_berita_acara2 }}
                            @endif
                        </td>
                        <td class="px-3 py-2 text-gray-700">
                            {{ $item->nama_pencipta_arsip }}
                            @if ($contentSnippets->has($item->id))
                                <div class="text-xs text-gray-400 mt-1 italic max-w-xs">
                                    Hal. {{ $contentSnippets[$item->id]['page_number'] }}:
                                    "...{{ $contentSnippets[$item->id]['snippet'] }}..."
                                </div>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-gray-700 whitespace-nowrap">{{ $item->penyingkatan_pencipta_arsip ?? '-' }}</td>
                        <td class="px-3 py-2 text-gray-700 whitespace-nowrap">{{ $item->klasifikasi_pencipta_arsip ?? '-' }}</td>
                        <td class="px-3 py-2 text-gray-700 text-center border-l border-gray-100 whitespace-nowrap">{{ $item->kelengkapan_berkas ?? '-' }}</td>
                        <td class="px-3 py-2 text-gray-700 text-center whitespace-nowrap">{{ $item->jenis_berkas ?? '-' }}</td>
                        <td class="px-3 py-2 text-gray-700 whitespace-nowrap">{{ $item->keterangan_kelengkapan ?? '-' }}</td>
                        <td class="px-3 py-2 text-gray-700 text-center border-l border-gray-100 whitespace-nowrap">{{ $item->lokasi_rak ?? '-' }}</td>
                        <td class="px-3 py-2 text-gray-700 text-center whitespace-nowrap">{{ $item->lokasi_baris ?? '-' }}</td>
                        <td class="px-3 py-2 text-gray-700 text-center whitespace-nowrap">{{ $item->lokasi_boks ?? '-' }}</td>
                        <td class="px-3 py-2 text-center border-l border-gray-100">{{ $item->status_pemindaian_bast ? '✔' : '-' }}</td>
                        <td class="px-3 py-2 text-center">{{ $item->status_pemindaian_daftar ? '✔' : '-' }}</td>
                        <td class="px-3 py-2 text-gray-700 border-l border-gray-100 whitespace-nowrap">{{ $item->kelengkapan_dipindai ?? '-' }}</td>
                        <td class="px-3 py-2 text-gray-700 whitespace-nowrap">{{ $item->kategori_arsip ?? '-' }}</td>
                        <td class="px-3 py-2 text-gray-700 whitespace-nowrap">
                            @if ($item->tahun_arsip_mulai && $item->tahun_arsip_selesai)
                                {{ $item->tahun_arsip_mulai }} - {{ $item->tahun_arsip_selesai }}
                            @else
                                {{ $item->tahun_arsip_mulai ?? $item->tahun_arsip_selesai ?? '-' }}
                            @endif
                        </td>
                        <td class="px-3 py-2 text-gray-700 max-w-xs">{{ $item->jumlah_arsip_diserahkan ?? '-' }}</td>
                        <td class="px-3 py-2 text-gray-700 max-w-xs">{{ $item->ringkasan_arsip ?? '-' }}</td>
                        <td class="px-3 py-2 text-gray-400 whitespace-nowrap">-</td>
                        <td class="px-3 py-2 border-l border-gray-100">
                            @forelse ($item->documents as $doc)
                                @php
                                    $badge = match($doc->read_status) {
                                        'DONE' => 'bg-green-100 text-green-700',
                                        'PROCESSING' => 'bg-amber-100 text-amber-700',
                                        'FAILED' => 'bg-red-100 text-red-700',
                                        default => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <div class="mb-1">
                                    <span class="inline-block text-xs px-2 py-1 rounded-full {{ $badge }} mr-1 whitespace-nowrap">
                                        {{ $doc->read_status }}
                                    </span>
                                    <a href="{{ route('documents.view', $doc) }}" target="_blank"
                                       class="text-xs text-blue-600 hover:underline whitespace-nowrap">
                                        Buka PDF
                                    </a>
                                    <form action="{{ route('documents.reprocess', $doc) }}" method="POST">
                                        @csrf

                                        <button type="submit"
                                                title="Proses ulang OCR"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-yellow-500 text-white hover:bg-yellow-600 transition"
                                                aria-label="Proses ulang OCR">
                                            <i class="fa-solid fa-rotate-right"></i>
                                        </button>
                                    </form>
                                </div>
                            @empty
                                <span class="text-xs text-gray-400 whitespace-nowrap">Belum ada dokumen</span>
                            @endforelse
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="20" class="px-4 py-8 text-center text-gray-400">
                            Tidak ada data arsip{{ $q !== '' ? ' yang cocok dengan pencarian.' : '.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $arsip->links() }}
    </div>

@endsection
