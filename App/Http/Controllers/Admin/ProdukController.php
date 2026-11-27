<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProdukRepository; // Sesuaikan namespace jika memakai App\Repositories
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ProdukController extends Controller
{
    public function __construct(
        private ProdukRepository $produkRepo
    ) {}

    public function index(Request $request)
    {
        $cari              = trim((string) $request->input('cari', ''));
        $hanyaBelumDipesan = $request->input('filter') === 'belum_dipesan';
        $perPage           = 10;
        $page              = max(1, (int) $request->input('page', 1));
        $offset            = ($page - 1) * $perPage;

        $total = $this->produkRepo->countDaftarProduk($cari, $hanyaBelumDipesan);
        $items = $this->produkRepo->getDaftarProduk($cari, $hanyaBelumDipesan, $perPage, $offset);

        // Paginasi Laravel untuk hasil raw query
        $produk = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path'  => $request->url(),
                'query' => $request->query(),
            ]
        );

        $jumlahTakLaku = count($this->produkRepo->produkBelumPernahDipesan());

        return view('admin.produk.index', compact(
            'produk',
            'cari',
            'hanyaBelumDipesan',
            'jumlahTakLaku'
        ));
    }

    public function create()
    {
        return view('admin.produk.form', ['produk' => null]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_produk' => 'required|string|max:50',
            'kategori'    => 'required|string|max:30',
            'harga'       => 'required|numeric|min:1',
            'stok'        => 'required|integer|min:0',
        ], [
            'nama_produk.required' => 'Nama produk wajib diisi.',
            'kategori.required'    => 'Kategori wajib dipilih atau diisi.',
            'harga.min'            => 'Harga produk harus lebih dari Rp0.',
            'stok.min'             => 'Stok tidak boleh bernilai negatif.',
        ]);

        $idBaru = $this->produkRepo->generateIdBaru();

        $this->produkRepo->insertProduk(
            $idBaru,
            $validated['nama_produk'],
            $validated['kategori'],
            (float) $validated['harga'],
            (int) $validated['stok']
        );

        return redirect()->route('admin.produk.index')
            ->with('success', "Produk {$idBaru} ({$validated['nama_produk']}) berhasil ditambahkan.");
    }

    public function edit(string $id)
    {
        $produk = $this->produkRepo->findById($id);

        if (!$produk) {
            return redirect()->route('admin.produk.index')
                ->with('error', "Produk dengan ID {$id} tidak ditemukan.");
        }

        return view('admin.produk.form', compact('produk'));
    }

    public function update(Request $request, string $id)
    {
        if (!$this->produkRepo->findById($id)) {
            return redirect()->route('admin.produk.index')
                ->with('error', "Produk dengan ID {$id} tidak ditemukan.");
        }

        $validated = $request->validate([
            'nama_produk' => 'required|string|max:50',
            'kategori'    => 'required|string|max:30',
            'harga'       => 'required|numeric|min:1',
            'stok'        => 'required|integer|min:0',
        ], [
            'nama_produk.required' => 'Nama produk wajib diisi.',
            'kategori.required'    => 'Kategori wajib diisi.',
            'harga.min'            => 'Harga produk harus lebih dari Rp0.',
            'stok.min'             => 'Stok tidak boleh bernilai negatif.',
        ]);

        $this->produkRepo->updateProduk(
            $id,
            $validated['nama_produk'],
            $validated['kategori'],
            (float) $validated['harga'],
            (int) $validated['stok']
        );

        return redirect()->route('admin.produk.index')
            ->with('success', "Data produk {$id} berhasil diperbarui.");
    }

    public function destroy(string $id)
    {
        $produk = $this->produkRepo->findById($id);

        if (!$produk) {
            return redirect()->route('admin.produk.index')
                ->with('error', "Produk dengan ID {$id} tidak ditemukan.");
        }

        // Proteksi aturan bisnis & Foreign Key sebelum DELETE
        if ($this->produkRepo->pernahDipesan($id)) {
            return redirect()->route('admin.produk.index')
                ->with('error', "Produk \"{$produk->nama_produk}\" tidak dapat dihapus karena sudah memiliki riwayat pesanan. Gunakan tombol Nonaktifkan bila diperlukan.");
        }

        try {
            $this->produkRepo->deleteProduk($id);
            return redirect()->route('admin.produk.index')
                ->with('success', "Produk \"{$produk->nama_produk}\" berhasil dihapus.");
        } catch (QueryException $e) {
            // Pengaman lapis kedua jika MySQL mengembalikan error FK 1451
            if (($e->errorInfo[1] ?? null) === 1451) {
                return redirect()->route('admin.produk.index')
                    ->with('error', "Produk \"{$produk->nama_produk}\" tidak dapat dihapus karena masih dirujuk pada data pesanan.");
            }
            throw $e;
        }
    }

    public function toggleStatus(string $id)
    {
        $produk = $this->produkRepo->findById($id);

        if (!$produk) {
            return redirect()->route('admin.produk.index')
                ->with('error', "Produk dengan ID {$id} tidak ditemukan.");
        }

        $sekarangAktif = $this->produkRepo->toggleAktif($id);
        $label = $sekarangAktif ? 'diaktifkan kembali' : 'dinonaktifkan';

        return redirect()->route('admin.produk.index')
            ->with('success', "Produk \"{$produk->nama_produk}\" berhasil {$label}.");
    }
}