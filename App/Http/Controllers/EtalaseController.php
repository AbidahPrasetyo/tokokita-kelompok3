<?php

namespace App\Http\Controllers;

use App\Models\EtalaseRepository; // Sesuaikan dengan namespace EtalaseRepository kamu
use Illuminate\Http\Request;

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

        // Validasi logis jika harga minimum lebih besar dari maksimum
        if ($min !== null && $max !== null && $min > $max) {
            return redirect()->route('etalase.index')
                ->with('error', 'Harga minimum tidak boleh lebih besar daripada harga maksimum.');
        }

        $daftarKategori = $this->etalaseRepo->daftarKategori();
        $produk         = $this->etalaseRepo->getProdukKatalog($kategori, $min, $max, $cari, $urut);

        return view('etalase.index', compact(
            'produk',
            'daftarKategori',
            'kategori',
            'min',
            'max',
            'cari',
            'urut'
        ));
    }

    // Method sementara agar tombol "+ Keranjang" tidak error sebelum Minggu 2 (T04)
    public function tambahKeranjang()
    {
        return back()->with('error', 'Fitur keranjang belanja akan diaktifkan di Minggu 2 (T04).');
    }

    public function hapusKeranjang()
    {
        return back();
    }

    public function checkout()
    {
        return back()->with('error', 'Fitur checkout akan diaktifkan di Minggu 2 (T05).');
    }
}