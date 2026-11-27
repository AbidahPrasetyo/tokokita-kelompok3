<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PelangganRepository;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class PelangganController extends Controller
{
    public function __construct(
        private PelangganRepository $pelangganRepo
    ) {}

    public function index(Request $request)
    {
        $cari       = trim((string) $request->input('cari', ''));
        $pelanggan  = $this->pelangganRepo->getDaftarPelanggan($cari);
        $sebaranKota = $this->pelangganRepo->sebaranPerKota();
        $prioritas  = $this->pelangganRepo->pelangganPrioritas(2026, 10000000);
        $belumPesan = $this->pelangganRepo->pelangganBelumPernahMemesan();

        return view('admin.pelanggan.index', compact(
            'pelanggan',
            'cari',
            'sebaranKota',
            'prioritas',
            'belumPesan'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'  => 'required|string|max:50',
            'email' => 'required|email|max:60',
            'kota'  => 'required|string|max:30',
        ], [
            'nama.required'  => 'Nama pelanggan wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
            'kota.required'  => 'Kota wajib diisi.',
        ]);

        if ($this->pelangganRepo->emailSudahDipakai($validated['email'])) {
            return back()->withInput()->with('error', 'Email tersebut sudah terdaftar pada pelanggan lain.');
        }

        $idBaru = $this->pelangganRepo->generateIdBaru();

        $this->pelangganRepo->insertPelanggan(
            $idBaru,
            $validated['nama'],
            $validated['email'],
            $validated['kota']
        );

        return redirect()->route('admin.pelanggan.index')
            ->with('success', "Pelanggan {$idBaru} ({$validated['nama']}) berhasil ditambahkan.");
    }

    public function update(Request $request, string $id)
    {
        if (!$this->pelangganRepo->findById($id)) {
            return redirect()->route('admin.pelanggan.index')
                ->with('error', "Pelanggan {$id} tidak ditemukan.");
        }

        $validated = $request->validate([
            'nama'  => 'required|string|max:50',
            'email' => 'required|email|max:60',
            'kota'  => 'required|string|max:30',
        ]);

        if ($this->pelangganRepo->emailSudahDipakai($validated['email'], $id)) {
            return back()->with('error', 'Email tersebut sudah digunakan oleh pelanggan lain.');
        }

        $this->pelangganRepo->updatePelanggan(
            $id,
            $validated['nama'],
            $validated['email'],
            $validated['kota']
        );

        return redirect()->route('admin.pelanggan.index')
            ->with('success', "Data pelanggan {$id} berhasil diperbarui.");
    }

    public function destroy(string $id)
    {
        $pelanggan = $this->pelangganRepo->findById($id);

        if (!$pelanggan) {
            return redirect()->route('admin.pelanggan.index')
                ->with('error', "Pelanggan {$id} tidak ditemukan.");
        }

        if ($this->pelangganRepo->punyaPesanan($id)) {
            return redirect()->route('admin.pelanggan.index')
                ->with('error', "Pelanggan \"{$pelanggan->nama}\" tidak dapat dihapus karena masih memiliki riwayat pesanan.");
        }

        try {
            $this->pelangganRepo->deletePelanggan($id);
            return redirect()->route('admin.pelanggan.index')
                ->with('success', "Pelanggan \"{$pelanggan->nama}\" berhasil dihapus.");
        } catch (QueryException $e) {
            if (($e->errorInfo[1] ?? null) === 1451) {
                return redirect()->route('admin.pelanggan.index')
                    ->with('error', "Pelanggan \"{$pelanggan->nama}\" tidak dapat dihapus karena masih terhubung ke data pesanan.");
            }
            throw $e;
        }
    }
}