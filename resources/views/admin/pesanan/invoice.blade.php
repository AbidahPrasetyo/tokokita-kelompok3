<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $pesanan->id_pesanan }} — TokoKita</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
        }
    </style>
</head>
<body class="bg-stone-100 text-stone-800 py-8 px-4">
    <div class="max-w-3xl mx-auto bg-white border border-stone-200 rounded-xl p-8 shadow-sm">
        {{-- Tombol Cetak (Tersembunyi saat Print) --}}
        <div class="no-print flex justify-between items-center mb-6 pb-4 border-b border-stone-200">
            <a href="{{ route('admin.pesanan.show', $pesanan->id_pesanan) }}" class="text-sm text-stone-600 hover:underline">&larr; Kembali</a>
            <button onclick="window.print()" class="bg-[#A8380F] hover:bg-[#8E2F0C] text-white text-sm font-semibold px-4 py-2 rounded-lg">
                Cetak / Simpan PDF
            </button>
        </div>

        {{-- Kop Invoice --}}
        <div class="flex justify-between items-start border-b-2 border-[#A8380F] pb-5 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-[#A8380F]">TokoKita</h1>
                <p class="text-xs text-stone-500">Invoice Resmi Transaksi Penjualan</p>
            </div>
            <div class="text-right text-sm">
                <p class="font-bold text-lg text-stone-800">INVOICE #{{ $pesanan->id_pesanan }}</p>
                <p class="text-stone-600">Tanggal: {{ format_tanggal($pesanan->tgl_pesan) }}</p>
                <p class="text-xs uppercase font-semibold mt-1 text-stone-700">Status: {{ $pesanan->status }}</p>
            </div>
        </div>

        {{-- Data Pelanggan --}}
        <div class="mb-6 text-sm">
            <p class="text-xs uppercase font-semibold text-stone-400">Ditagihkan Kepada</p>
            <p class="font-bold text-stone-800 text-base">{{ $pesanan->nama_pelanggan }} ({{ $pesanan->id_pelanggan }})</p>
            <p class="text-stone-600">{{ $pesanan->email_pelanggan }} &middot; {{ $pesanan->kota_pelanggan }}</p>
        </div>

        {{-- Tabel Item --}}
        <table class="w-full text-sm text-left border-collapse">
            <thead>
                <tr class="border-b-2 border-stone-300 text-xs uppercase text-stone-500">
                    <th class="py-2.5">Produk</th>
                    <th class="py-2.5 text-right">Harga Satuan</th>
                    <th class="py-2.5 text-center">Qty</th>
                    <th class="py-2.5 text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-200">
                @foreach($items as $row)
                    <tr>
                        <td class="py-3">
                            <p class="font-medium text-stone-800">{{ $row->nama_produk }}</p>
                            <p class="text-xs text-stone-500">{{ $row->id_produk }} &middot; {{ $row->kategori }}</p>
                        </td>
                        <td class="py-3 text-right">{{ format_rupiah($row->harga_satuan) }}</td>
                        <td class="py-3 text-center">{{ $row->jumlah }}</td>
                        <td class="py-3 text-right font-semibold">{{ format_rupiah($row->subtotal) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-stone-300">
                    <td colspan="3" class="py-4 text-right font-bold text-stone-800">TOTAL PEMBAYARAN</td>
                    <td class="py-4 text-right font-bold text-xl text-[#A8380F]">{{ format_rupiah($pesanan->total_nilai) }}</td>
                </tr>
            </tfoot>
        </table>

        <p class="mt-8 text-xs text-stone-400 text-center">
            Terima kasih telah berbelanja di TokoKita. Harga satuan tercatat sesuai nilai transaksi historis.
        </p>
    </div>
</body>
</html>