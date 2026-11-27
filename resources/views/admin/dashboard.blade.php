@extends('layouts.admin')

@section('title', 'Dashboard Penjualan — TokoKita Admin')

@section('content')
<div class="space-y-6">

    {{-- Header Dashboard & Pemilih Tahun --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-stone-800">Dashboard Penjualan {{ $tahun }}</h1>
            <p class="text-sm text-stone-500">
                Ringkasan performa transaksi TokoKita berdasarkan <code>detail_pesanan.harga_satuan</code> (harga historis).
            </p>
        </div>

        <form action="{{ route('admin.dashboard') }}" method="GET" class="flex items-center gap-2 text-sm">
            <label for="tahun" class="text-stone-600 font-medium">Tahun Laporan:</label>
            <select id="tahun" name="tahun" onchange="this.form.submit()"
                    class="border border-stone-300 rounded-lg px-3 py-1.5 bg-white text-stone-800 focus:outline-none focus:ring-1 focus:ring-[#A8380F]">
                @foreach([2025, 2026, 2027] as $opsiTahun)
                    <option value="{{ $opsiTahun }}" {{ $tahun === $opsiTahun ? 'selected' : '' }}>
                        {{ $opsiTahun }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- BARIS 1: 4 Kartu Ringkasan --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('admin.pesanan.index') }}"
           class="bg-white border border-stone-200 rounded-xl p-4 shadow-sm hover:border-[#A8380F]/50 transition block">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wide text-stone-500">Jumlah Pesanan</p>
                <span class="text-[11px] font-bold px-1.5 py-0.5 rounded bg-stone-100 text-stone-600">3b</span>
            </div>
            <p class="text-2xl font-bold text-[#A8380F] mt-1.5">{{ $ringkasan['jumlah_pesanan'] }}</p>
            <p class="text-[11px] text-stone-400 mt-1">Seluruh status pesanan</p>
        </a>

        <div class="bg-white border border-stone-200 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wide text-stone-500">Pendapatan {{ $tahun }}</p>
                <span class="text-[11px] font-bold px-1.5 py-0.5 rounded bg-red-100 text-red-700">3h</span>
            </div>
            <p class="text-2xl font-bold text-emerald-700 mt-1.5" title="{{ format_rupiah($ringkasan['total_pendapatan']) }}">
                {{ format_rupiah_singkat($ringkasan['total_pendapatan']) }}
            </p>
            <p class="text-[11px] text-stone-400 mt-1">{{ format_rupiah($ringkasan['total_pendapatan']) }} (tanpa batal)</p>
        </div>

        <a href="{{ route('admin.pelanggan.index') }}"
           class="bg-white border border-stone-200 rounded-xl p-4 shadow-sm hover:border-[#A8380F]/50 transition block">
            <p class="text-xs font-semibold uppercase tracking-wide text-stone-500">Total Pelanggan</p>
            <p class="text-2xl font-bold text-sky-700 mt-1.5">{{ $ringkasan['total_pelanggan'] }}</p>
            <p class="text-[11px] text-stone-400 mt-1">Pelanggan terdaftar</p>
        </a>

        <a href="{{ route('admin.produk.index', ['filter' => 'belum_dipesan']) }}"
           class="bg-white border border-stone-200 rounded-xl p-4 shadow-sm hover:border-red-400 transition block">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wide text-stone-500">Produk Tak Laku</p>
                <span class="text-[11px] font-bold px-1.5 py-0.5 rounded bg-red-100 text-red-700">3e</span>
            </div>
            <p class="text-2xl font-bold text-red-700 mt-1.5">{{ $ringkasan['produk_tak_laku'] }}</p>
            <p class="text-[11px] text-stone-400 mt-1">Belum pernah dipesan &rarr;</p>
        </a>
    </div>

    {{-- BARIS 2: Grafik Pendapatan Bulanan (3h) & Tabel 5 Produk Terlaris (3c) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        {{-- Grafik Batang Pendapatan Bulanan (Soal 3h) --}}
        <div class="lg:col-span-7 bg-white border border-stone-200 rounded-xl p-5 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h2 class="font-bold text-stone-800">Pendapatan per Bulan ({{ $tahun }})</h2>
                        <p class="text-xs text-stone-500">Akumulasi nilai transaksi per bulan aktif</p>
                    </div>
                    <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-red-100 text-red-700">3h</span>
                </div>

                @if(count($pendapatanBulanan) === 0)
                    <div class="h-64 flex items-center justify-center text-sm text-stone-400">
                        Belum ada data pendapatan pada tahun {{ $tahun }}.
                    </div>
                @else
                    <div class="h-64">
                        <canvas id="chartPendapatanBulanan"></canvas>
                    </div>
                @endif
            </div>

            {{-- Keterangan Definisi Status Laporan 3h (T19) --}}
            <div class="mt-4 pt-3 border-t border-stone-100 text-xs text-stone-600 bg-stone-50 rounded-lg px-3 py-2">
                <strong>Cakupan Status (Soal 3h):</strong> Menghitung pesanan berstatus
                <span class="font-semibold text-stone-800">diproses, dikirim, dan selesai</span>
                (pesanan berstatus <span class="font-semibold text-red-700">batal</span> tidak dihitung). Nominal memakai <code>detail_pesanan.harga_satuan</code>.
            </div>
        </div>

        {{-- Tabel 5 Produk Terlaris (Soal 3c) --}}
        <div class="lg:col-span-5 bg-white border border-stone-200 rounded-xl p-5 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h2 class="font-bold text-stone-800">5 Produk Terlaris</h2>
                        <p class="text-xs text-stone-500">Urutan berdasarkan total unit terjual</p>
                    </div>
                    <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-red-100 text-red-700">3c</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="border-b border-stone-200 text-xs text-stone-500 uppercase bg-stone-50">
                            <tr>
                                <th class="py-2.5 px-2 w-10 text-center">#</th>
                                <th class="py-2.5 px-2">Nama Produk</th>
                                <th class="py-2.5 px-2 text-right">Terjual</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @forelse($produkTerlaris as $idx => $item)
                                <tr class="hover:bg-stone-50">
                                    <td class="py-2.5 px-2 text-center font-semibold text-stone-500">{{ $idx + 1 }}</td>
                                    <td class="py-2.5 px-2 font-medium text-stone-800">{{ $item->nama_produk }}</td>
                                    <td class="py-2.5 px-2 text-right font-bold text-[#A8380F]">{{ $item->total_terjual }} unit</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-6 text-center text-stone-400">Belum ada data penjualan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-stone-100 text-xs text-stone-500">
                Dihitung dari total <code>SUM(detail_pesanan.jumlah)</code> per produk (maksimal 5 produk teratas).
            </div>
        </div>
    </div>

    {{-- BARIS 3: Grafik Pendapatan per Kategori (3g) + Panel Pelanggan (3d & 3i) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        {{-- Grafik Pendapatan per Kategori (Soal 3g) --}}
        <div class="lg:col-span-7 bg-white border border-stone-200 rounded-xl p-5 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h2 class="font-bold text-stone-800">Pendapatan per Kategori</h2>
                        <p class="text-xs text-stone-500">Perbandingan realisasi pendapatan tiap kategori produk</p>
                    </div>
                    <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-red-100 text-red-700">3g</span>
                </div>

                @if(count($pendapatanKategori) === 0)
                    <div class="h-48 flex items-center justify-center text-sm text-stone-400">
                        Belum ada pesanan berstatus selesai.
                    </div>
                @else
                    <div class="h-52">
                        <canvas id="chartPendapatanKategori"></canvas>
                    </div>

                    {{-- Ringkasan Angka Pasti per Kategori dalam Format Rupiah --}}
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        @foreach($pendapatanKategori as $kat)
                            <div class="border border-stone-200 rounded-lg px-3 py-2 bg-stone-50/70">
                                <p class="text-xs text-stone-500">{{ $kat->kategori }}</p>
                                <p class="text-sm font-bold text-[#A8380F] mt-0.5">{{ format_rupiah($kat->pendapatan) }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Keterangan Definisi Status Laporan 3g (T19) --}}
            <div class="mt-4 pt-3 border-t border-stone-100 text-xs text-stone-600 bg-stone-50 rounded-lg px-3 py-2">
                <strong>Cakupan Status (Soal 3g):</strong> Hanya menghitung pesanan berstatus
                <span class="font-semibold text-emerald-700">selesai</span>
                (status <em>diproses</em>, <em>dikirim</em>, dan <em>batal</em> tidak dihitung). Nominal memakai <code>detail_pesanan.harga_satuan</code>.
            </div>
        </div>

        {{-- Kolom Kanan: Panel Pelanggan Prioritas (3d) & Belum Pernah Memesan (3i) --}}
        <div class="lg:col-span-5 space-y-5">
            {{-- Panel Pelanggan Prioritas (Soal 3d) --}}
            <div class="bg-white border border-stone-200 rounded-xl p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h2 class="font-bold text-stone-800 text-sm">Pelanggan Prioritas {{ $tahun }} (&gt; Rp10 jt)</h2>
                        <p class="text-xs text-stone-500">Total belanja di atas Rp10.000.000</p>
                    </div>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-700">3d</span>
                </div>

                @forelse($pelangganPrioritas as $pr)
                    <div class="py-2.5 border-b border-stone-100 last:border-0 flex items-center justify-between gap-2 text-sm">
                        <div>
                            <p class="font-semibold text-stone-800">{{ $pr->nama }}</p>
                            <p class="text-xs text-stone-500">{{ $pr->id_pelanggan }} &middot; {{ $pr->kota }}</p>
                        </div>
                        <span class="font-bold text-emerald-700 shrink-0">{{ format_rupiah($pr->total_belanja) }}</span>
                    </div>
                @empty
                    <p class="text-xs text-stone-400 py-3">Belum ada pelanggan yang melampaui Rp10.000.000 pada {{ $tahun }}.</p>
                @endforelse
            </div>

            {{-- Panel Pelanggan Belum Pernah Memesan (Soal 3i) --}}
            <div class="bg-white border border-stone-200 rounded-xl p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h2 class="font-bold text-stone-800 text-sm">Belum Pernah Memesan (Target Promo)</h2>
                        <p class="text-xs text-stone-500">Pelanggan terdaftar tanpa riwayat transaksi</p>
                    </div>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-700">3i</span>
                </div>

                @forelse($pelangganBelumPesan as $bp)
                    <div class="py-2.5 border-b border-stone-100 last:border-0 flex items-center justify-between gap-2 text-sm">
                        <div>
                            <p class="font-semibold text-stone-800">{{ $bp->nama }}</p>
                            <p class="text-xs text-stone-500">{{ $bp->email }}</p>
                        </div>
                        <span class="text-xs font-medium px-2 py-0.5 rounded bg-amber-100 text-amber-800 shrink-0">{{ $bp->kota }}</span>
                    </div>
                @empty
                    <p class="text-xs text-stone-400 py-3">Seluruh pelanggan sudah pernah melakukan pesanan.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- CATATAN DEFINISI STATUS LINTAS LAPORAN (T19: 3b, 3g, 3h) --}}
    <div class="bg-[#FAF3EF] border border-stone-200 rounded-xl p-4 text-xs text-stone-700">
        <p class="font-bold text-stone-800 mb-2">Keterangan Definisi Status Laporan Keuangan TokoKita</p>
        <ul class="space-y-1 list-disc list-inside">
            <li>
                <strong>Daftar Pesanan (Soal 3b):</strong> Menampilkan total nilai <code>SUM(jumlah * harga_satuan)</code> untuk <strong>seluruh status</strong> (<em>diproses, dikirim, selesai,</em> dan <em>batal</em>).
            </li>
            <li>
                <strong>Pendapatan per Kategori (Soal 3g):</strong> Hanya menghitung pesanan berstatus <strong>selesai</strong>.
            </li>
            <li>
                <strong>Pendapatan Bulanan &amp; Kartu Pendapatan (Soal 3h):</strong> Menghitung pesanan berstatus <strong>diproses, dikirim, dan selesai</strong> (status <strong>batal</strong> dikecualikan).
            </li>
        </ul>
    </div>

</div>
@endsection

@push('scripts')
<script>
    // Helper format Rupiah Indonesia untuk sumbu & tooltip Chart.js
    function formatRupiahJS(nilai) {
        return 'Rp' + Math.round(Number(nilai || 0)).toLocaleString('id-ID');
    }

    function formatRupiahRingkasJS(nilai) {
        const n = Number(nilai || 0);
        if (n >= 1000000) {
            return 'Rp' + (n / 1000000).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' jt';
        }
        if (n >= 1000) {
            return 'Rp' + (n / 1000).toLocaleString('id-ID', { maximumFractionDigits: 0 }) + ' rb';
        }
        return 'Rp' + n.toLocaleString('id-ID');
    }

    const namaBulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    // 1. Grafik Batang Pendapatan Bulanan (Soal 3h)
    const dataBulanan = @json($pendapatanBulanan);
    const canvasBulanan = document.getElementById('chartPendapatanBulanan');
    if (canvasBulanan && dataBulanan.length > 0) {
        new Chart(canvasBulanan, {
            type: 'bar',
            data: {
                labels: dataBulanan.map(item => namaBulan[Number(item.bulan)] || ('Bln ' + item.bulan)),
                datasets: [{
                    label: 'Pendapatan',
                    data: dataBulanan.map(item => Number(item.pendapatan)),
                    backgroundColor: '#C86D44',
                    hoverBackgroundColor: '#A8380F',
                    borderRadius: 6,
                    maxBarThickness: 44
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => 'Pendapatan: ' + formatRupiahJS(ctx.parsed.y)
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: (val) => formatRupiahRingkasJS(val)
                        }
                    }
                }
            }
        });
    }

    // 2. Grafik Batang Horizontal Pendapatan per Kategori (Soal 3g)
    const dataKategori = @json($pendapatanKategori);
    const canvasKategori = document.getElementById('chartPendapatanKategori');
    if (canvasKategori && dataKategori.length > 0) {
        new Chart(canvasKategori, {
            type: 'bar',
            data: {
                labels: dataKategori.map(item => item.kategori),
                datasets: [{
                    label: 'Pendapatan Selesai',
                    data: dataKategori.map(item => Number(item.pendapatan)),
                    backgroundColor: ['#A8380F', '#D48C6A', '#E2B49A'],
                    borderRadius: 6,
                    maxBarThickness: 32
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => 'Pendapatan (Selesai): ' + formatRupiahJS(ctx.parsed.x)
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            callback: (val) => formatRupiahRingkasJS(val)
                        }
                    }
                }
            }
        });
    }
</script>
@endpush