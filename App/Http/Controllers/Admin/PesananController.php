<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PesananRepository;
use Illuminate\Http\Request;
use RuntimeException;

class PesananController extends Controller
{
    public function __construct(
        private PesananRepository $pesananRepo
    ) {}

    public function index(Request $request)
    {
        $cari                  = trim((string) $request->input('cari', ''));
        $status                = $request->input('status');
        $hanyaLebihDariTiga    = $request->input('filter') === 'multi_produk';

        if (!in_array($status, ['diproses', 'dikirim', 'selesai', 'batal'], true)) {
            $status = null;
        }

        $pesanan          = $this->pesananRepo->getDaftarPesanan($cari, $status, $hanyaLebihDariTiga);
        $jumlahMultiProduk = count($this->pesananRepo->pesananLebihDariTigaJenis());

        return view('admin.pesanan.index', compact(
            'pesanan',
            'cari',
            'status',
            'hanyaLebihDariTiga',
            'jumlahMultiProduk'
        ));
    }

    public function show(int $id)
    {
        $pesanan = $this->pesananRepo->findMasterById($id);

        if (!$pesanan) {
            return redirect()->route('admin.pesanan.index')
                ->with('error', "Pesanan #{$id} tidak ditemukan.");
        }

        $items            = $this->pesananRepo->getDetailItems($id);
        $statusBerikutnya = $this->pesananRepo->getStatusBerikutnya($pesanan->status);

        return view('admin.pesanan.show', compact('pesanan', 'items', 'statusBerikutnya'));
    }

    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:diproses,dikirim,selesai,batal',
        ], [
            'status.in' => 'Status pesanan tidak valid.',
        ]);

        try {
            $this->pesananRepo->ubahStatus($id, $validated['status']);

            $catatan = $validated['status'] === 'batal'
                ? ' Stok produk terkait telah dikembalikan secara otomatis.'
                : '';

            return redirect()->route('admin.pesanan.show', $id)
                ->with('success', "Status pesanan #{$id} berhasil diubah menjadi '{$validated['status']}'.{$catatan}");
        } catch (RuntimeException $e) {
            return redirect()->route('admin.pesanan.show', $id)
                ->with('error', $e->getMessage());
        }
    }

    public function invoice(int $id)
    {
        $pesanan = $this->pesananRepo->findMasterById($id);

        if (!$pesanan) {
            return redirect()->route('admin.pesanan.index')
                ->with('error', "Pesanan #{$id} tidak ditemukan.");
        }

        $items = $this->pesananRepo->getDetailItems($id);

        return view('admin.pesanan.invoice', compact('pesanan', 'items'));
    }
}