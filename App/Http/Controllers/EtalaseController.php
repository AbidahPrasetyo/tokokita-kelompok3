<?php

namespace App\Http\Controllers;

use App\Models\EtalaseRepository;
use Illuminate\Http\Request;

class EtalaseController extends Controller
{
    public function __construct(
        private EtalaseRepository $etalaseRepo
    ) {}

    public function index(Request $request)
    {
        $kategori = $request->input('kategori', 'Elektronik');
        $min      = (float) $request->input('min', 1000000);
        $max      = (float) $request->input('max', 5000000);

        // Panggil fungsi dari Repository
        $produk = $this->etalaseRepo->filterProduk($kategori, $min, $max);

        // Kirim hasil ke view (atau pakai dd($produk) dulu untuk tes awal)
        return response()->json($produk);
    }
}
