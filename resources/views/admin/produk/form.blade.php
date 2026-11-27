@extends('layouts.admin')

@section('title', ($produk ? 'Ubah' : 'Tambah') . ' Produk — TokoKita Admin')

@section('content')
<div class="max-w-xl bg-white border border-stone-200 rounded-xl p-6 shadow-sm">
    <h1 class="text-xl font-bold text-stone-800 mb-1">
        {{ $produk ? "Ubah Produk ({$produk->id_produk})" : 'Tambah Produk Baru' }}
    </h1>
    <p class="text-sm text-stone-500 mb-5">
        Pastikan seluruh kolom diisi dengan benar. Harga pada pesanan lama tidak akan ikut berubah.
    </p>

    <form action="{{ $produk ? route('admin.produk.update', $produk->id_produk) : route('admin.produk.store') }}"
          method="POST" class="space-y-4">
        @csrf
        @if($produk)
            @method('PUT')
        @endif

        <div>
            <label for="nama_produk" class="block text-sm font-medium text-stone-700 mb-1">Nama Produk</label>
            <input type="text" id="nama_produk" name="nama_produk" required maxlength="50"
                   value="{{ old('nama_produk', $produk->nama_produk ?? '') }}"
                   class="w-full border border-stone-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#A8380F]">
        </div>

        <div>
            <label for="kategori" class="block text-sm font-medium text-stone-700 mb-1">Kategori</label>
            @php $katTerpilih = old('kategori', $produk->kategori ?? ''); @endphp
            <select id="kategori" name="kategori" required
                    class="w-full border border-stone-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-[#A8380F]">
                <option value="">-- Pilih Kategori --</option>
                @foreach(['Elektronik', 'Fashion', 'Alat Tulis'] as $opsi)
                    <option value="{{ $opsi }}" {{ $katTerpilih === $opsi ? 'selected' : '' }}>{{ $opsi }}</option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="harga" class="block text-sm font-medium text-stone-700 mb-1">Harga (Rp)</label>
                <input type="number" id="harga" name="harga" required min="1" step="1"
                       value="{{ old('harga', isset($produk) ? (int) $produk->harga : '') }}"
                       class="w-full border border-stone-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#A8380F]">
            </div>
            <div>
                <label for="stok" class="block text-sm font-medium text-stone-700 mb-1">Stok</label>
                <input type="number" id="stok" name="stok" required min="0" step="1"
                       value="{{ old('stok', $produk->stok ?? 0) }}"
                       class="w-full border border-stone-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#A8380F]">
            </div>
        </div>

        <div class="pt-3 flex items-center gap-2">
            <button type="submit"
                    class="bg-[#A8380F] hover:bg-[#8E2F0C] text-white text-sm font-semibold px-5 py-2 rounded-lg transition">
                Simpan
            </button>
            <a href="{{ route('admin.produk.index') }}"
               class="px-4 py-2 text-sm border border-stone-300 rounded-lg text-stone-700 hover:bg-stone-100 transition">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection