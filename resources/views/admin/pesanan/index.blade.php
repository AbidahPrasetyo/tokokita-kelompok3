@extends('layouts.admin')

@section('title', 'Manajemen Pesanan — TokoKita Admin')

@section('content')
<div class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-bold text-stone-800">Manajemen Pesanan</h1>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-700">3b</span>
            </div>
            <p class="text-sm text-stone-500">
                Daftar seluruh transaksi beserta total nilai berdasarkan <code>detail_pesanan.harga_satuan</code> (mencakup seluruh status).
            </p>
        </div>
    </div>

    {{-- Bar Filter Status, Filter 3j & Pencarian --}}
    <div class="bg-white border border-stone-200 rounded-xl p-4 shadow-sm flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2 text-sm">
            <a href="{{ route('admin.pesanan.index', ['cari' => $cari]) }}"
               class="px-3 py-1.5 rounded-lg font-medium transition {{ !$hanyaLebihDariTiga && !$status ? 'bg-[#A8380F] text-white' : 'bg-stone-100 text-stone-700 hover:bg-stone-200' }}">
                Semua Pesanan
            </a>

            @foreach(['diproses', 'dikirim', 'selesai', 'batal'] as $st)
                <a href="{{ route('admin.pesanan.index', ['status' => $st, 'cari' => $cari]) }}"
                   class="px-3 py-1.5 rounded-lg font-medium capitalize transition {{ $status === $st && !$hanyaLebihDariTiga ? 'bg-[#A8380F] text-white' : 'bg-stone-100 text-stone-700 hover:bg-stone-200' }}">
                    {{ $st }}
                </a>
            @endforeach

            <a href="{{ route('admin.pesanan.index', ['filter' => 'multi_produk', 'cari' => $cari]) }}"
               class="px-3 py-1.5 rounded-lg font-medium transition flex items-center gap-1.5 {{ $hanyaLebihDariTiga ? 'bg-[#A8380F] text-white' : 'bg-stone-100 text-stone-700 hover:bg-stone-200' }}">
                <span>&gt; 3 Jenis Produk ({{ $jumlahMultiProduk }})</span>
                <span class="text-[11px] font-bold px-1.5 py-0.5 rounded {{ $hanyaLebihDariTiga ? 'bg-white/20 text-white' : 'bg-red-100 text-red-700' }}">3j</span>
            </a>
        </div>

        <form action="{{ route('admin.pesanan.index') }}" method="GET" class="flex items-center gap-2">
            @if($status) <input type="hidden" name="status" value="{{ $status }}"> @endif
            @if($hanyaLebihDariTiga) <input type="hidden" name="filter" value="multi_produk"> @endif
            <input type="text" name="cari" value="{{ $cari }}" placeholder="Cari ID pesanan / nama..."
                   class="border border-stone-300 rounded-lg px-3 py-1.5 text-sm w-56 focus:outline-none focus:ring-1 focus:ring-[#A8380F]">
            <button type="submit" class="bg-stone-800 hover:bg-stone-700 text-white text-sm px-3.5 py-1.5 rounded-lg">
                Cari
            </button>
        </form>
    </div>

    {{-- Tabel Daftar Pesanan --}}
    <div class="bg-white border border-stone-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-stone-50 border-b border-stone-200 text-stone-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">ID Pesanan</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Pelanggan</th>
                        <th class="px-4 py-3 text-center">Jenis Produk</th>
                        <th class="px-4 py-3 text-right">Total Nilai</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($pesanan as $item)
                        @php
                            $badgeClass = match ($item->status) {
                                'diproses' => 'bg-amber-100 text-amber-800',
                                'dikirim'  => 'bg-sky-100 text-sky-800',
                                'selesai'  => 'bg-emerald-100 text-emerald-800',
                                'batal'    => 'bg-red-100 text-red-800',
                                default    => 'bg-stone-100 text-stone-700',
                            };
                        @endphp
                        <tr class="hover:bg-stone-50">
                            <td class="px-4 py-3 font-mono font-bold text-stone-800">#{{ $item->id_pesanan }}</td>
                            <td class="px-4 py-3 text-stone-600">{{ format_tanggal($item->tgl_pesan) }}</td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-stone-800">{{ $item->nama_pelanggan }}</p>
                                <p class="text-xs text-stone-500">{{ $item->id_pelanggan }} &middot; {{ $item->kota }}</p>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="font-semibold">{{ $item->jumlah_jenis }} jenis</span>
                                @if($item->jumlah_jenis > 3)
                                    <span class="ml-1 text-[11px] font-bold px-1.5 py-0.5 rounded bg-red-100 text-red-700">3j</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-stone-800">
                                {{ format_rupiah($item->total_nilai) }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-full capitalize {{ $badgeClass }}">
                                    {{ $item->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('admin.pesanan.show', $item->id_pesanan) }}"
                                       class="px-3 py-1 text-xs font-semibold bg-[#A8380F] hover:bg-[#8E2F0C] text-white rounded-md transition">
                                        Detail
                                    </a>
                                    <a href="{{ route('admin.pesanan.invoice', $item->id_pesanan) }}" target="_blank"
                                       class="px-2.5 py-1 text-xs font-medium border border-stone-300 rounded-md hover:bg-stone-100 transition">
                                        Invoice
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-stone-500">
                                Tidak ada data pesanan yang sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-stone-200 bg-stone-50 text-xs text-stone-500">
            Menampilkan {{ count($pesanan) }} pesanan. Total nilai dihitung dari <code>SUM(jumlah * harga_satuan)</code> untuk seluruh status yang ditampilkan.
        </div>
    </div>
</div>
@endsection