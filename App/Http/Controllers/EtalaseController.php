<?php

namespace App\Http\Controllers;

use App\Models\EtalaseRepository; // Ubah ke App\Repositories jika memakai folder Repositories
use Illuminate\Http\Request;
use RuntimeException;
use Throwable;

class EtalaseController extends Controller
{
    public function __construct(
        private EtalaseRepository $etalaseRepo
    ) {}

    public function index(Request $request)
    {
        $validated = $request->validate([
            'kategori' => 'nullable|string|max:30',
            'min'      => 'nullable|numeric|min:0',
            'max'      => 'nullable|numeric|min:0',
            'cari'     => 'nullable|string|max:50',
            'urut'     => 'nullable|in:termurah,termahal,nama_asc',
        ]);

        $kategori = $validated['kategori'] ?? null;
        $min      = isset($validated['min']) ? (float) $validated['min'] : null;
        $max      = isset($validated['max']) ? (float) $validated['max'] : null;
        $cari     = $validated['cari'] ?? null;
        $urut     = $validated['urut'] ?? 'termurah';

        if ($min !== null && $max !== null && $min > $max) {
            return redirect()->route('etalase.index')
                ->with('error', 'Harga minimum tidak boleh lebih besar daripada harga maksimum.');
        }

        $daftarKategori  = $this->etalaseRepo->daftarKategori();
        $produk          = $this->etalaseRepo->getProdukKatalog($kategori, $min, $max, $cari, $urut);
        $daftarPelanggan = $this->etalaseRepo->daftarPelanggan();

        // Hitung isi & total sementara keranjang dari session
        $keranjang      = session('keranjang', []);
        $totalKeranjang = 0;
        foreach ($keranjang as $item) {
            $totalKeranjang += ((float) $item['harga']) * ((int) $item['jumlah']);
        }

        return view('etalase.index', compact(
            'produk',
            'daftarKategori',
            'daftarPelanggan',
            'keranjang',
            'totalKeranjang',
            'kategori',
            'min',
            'max',
            'cari',
            'urut'
        ));
    }

    /**
     * T04: Tambah produk ke keranjang berbasis session.
     */
    public function tambahKeranjang(Request $request)
    {
        $validated = $request->validate([
            'id_produk' => 'required|string|size:3',
        ]);

        $produk = $this->etalaseRepo->findProdukAktif($validated['id_produk']);
        if (!$produk) {
            return back()->with('error', 'Produk tidak ditemukan atau sedang nonaktif.');
        }

        $keranjang = session('keranjang', []);
        $id        = $produk->id_produk;
        $qtyBaru   = isset($keranjang[$id]) ? ($keranjang[$id]['jumlah'] + 1) : 1;

        if ($qtyBaru > (int) $produk->stok) {
            return back()->with('error', "Stok {$produk->nama_produk} hanya tersedia {$produk->stok} unit.");
        }

        $keranjang[$id] = [
            'id_produk'   => $id,
            'nama_produk' => $produk->nama_produk,
            'harga'       => (float) $produk->harga,
            'jumlah'      => $qtyBaru,
            'stok'        => (int) $produk->stok,
        ];

        session()->put('keranjang', $keranjang);

        return back()->with('success', "{$produk->nama_produk} ditambahkan ke keranjang.");
    }

    /**
     * T04: Ubah jumlah item di dalam keranjang (jika 0, otomatis dihapus).
     */
    public function ubahKeranjang(Request $request)
    {
        $validated = $request->validate([
            'id_produk' => 'required|string|size:3',
            'jumlah'    => 'required|integer|min:0',
        ]);

        $keranjang = session('keranjang', []);
        $id        = $validated['id_produk'];

        if (!isset($keranjang[$id])) {
            return back()->with('error', 'Item tidak ditemukan di keranjang.');
        }

        $jumlahBaru = (int) $validated['jumlah'];

        if ($jumlahBaru === 0) {
            $nama = $keranjang[$id]['nama_produk'];
            unset($keranjang[$id]);
            session()->put('keranjang', $keranjang);
            return back()->with('success', "{$nama} dihapus dari keranjang.");
        }

        $produk = $this->etalaseRepo->findProdukAktif($id);
        if (!$produk) {
            unset($keranjang[$id]);
            session()->put('keranjang', $keranjang);
            return back()->with('error', 'Produk sudah tidak tersedia dan dihapus dari keranjang.');
        }

        if ($jumlahBaru > (int) $produk->stok) {
            return back()->with('error', "Jumlah melebihi stok tersedia ({$produk->stok} unit) untuk {$produk->nama_produk}.");
        }

        $keranjang[$id]['jumlah'] = $jumlahBaru;
        $keranjang[$id]['harga']  = (float) $produk->harga;
        $keranjang[$id]['stok']   = (int) $produk->stok;

        session()->put('keranjang', $keranjang);

        return back()->with('success', "Jumlah {$produk->nama_produk} diperbarui menjadi {$jumlahBaru}.");
    }

    /**
     * T04: Hapus satu item dari keranjang.
     */
    public function hapusKeranjang(Request $request)
    {
        $validated = $request->validate([
            'id_produk' => 'required|string|size:3',
        ]);

        $keranjang = session('keranjang', []);
        $id        = $validated['id_produk'];

        if (isset($keranjang[$id])) {
            $nama = $keranjang[$id]['nama_produk'];
            unset($keranjang[$id]);
            session()->put('keranjang', $keranjang);
            return back()->with('success', "{$nama} dihapus dari keranjang.");
        }

        return back();
    }

    /**
     * T05: Proses checkout dalam 1 transaksi atomik.
     */
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'id_pelanggan' => 'required|string|size:3',
        ], [
            'id_pelanggan.required' => 'Silakan pilih pelanggan terlebih dahulu sebelum checkout.',
        ]);

        $keranjang = session('keranjang', []);
        if (empty($keranjang)) {
            return back()->with('error', 'Keranjang belanja masih kosong.');
        }

        // Gunakan TANGGAL_ACUAN pada mode demo, atau tanggal hari ini pada mode produksi
        $tglPesan = config('tokokita.demo_mode')
            ? config('tokokita.tanggal_acuan', '2026-11-13')
            : date('Y-m-d');

        try {
            $idPesanan = $this->etalaseRepo->prosesCheckout(
                $validated['id_pelanggan'],
                $keranjang,
                $tglPesan
            );

            // Kosongkan session keranjang hanya setelah COMMIT berhasil
            session()->forget('keranjang');

            return redirect()->route('etalase.index')
                ->with('success', "Pesanan #{$idPesanan} berhasil dibuat dengan status 'diproses'. Stok produk telah diperbarui.");
        } catch (RuntimeException $e) {
            return back()->with('error', 'Checkout gagal (transaksi dibatalkan): ' . $e->getMessage());
        } catch (Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan sistem saat checkout. Seluruh transaksi telah dibatalkan (ROLLBACK).');
        }
    }
}