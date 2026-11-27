@extends('layouts.admin')

@section('title', 'Dashboard Penjualan — TokoKita Admin')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-stone-800">Dashboard Penjualan {{ $tahun }}</h1>
        <p class="text-sm text-stone-500">Tahap Minggu 1: Verifikasi query analitik (Soal 3c, 3g, 3h) &amp; integrasi Chart.js.</p>
    </div>

    {{-- 4 Kartu Ringkasan --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-stone-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-stone-500">Jumlah Pesanan</p>
            <p class="text-2xl font-bold text-[#A8380F] mt-1">{{ $ringkasan['jumlah_pesanan'] }}</p>
        </div>
        <div class="bg-white border border-stone-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-stone-500">Pendapatan (tanpa batal)</p>
            <p class="text-2xl font-bold text-emerald-700 mt-1">{{ format_rupiah_singkat($ringkasan['total_pendapatan']) }}</p>
        </div>
        <div class="bg-white border border-stone-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-stone-500">Pelanggan</p>
            <p class="text-2xl font-bold text-sky-700 mt-1">{{ $ringkasan['total_pelanggan'] }}</p>
        </div>
        <div class="bg-white border border-stone-200 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold text-stone-500">Produk Tak Laku</p>
                <span class="text-[11px] font-bold px-1.5 py-0.5 rounded bg-red-100 text-red-700">3e</span>
            </div>
            <p class="text-2xl font-bold text-red-700 mt-1">{{ $ringkasan['produk_tak_laku'] }}</p>
        </div>
    </div>

    {{-- Baris Grafik 3h & Tabel 3c --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        {{-- Grafik Pendapatan Bulanan (Soal 3h) --}}
        <div class="lg:col-span-7 bg-white border border-stone-200 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-bold text-stone-800">Pendapatan per Bulan {{ $tahun }}</h2>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-700">3h</span>
            </div>
            <div class="h-60">
                <canvas id="chartPendapatanBulanan"></canvas>
            </div>
            <p class="text-xs text-stone-500 mt-3">
                * Status dihitung: <strong>diproses, dikirim, selesai</strong> (status <strong>batal</strong> tidak dihitung).
            </p>
        </div>

        {{-- Tabel 5 Produk Terlaris (Soal 3c) --}}
        <div class="lg:col-span-5 bg-white border border-stone-200 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-bold text-stone-800">5 Produk Terlaris</h2>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-700">3c</span>
            </div>
            <table class="w-full text-sm text-left">
                <thead class="border-b border-stone-200 text-xs text-stone-500 uppercase">
                    <tr>
                        <th class="py-2">#</th>
                        <th class="py-2">Produk</th>
                        <th class="py-2 text-right">Unit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach($produkTerlaris as $idx => $item)
                        <tr>
                            <td class="py-2 text-stone-500">{{ $idx + 1 }}</td>
                            <td class="py-2 font-medium text-stone-800">{{ $item->nama_produk }}</td>
                            <td class="py-2 text-right font-semibold text-stone-800">{{ $item->total_terjual }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pendapatan per Kategori (Soal 3g) --}}
    <div class="bg-white border border-stone-200 rounded-xl p-5 shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-bold text-stone-800">Pendapatan per Kategori (Status Selesai)</h2>
            <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-700">3g</span>
        </div>
        <div class="space-y-3">
            @foreach($pendapatanKategori as $kat)
                <div class="flex items-center justify-between text-sm border-b border-stone-100 pb-2">
                    <span class="font-medium text-stone-700">{{ $kat->kategori }}</span>
                    <span class="font-bold text-[#A8380F]">{{ format_rupiah($kat->pendapatan) }}</span>
                </div>
            @endforeach
        </div>
        <p class="text-xs text-stone-500 mt-3">
            * Status dihitung: hanya pesanan berstatus <strong>selesai</strong>.
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const dataBulanan = @json($pendapatanBulanan);
    const labelBulan = dataBulanan.map(item => 'Bulan ' + item.bulan);
    const nilaiPendapatan = dataBulanan.map(item => Number(item.pendapatan));

    const ctx = document.getElementById('chartPendapatanBulanan');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labelBulan,
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: nilaiPendapatan,
                backgroundColor: '#C86D44',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>
@endpush