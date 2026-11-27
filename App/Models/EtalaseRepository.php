<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class EtalaseRepository
{
    /**
     * Ambil daftar kategori unik untuk sidebar filter.
     */
    public function daftarKategori(): array
    {
        $sql = "SELECT DISTINCT kategori FROM produk ORDER BY kategori ASC";
        return DB::select($sql);
    }

    /**
     * Filter katalog produk berdasarkan kategori, rentang harga, pencarian, dan urutan.
     * Menjawab Soal 3a (kolom: nama_produk, harga; konsep: BETWEEN, AND).
     */
    public function getProdukKatalog(
        ?string $kategori = null,
        ?float $hargaMin = null,
        ?float $hargaMax = null,
        ?string $cari = null,
        string $urut = 'termurah'
    ): array {
        // Whitelist kolom pengurutan agar aman dari SQL Injection
        $orderBy = match ($urut) {
            'termahal' => 'p.harga DESC',
            'nama_asc' => 'p.nama_produk ASC',
            default    => 'p.harga ASC',
        };

        $min = $hargaMin ?? 0;
        $max = $hargaMax ?? 999999999999;

        // -- Soal 3a
        $sql = "
            SELECT
                p.id_produk,
                p.nama_produk,
                p.kategori,
                p.harga,
                p.stok
            FROM produk p
            WHERE p.harga BETWEEN ? AND ?
        ";

        $bindings = [$min, $max];

        if (!empty($kategori)) {
            $sql .= " AND p.kategori = ?";
            $bindings[] = $kategori;
        }

        if (!empty($cari)) {
            $sql .= " AND p.nama_produk LIKE ?";
            $bindings[] = '%' . $cari . '%';
        }

        $sql .= " ORDER BY {$orderBy}";

        return DB::select($sql, $bindings);
    }
}