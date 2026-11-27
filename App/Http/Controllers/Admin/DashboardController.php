<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LaporanRepository; // Sesuaikan namespace jika memakai App\Repositories
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private LaporanRepository $laporanRepo
    ) {}

    public function index(Request $request)
    {
        $validated = $request->validate([
            'tahun' => 'nullable|integer|min:2020|max:2100',
        ]);

        $tahun = (int) ($validated['tahun'] ?? 2026);

        $ringkasan          = $this->laporanRepo->ringkasanDashboard($tahun);
        $produkTerlaris     = $this->laporanRepo->produkTerlaris(5);
        $pendapatanKategori = $this->laporanRepo->pendapatanPerKategori('selesai');
        $pendapatanBulanan  = $this->laporanRepo->pendapatanBulanan($tahun);
        $pelangganPrioritas = $this->laporanRepo->pelangganPrioritas($tahun, 10000000);
        $pelangganBelumPesan = $this->laporanRepo->pelangganBelumPernahMemesan();

        return view('admin.dashboard', compact(
            'tahun',
            'ringkasan',
            'produkTerlaris',
            'pendapatanKategori',
            'pendapatanBulanan',
            'pelangganPrioritas',
            'pelangganBelumPesan'
        ));
    }
}