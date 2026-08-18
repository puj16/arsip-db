@extends('layouts.app')

@section('title', 'Tambah Arsip')

@section('content')

    <a href="{{ route('arsip.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Kembali</a>

    <h1 class="text-xl font-semibold text-gray-800 mt-2 mb-6">Tambah Arsip</h1>

    @if ($errors->any())
        <div class="mb-4 rounded-md bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('arsip.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="bg-white border border-gray-200 rounded-xl p-6 grid grid-cols-2 gap-4">
            <x-field label="Nomor Urut" name="nomor_urut" type="number" required />
            <x-field label="Nomor Lama (CF)" name="nomor_lama_cf" />
            <x-field label="Tanggal BAST" name="tanggal_bast" type="date" />
            <x-field label="Tahun" name="tahun" type="number" />
            <x-field label="Nomor Berita Acara 1" name="nomor_berita_acara1" />
            <x-field label="Nomor Berita Acara 2" name="nomor_berita_acara2" />
            <x-field label="Nama Pencipta Arsip" name="nama_pencipta_arsip" required />
            <x-field label="Penyingkatan Pencipta Arsip" name="penyingkatan_pencipta_arsip" />

            <x-select label="Klasifikasi Pencipta Arsip" name="klasifikasi_pencipta_arsip"
                :options="['Lembaga Negara', 'Ormas', 'Lain-Lain','BUMN']" />

            <x-field label="Kelengkapan Berkas" name="kelengkapan_berkas" placeholder="cth: 1 Berkas" />

            <x-select label="Jenis Berkas" name="jenis_berkas" :options="['Asli', 'Kopi']" />

            <x-field label="Keterangan Kelengkapan" name="keterangan_kelengkapan" placeholder="cth: BAST dan Daftar" />
            <x-field label="Lokasi Rak" name="lokasi_rak" />
            <x-field label="Lokasi Baris" name="lokasi_baris" />
            <x-field label="Lokasi Boks" name="lokasi_boks" />

            <x-select label="Kategori Arsip" name="kategori_arsip" :options="['Statis', 'Dinamis']" />

            <x-field label="Tahun Arsip Mulai" name="tahun_arsip_mulai" type="number" />
            <x-field label="Tahun Arsip Selesai" name="tahun_arsip_selesai" type="number" />

            <div class="col-span-2">
                <label class="block text-sm text-gray-600 mb-1">Jumlah Arsip yang Diserahkan</label>
                <textarea name="jumlah_arsip_diserahkan" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('jumlah_arsip_diserahkan') }}</textarea>
            </div>

            <div class="col-span-2">
                <label class="block text-sm text-gray-600 mb-1">Ringkasan Arsip</label>
                <textarea name="ringkasan_arsip" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('ringkasan_arsip') }}</textarea>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Dokumen Digital (PDF, opsional)</label>
            <input type="file" name="file" accept="application/pdf"
                   class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 file:text-sm hover:file:bg-blue-100">
            <p class="text-xs text-gray-400 mt-2">
                Kalau diisi, dokumen akan otomatis diproses OCR di latar belakang setelah disimpan.
            </p>
        </div>

        <button type="submit"
                class="inline-flex items-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-medium text-white shadow-sm hover:bg-blue-700 transition">
            Simpan Arsip
        </button>
    </form>

@endsection
