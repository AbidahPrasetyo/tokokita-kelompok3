@extends('layouts.admin')

@section('title', 'Manajemen Pelanggan — TokoKita Admin')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-stone-800">Manajemen Pelanggan</h1>
            <p class="text-sm text-stone-500">Kelola data pelanggan, pantau sebaran kota, pelanggan prioritas 2026, dan target promo.</p>
        </div>
        <button type="button" onclick="bukaModalTambah()"
                class="bg-[#A8380F] hover:bg-[#8E2F0C] text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
            + Tambah Pelanggan
        </button>
    </div>

    {{-- 3 Panel Analitik Pelanggan (Soal 3d, 3i, 3f) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Panel Soal 3d: Pelanggan Prioritas 2026 > Rp10 jt --}}
        <div class="bg-white border border-stone-200 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-bold text-stone-800 text-sm">Pelanggan Prioritas 2026 (&gt; Rp10 jt)</h2>
                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-700">3d</span>
            </div>
            @forelse($prioritas as $pr)
                <div class="py-2 border-b border-stone-100 last:border-0 flex items-center justify-between text-sm">
                    <div>
                        <p class="font-semibold text-stone-800">{{ $pr->nama }}</p>
                        <p class="text-xs text-stone-500">{{ $pr->kota }}</p>
                    </div>
                    <span class="font-bold text-emerald-700">{{ format_rupiah($pr->total_belanja) }}</span>
                </div>
            @empty
                <p class="text-xs text-stone-500">Belum ada pelanggan dengan total belanja &gt; Rp10 juta.</p>
            @endforelse
        </div>

        {{-- Panel Soal 3i: Belum Pernah Memesan --}}
        <div class="bg-white border border-stone-200 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-bold text-stone-800 text-sm">Belum Pernah Memesan (Target Promo)</h2>
                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-700">3i</span>
            </div>
            @forelse($belumPesan as $bp)
                <div class="py-2 border-b border-stone-100 last:border-0 text-sm">
                    <p class="font-semibold text-stone-800">{{ $bp->nama }} <span class="text-xs font-mono text-stone-400">({{ $bp->id_pelanggan }})</span></p>
                    <p class="text-xs text-stone-500">{{ $bp->email }} &middot; {{ $bp->kota }}</p>
                </div>
            @empty
                <p class="text-xs text-stone-500">Seluruh pelanggan sudah pernah melakukan pesanan.</p>
            @endforelse
        </div>

        {{-- Panel Soal 3f: Sebaran Pelanggan per Kota --}}
        <div class="bg-white border border-stone-200 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-bold text-stone-800 text-sm">Sebaran Pelanggan per Kota</h2>
                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-700">3f</span>
            </div>
            <div class="space-y-1.5 text-sm">
                @foreach($sebaranKota as $sk)
                    <div class="flex items-center justify-between border-b border-stone-100 pb-1 last:border-0">
                        <span class="text-stone-700">{{ $sk->kota }}</span>
                        <span class="font-bold text-stone-800">{{ $sk->jumlah_pelanggan }} orang</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Pencarian & Tabel CRUD Pelanggan --}}
    <div class="bg-white border border-stone-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-stone-200 flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-bold text-stone-800 text-sm">Daftar Seluruh Pelanggan ({{ count($pelanggan) }})</h2>
            <form action="{{ route('admin.pelanggan.index') }}" method="GET" class="flex items-center gap-2">
                <input type="text" name="cari" value="{{ $cari }}" placeholder="Cari nama, email, kota..."
                       class="border border-stone-300 rounded-lg px-3 py-1.5 text-sm w-56 focus:outline-none focus:ring-1 focus:ring-[#A8380F]">
                <button type="submit" class="bg-stone-800 hover:bg-stone-700 text-white text-sm px-3.5 py-1.5 rounded-lg">Cari</button>
                @if($cari !== '')
                    <a href="{{ route('admin.pelanggan.index') }}" class="text-xs text-stone-500 hover:underline">Reset</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-stone-50 border-b border-stone-200 text-xs uppercase text-stone-600">
                    <tr>
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">Nama Pelanggan</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Kota</th>
                        <th class="px-4 py-3 text-center">Jumlah Pesanan</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($pelanggan as $pl)
                        <tr class="hover:bg-stone-50">
                            <td class="px-4 py-3 font-mono font-semibold text-stone-700">{{ $pl->id_pelanggan }}</td>
                            <td class="px-4 py-3 font-medium text-stone-800">{{ $pl->nama }}</td>
                            <td class="px-4 py-3 text-stone-600">{{ $pl->email }}</td>
                            <td class="px-4 py-3 text-stone-600">{{ $pl->kota }}</td>
                            <td class="px-4 py-3 text-center">
                                @if((int) $pl->jumlah_pesanan === 0)
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">Belum pernah pesan</span>
                                @else
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700">{{ $pl->jumlah_pesanan }} pesanan</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button"
                                            data-url="{{ route('admin.pelanggan.update', $pl->id_pelanggan) }}"
                                            data-id="{{ $pl->id_pelanggan }}"
                                            data-nama="{{ $pl->nama }}"
                                            data-email="{{ $pl->email }}"
                                            data-kota="{{ $pl->kota }}"
                                            onclick="bukaModalUbah(this)"
                                            class="px-2.5 py-1 text-xs font-medium border border-stone-300 rounded-md hover:bg-stone-100">
                                        Ubah
                                    </button>
                                    <button type="button"
                                            data-url="{{ route('admin.pelanggan.destroy', $pl->id_pelanggan) }}"
                                            data-nama="{{ $pl->nama }}"
                                            onclick="bukaModalHapus(this)"
                                            class="px-2.5 py-1 text-xs font-medium border border-red-300 text-red-700 rounded-md hover:bg-red-50">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-stone-500">Data pelanggan tidak ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Tambah & Ubah Pelanggan --}}
<div id="modalFormPelanggan" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl">
        <h3 id="judulModalPelanggan" class="text-lg font-bold text-stone-800 mb-4">Tambah Pelanggan</h3>
        <form id="formPelanggan" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="_method" id="methodPelanggan" value="POST">

            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Nama Lengkap</label>
                <input type="text" name="nama" id="inputNama" required maxlength="50"
                       class="w-full border border-stone-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#A8380F]">
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Email</label>
                <input type="email" name="email" id="inputEmail" required maxlength="60"
                       class="w-full border border-stone-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#A8380F]">
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Kota</label>
                <input type="text" name="kota" id="inputKota" required maxlength="30"
                       class="w-full border border-stone-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#A8380F]">
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="tutupModal('modalFormPelanggan')"
                        class="px-4 py-2 text-sm border border-stone-300 rounded-lg text-stone-700 hover:bg-stone-100">
                    Batal
                </button>
                <button type="submit"
                        class="px-4 py-2 text-sm font-semibold bg-[#A8380F] hover:bg-[#8E2F0C] text-white rounded-lg">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Konfirmasi Hapus Pelanggan --}}
<div id="modalHapusPelanggan" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl">
        <h3 class="text-lg font-bold text-stone-800">Konfirmasi Hapus Pelanggan</h3>
        <p class="text-sm text-stone-600 mt-2">
            Hapus data pelanggan <strong id="namaPelangganHapus" class="text-stone-900"></strong>?
        </p>
        <div class="mt-5 flex justify-end gap-2">
            <button type="button" onclick="tutupModal('modalHapusPelanggan')"
                    class="px-4 py-2 text-sm border border-stone-300 rounded-lg text-stone-700 hover:bg-stone-100">
                Batal
            </button>
            <form id="formHapusPelanggan" method="POST">
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
    function bukaModalTambah() {
        document.getElementById('judulModalPelanggan').textContent = 'Tambah Pelanggan Baru';
        document.getElementById('formPelanggan').action = "{{ route('admin.pelanggan.store') }}";
        document.getElementById('methodPelanggan').value = 'POST';
        document.getElementById('inputNama').value = '';
        document.getElementById('inputEmail').value = '';
        document.getElementById('inputKota').value = '';
        tampilkanModal('modalFormPelanggan');
    }

    function bukaModalUbah(btn) {
        document.getElementById('judulModalPelanggan').textContent = 'Ubah Pelanggan (' + btn.dataset.id + ')';
        document.getElementById('formPelanggan').action = btn.dataset.url;
        document.getElementById('methodPelanggan').value = 'PUT';
        document.getElementById('inputNama').value = btn.dataset.nama;
        document.getElementById('inputEmail').value = btn.dataset.email;
        document.getElementById('inputKota').value = btn.dataset.kota;
        tampilkanModal('modalFormPelanggan');
    }

    function bukaModalHapus(btn) {
        document.getElementById('formHapusPelanggan').action = btn.dataset.url;
        document.getElementById('namaPelangganHapus').textContent = btn.dataset.nama;
        tampilkanModal('modalHapusPelanggan');
    }

    function tampilkanModal(id) {
        const el = document.getElementById(id);
        el.classList.remove('hidden');
        el.classList.add('flex');
    }

    function tutupModal(id) {
        const el = document.getElementById(id);
        el.classList.add('hidden');
        el.classList.remove('flex');
    }
</script>
@endpush