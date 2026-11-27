@extends('layouts.admin')

@section('title', "Detail Pesanan #{$pesanan->id_pesanan} — TokoKita Admin")

@section('content')
@php
    $badgeClass = match ($pesanan->status) {
        'diproses' => 'bg-amber-100 text-amber-800',
        'dikirim'  => 'bg-sky-100 text-sky-800',
        'selesai'  => 'bg-emerald-100 text-emerald-800',
        'batal'    => 'bg-red-100 text-red-800',
        default    => 'bg-stone-100 text-stone-700',
    };
@endphp

<div class="space-y-5 max-w-5xl">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('admin.pesanan.index') }}" class="text-xs text-[#A8380F] hover:underline">&larr; Kembali ke Daftar Pesanan</a>
            <div class="flex items-center gap-3 mt-1">
                <h1 class="text-2xl font-bold text-stone-800">Pesanan #{{ $pesanan->id_pesanan }}</h1>
                <span class="text-xs font-semibold px-3 py-1 rounded-full capitalize {{ $badgeClass }}">
                    {{ $pesanan->status }}
                </span>
            </div>
        </div>

        <a href="{{ route('admin.pesanan.invoice', $pesanan->id_pesanan) }}" target="_blank"
           class="bg-stone-800 hover:bg-stone-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
            Cetak Invoice
        </a>
    </div>

    {{-- Informasi Master Pesanan & Panel Ubah Status (T12) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        <div class="lg:col-span-7 bg-white border border-stone-200 rounded-xl p-5 shadow-sm space-y-3 text-sm">
            <h2 class="font-bold text-stone-800 border-b border-stone-100 pb-2">Informasi Pesanan</h2>
            <div class="grid grid-cols-2 gap-y-2">
                <span class="text-stone-500">Tanggal Pesan</span>
                <span class="font-medium text-stone-800">{{ format_tanggal($pesanan->tgl_pesan) }}</span>

                <span class="text-stone-500">Pelanggan</span>
                <span class="font-medium text-stone-800">{{ $pesanan->nama_pelanggan }} ({{ $pesanan->id_pelanggan }})</span>

                <span class="text-stone-500">Email &amp; Kota</span>
                <span class="text-stone-700">{{ $pesanan->email_pelanggan }} &middot; {{ $pesanan->kota_pelanggan }}</span>

                <span class="text-stone-500">Jumlah Jenis Produk</span>
                <span class="font-semibold text-stone-800">
                    {{ $pesanan->jumlah_jenis }} jenis ({{ $pesanan->total_unit }} unit)
                    @if($pesanan->jumlah_jenis > 3)
                        <span class="ml-1 text-[11px] font-bold px-1.5 py-0.5 rounded bg-red-100 text-red-700">Soal 3j (&gt;3 jenis)</span>
                    @endif
                </span>
            </div>
        </div>

        {{-- Panel Ubah Status Sesuai Diagram Siklus (T12) --}}
        <div class="lg:col-span-5 bg-[#FAF3EF] border border-stone-200 rounded-xl p-5 shadow-sm flex flex-col justify-between">
            <div>
                <h2 class="font-bold text-stone-800 mb-1">Perbarui Status Pesanan</h2>
                <p class="text-xs text-stone-500 mb-4">
                    Alur sah: <strong>diproses &rarr; dikirim &rarr; selesai</strong>. Pembatalan (<strong>batal</strong>) hanya diizinkan dari status <strong>diproses</strong>.
                </p>

                @if(empty($statusBerikutnya))
                    <div class="px-3 py-2.5 rounded-lg bg-stone-200/80 text-stone-700 text-xs font-medium">
                        Status <strong>{{ $pesanan->status }}</strong> bersifat final dan terkunci permanen.
                    </div>
                @else
                    <form action="{{ route('admin.pesanan.status', $pesanan->id_pesanan) }}" method="POST" class="space-y-3">
                        @csrf
                        @method('PATCH')
                        <select name="status" required
                                class="w-full border border-stone-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-[#A8380F]">
                            @foreach($statusBerikutnya as $opsi)
                                <option value="{{ $opsi }}">Ubah ke: {{ ucfirst($opsi) }}</option>
                            @endforeach
                        </select>
                        <button type="submit"
                                onclick="return confirm('Simpan perubahan status pesanan #{{ $pesanan->id_pesanan }}?')"
                                class="w-full bg-[#A8380F] hover:bg-[#8E2F0C] text-white text-sm font-semibold py-2 rounded-lg transition">
                            Simpan Status
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Tabel Item Detail Pesanan --}}
    <div class="bg-white border border-stone-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-stone-200">
            <h2 class="font-bold text-stone-800 text-sm">Rincian Produk Dipesan</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-stone-50 border-b border-stone-200 text-xs uppercase text-stone-500">
                    <tr>
                        <th class="px-4 py-3">Kode</th>
                        <th class="px-4 py-3">Nama Produk</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3 text-right">Harga Satuan</th>
                        <th class="px-4 py-3 text-center">Jumlah</th>
                        <th class="px-4 py-3 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach($items as $row)
                        <tr>
                            <td class="px-4 py-3 font-mono text-stone-600">{{ $row->id_produk }}</td>
                            <td class="px-4 py-3 font-medium text-stone-800">{{ $row->nama_produk }}</td>
                            <td class="px-4 py-3 text-stone-600">{{ $row->kategori }}</td>
                            <td class="px-4 py-3 text-right">{{ format_rupiah($row->harga_satuan) }}</td>
                            <td class="px-4 py-3 text-center">{{ $row->jumlah }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-stone-800">{{ format_rupiah($row->subtotal) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-stone-50 border-t-2 border-stone-200">
                    <tr>
                        <td colspan="5" class="px-4 py-3 text-right font-bold text-stone-800">Total Transaksi</td>
                        <td class="px-4 py-3 text-right font-bold text-lg text-[#A8380F]">{{ format_rupiah($pesanan->total_nilai) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection