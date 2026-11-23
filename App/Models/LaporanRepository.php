<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class LaporanRepository
{
    /**
     * Total pendapatan per bulan pada tahun tertentu (tanpa status batal).
     * Menjawab Soal 3h (alias kolom: bulan, pendapatan).
     */
    public function pendapatanBulanan(int $tahun = 2026): array
    {
        // -- Soal 3h
        $sql = "
            SELECT
                MONTH(ps.tgl_pesan) AS bulan,
                SUM(dp.jumlah * dp.harga_satuan) AS pendapatan
            FROM pesanan ps
            INNER JOIN detail_pesanan dp ON ps.id_pesanan = dp.id_pesanan
            WHERE YEAR(ps.tgl_pesan) = ?
              AND ps.status <> 'batal'
            GROUP BY MONTH(ps.tgl_pesan)
            ORDER BY bulan ASC
        ";

        return DB::select($sql, [$tahun]);
    }

    /**
     * 5 produk terlaris berdasarkan total unit terjual.
     * Menjawab Soal 3c (alias kolom: nama_produk, total_terjual).
     */
    public function produkTerlaris(): array
    {
        // -- Soal 3c (tanpa input pengguna, jadi tidak perlu array binding)
        $sql = "
            SELECT
                pr.nama_produk,
                SUM(dp.jumlah) AS total_terjual
            FROM produk pr
            INNER JOIN detail_pesanan dp ON pr.id_produk = dp.id_produk
            GROUP BY pr.id_produk, pr.nama_produk
            ORDER BY total_terjual DESC
            LIMIT 5
        ";

        return DB::select($sql);
    }
}