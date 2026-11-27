@extends('layouts.admin')

@section('title', 'Manajemen Produk — TokoKita Admin')

@section('content')
<div class="space-y-5">
    {{-- Header Halaman --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-stone-800">Manajemen Produk</h1>
            <p class="text-sm text-stone-500">Kelola katalog produk, stok, status aktif, serta pantau produk yang belum pernah dipesan.</p>
        </div>
        <a href="{{ route('admin.produk.create') }}"
           class="bg-[#A8380F] hover:bg-[#8E2F0C] text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
            + Tambah Produk
        </a>
    </div>

    {{-- Bar Filter & Pencarian --}}
    <div class="bg-white border border-stone-200 rounded-xl p-4 shadow-sm flex flex-wrap items-center justify-between gap-3">
        {{-- Tab Filter Semua vs Soal 3e --}}
        <div class="flex items-center gap-2 text-sm">
            <a href="{{ route('admin.produk.index', ['cari' => $cari]) }}"
               class="px-3 py-1.5 rounded-lg font-medium transition {{ !$hanyaBelumDipesan ? 'bg-[#A8380F] text-white' : 'bg-stone-100 text-stone-700 hover:bg-stone-200' }}">
                Semua Produk
            </a>
            <a href="{{ route('admin.produk.index', ['filter' => 'belum_dipesan', 'cari' => $cari]) }}"
               class="px-3 py-1.5 rounded-lg font-medium transition flex items-center gap-1.5 {{ $hanyaBelumDipesan ? 'bg-[#A8380F] text-white' : 'bg-stone-100 text-stone-700 hover:bg-stone-200' }}">
                <span>Belum Pernah Dipesan ({{ $jumlahTakLaku }})</span>
                <span class="text-[11px] font-bold px-1.5 py-0.5 rounded {{ $hanyaBelumDipesan ? 'bg-white/20 text-white' : 'bg-red-100 text-red-700' }}">3e</span>
            </a>
        </div>

        {{-- Form Pencarian --}}
        <form action="{{ route('admin.produk.index') }}" method="GET" class="flex items-center gap-2">
            @if($hanyaBelumDipesan)
                <input type="hidden" name="filter" value="belum_dipesan">
            @endif
            <input
                type="text"
                name="cari"
                value="{{ $cari }}"
                placeholder="Cari ID, nama, atau kategori..."
                class="border border-stone-300 rounded-lg px-3 py-1.5 text-sm w-60 focus:outline-none focus:ring-1 focus:ring-[#A8380F]"
            >
            <button type="submit" class="bg-stone-800 hover:bg-stone-700 text-white text-sm px-3.5 py-1.5 rounded-lg transition">
                Cari
            </button>
            @if($cari !== '')
                <a href="{{ route('admin.produk.index', $hanyaBelumDipesan ? ['filter' => 'belum_dipesan'] : []) }}"
                   class="text-xs text-stone-500 hover:underline px-1">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Tabel Produk --}}
    <div class="bg-white border border-stone-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-stone-50 border-b border-stone-200 text-stone-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">Nama Produk</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3 text-right">Harga</th>
                        <th class="px-4 py-3 text-center">Stok</th>
                        <th class="px-4 py-3">Status &amp; Penanda</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($produk as $item)
                        <tr class="hover:bg-stone-50 {{ !$item->is_aktif ? 'opacity-60 bg-stone-50/70' : '' }}">
                            <td class="px-4 py-3 font-mono font-semibold text-stone-700">{{ $item->id_produk }}</td>
                            <td class="px-4 py-3 font-medium text-stone-800">{{ $item->nama_produk }}</td>
                            <td class="px-4 py-3 text-stone-600">{{ $item->kategori }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-stone-800">{{ format_rupiah($item->harga) }}</td>
                            <td class="px-4 py-3 text-center">{{ $item->stok }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1.5">
                                    @if((int) $item->jumlah_transaksi === 0)
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">
                                            Belum pernah dipesan
                                        </span>
                                    @else
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700">
                                            Dipesan {{ $item->jumlah_transaksi }}&times;
                                        </span>
                                    @endif

                                    @if(!$item->is_aktif)
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-stone-200 text-stone-700">
                                            Nonaktif
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('admin.produk.edit', $item->id_produk) }}"
                                       class="px-2.5 py-1 text-xs font-medium border border-stone-300 rounded-md hover:bg-stone-100 transition">
                                        Ubah
                                    </a>

                                    {{-- Opsi Nonaktifkan / Aktifkan --}}
                                    <form action="{{ route('admin.produk.toggle', $item->id_produk) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="px-2.5 py-1 text-xs font-medium border rounded-md transition {{ $item->is_aktif ? 'border-amber-300 text-amber-700 hover:bg-amber-50' : 'border-emerald-300 text-emerald-700 hover:bg-emerald-50' }}">
                                            {{ $item->is_aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>

                                    {{-- Tombol Hapus (Membuka Modal Konfirmasi) --}}
                                    <button
                                        type="button"
                                        onclick="bukaModalHapus('{{ route('admin.produk.destroy', $item->id_produk) }}', '{{ addslashes($item->nama_produk) }}')"
                                        class="px-2.5 py-1 text-xs font-medium border border-red-300 text-red-700 rounded-md hover:bg-red-50 transition">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-stone-500">
                                Tidak ada data produk yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer Info & Paginasi (> 10 baris) --}}
        <div class="px-4 py-3 border-t border-stone-200 bg-stone-50 flex flex-wrap items-center justify-between gap-2 text-xs text-stone-600">
            <span>Menampilkan {{ $produk->count() }} dari {{ $produk->total() }} produk</span>
            @if($produk->hasPages())
                <div>{{ $produk->links() }}</div>
            @endif
        </div>
    </div>
</div>

{{-- Modal Konfirmasi Hapus --}}
<div id="modalHapus" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl">
        <h3 class="text-lg font-bold text-stone-800">Konfirmasi Hapus Produk</h3>
        <p class="text-sm text-stone-600 mt-2">
            Apakah Anda yakin ingin menghapus produk <strong id="namaProdukHapus" class="text-stone-900"></strong>?
            Tindakan ini tidak dapat dibatalkan.
        </p>
        <div class="mt-5 flex justify-end gap-2">
            <button type="button" onclick="tutupModalHapus()"
                    class="px-4 py-2 text-sm border border-stone-300 rounded-lg text-stone-700 hover:bg-stone-100">
                Batal
            </button>
            <form id="formHapusProduk" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="px-4 py-2 text-sm font-semibold bg-red-600 hover:bg-red-700 text-white rounded-lg">
                    Ya, Hapus
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function bukaModalHapus(actionUrl, namaProduk) {
        document.getElementById('formHapusProduk').action = actionUrl;
        document.getElementById('namaProdukHapus').textContent = namaProduk;
        const modal = document.getElementById('modalHapus');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function tutupModalHapus() {
        const modal = document.getElementById('modalHapus');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@endpush