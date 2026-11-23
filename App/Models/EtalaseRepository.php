<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class EtalaseRepository
{
    /**
     * Filter katalog produk berdasarkan kategori dan rentang harga.
     * Menjawab Soal 3a (kolom utama: nama_produk, harga).
     */
    public function filterProduk(string $kategori, float $hargaMin, float $hargaMax): array
    {
        // -- Soal 3a
        $sql = "
            SELECT p.id_produk, p.nama_produk, p.kategori, p.harga, p.stok
            FROM produk p
            WHERE p.kategori = ?
              AND p.harga BETWEEN ? AND ?
            ORDER BY p.harga ASC
        ";

        // Nilai array mengisi tanda ? secara berurutan:
        // ? ke-1 = $kategori, ? ke-2 = $hargaMin, ? ke-3 = $hargaMax
        return DB::select($sql, [$kategori, $hargaMin, $hargaMax]);
    }
}