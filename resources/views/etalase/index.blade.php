@extends('layouts.etalase')

@section('title', 'TokoKita — Katalog Produk')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

    {{-- KIRI: Panel Filter Kategori & Harga (Soal 3a) --}}
    <aside class="lg:col-span-3 bg-white border border-stone-200 rounded-xl p-5 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-bold text-stone-800">Filter</h2>
            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-red-100 text-red-700">3a</span>
        </div>

        <form action="{{ route('etalase.katalog') }}" method="GET" class="space-y-5">
            @if($cari)
                <input type="hidden" name="cari" value="{{ $cari }}">
            @endif
            <input type="hidden" name="urut" value="{{ $urut }}">

            {{-- Pilihan Kategori --}}
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-stone-500 mb-2">Kategori</p>
                <div class="space-y-2 text-sm">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="kategori" value="" {{ empty($kategori) ? 'checked' : '' }} class="accent-[#A8380F]">
                        <span>Semua Kategori</span>
                    </label>
                    @foreach($daftarKategori as $kat)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="kategori" value="{{ $kat->kategori }}" {{ $kategori === $kat->kategori ? 'checked' : '' }} class="accent-[#A8380F]">
                            <span>{{ $kat->kategori }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Rentang Harga --}}
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-stone-500 mb-2">Harga (Rp)</p>
                <div class="grid grid-cols-2 gap-2">
                    <input
                        type="number"
                        name="min"
                        value="{{ $min !== null ? (int) $min : '' }}"
                        placeholder="Min"
                        min="0"
                        class="w-full px-3 py-1.5 text-sm border border-stone-300 rounded-md focus:outline-none focus:ring-1 focus:ring-[#A8380F]"
                    >
                    <input
                        type="number"
                        name="max"
                        value="{{ $max !== null ? (int) $max : '' }}"
                        placeholder="Maks"
                        min="0"
                        class="w-full px-3 py-1.5 text-sm border border-stone-300 rounded-md focus:outline-none focus:ring-1 focus:ring-[#A8380F]"
                    >
                </div>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-[#A8380F] hover:bg-[#8E2F0C] text-white text-sm font-semibold py-2 rounded-md transition">
                    Terapkan
                </button>
                <a href="{{ route('etalase.index') }}" class="px-3 py-2 text-sm border border-stone-300 rounded-md text-stone-600 hover:bg-stone-100 transition">
                    Reset
                </a>
            </div>
        </form>
    </aside>

    {{-- TENGAH: Daftar Katalog Produk --}}
    <section class="lg:col-span-6 space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <p class="text-sm font-semibold text-stone-700">
                {{ count($produk) }} produk ditemukan
                @if($cari)
                    <span class="font-normal text-stone-500">untuk "{{ $cari }}"</span>
                @endif
            </p>

            {{-- Dropdown Urutkan --}}
            <form action="{{ route('etalase.katalog') }}" method="GET" class="flex items-center gap-2 text-sm">
                @if($kategori) <input type="hidden" name="kategori" value="{{ $kategori }}"> @endif
                @if($min !== null) <input type="hidden" name="min" value="{{ (int) $min }}"> @endif
                @if($max !== null) <input type="hidden" name="max" value="{{ (int) $max }}"> @endif
                @if($cari) <input type="hidden" name="cari" value="{{ $cari }}"> @endif

                <label for="urut" class="text-stone-500">Urutkan:</label>
                <select id="urut" name="urut" onchange="this.form.submit()" class="border border-stone-300 rounded-md px-2.5 py-1 bg-white text-sm focus:outline-none">
                    <option value="termurah" {{ $urut === 'termurah' ? 'selected' : '' }}>Termurah</option>
                    <option value="termahal" {{ $urut === 'termahal' ? 'selected' : '' }}>Termahal</option>
                    <option value="nama_asc" {{ $urut === 'nama_asc' ? 'selected' : '' }}>Nama (A–Z)</option>
                </select>
            </form>
        </div>

        @if(count($produk) === 0)
            <div class="bg-white border border-stone-200 rounded-xl p-8 text-center text-stone-500 text-sm">
                Tidak ada produk yang cocok dengan filter tersebut.
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($produk as $item)
                    <div class="bg-white border border-stone-200 rounded-xl p-4 shadow-sm flex flex-col justify-between">
                        <div>
                            {{-- Placeholder Gambar Produk --}}
                            <div class="h-36 rounded-lg bg-stone-100 border border-stone-200 flex items-center justify-center mb-3 text-xs text-stone-400">
                                {{ $item->kategori }} &middot; {{ $item->id_produk }}
                            </div>

                            <h3 class="font-bold text-stone-800 text-base leading-snug">
                                {{ $item->nama_produk }}
                            </h3>
                            <p class="text-[#A8380F] font-bold text-lg mt-1">
                                {{ format_rupiah($item->harga) }}
                            </p>
                            <p class="text-xs text-stone-500 mt-0.5">
                                Stok: {{ $item->stok }}
                            </p>
                        </div>

                        <form action="{{ route('keranjang.tambah') }}" method="POST" class="mt-4">
                            @csrf
                            <input type="hidden" name="id_produk" value="{{ $item->id_produk }}">
                            <button
                                type="submit"
                                @disabled($item->stok <= 0)
                                class="w-full py-2 px-3 rounded-md text-sm font-semibold transition {{ $item->stok > 0 ? 'bg-[#A8380F] hover:bg-[#8E2F0C] text-white' : 'bg-stone-200 text-stone-400 cursor-not-allowed' }}"
                            >
                                {{ $item->stok > 0 ? '+ Keranjang' : 'Stok Habis' }}
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    {{-- KANAN: Panel Keranjang Belanja (Akan diaktifkan di Minggu 2) --}}
    <aside id="keranjang" class="lg:col-span-3 bg-[#FAF3EF] border border-stone-200 rounded-xl p-5 shadow-sm">
        <h2 class="font-bold text-stone-800 mb-3">Keranjang Belanja</h2>
        <p class="text-sm text-stone-500">
            Keranjang masih kosong. Fitur keranjang &amp; checkout transaksi akan melengkapi panel ini pada Minggu 2.
        </p>
    </aside>

</div>
@endsection