<?php

namespace App\Models; // Ubah ke App\Repositories jika repo Ketua memakai app/Repositories

use Illuminate\Support\Facades\DB;

class LaporanRepository
{
    /**
     * 5 produk terlaris berdasarkan total jumlah unit terjual.
     * Menjawab Soal 3c (alias wajib: nama_produk, total_terjual).
     * Konsep: SUM, GROUP BY, ORDER BY, LIMIT (tanpa subquery).
     */
    public function produkTerlaris(int $limit = 5): array
    {
        // -- Soal 3c
        $sql = "
            SELECT
                pr.nama_produk,
                SUM(dp.jumlah) AS total_terjual
            FROM produk pr
            INNER JOIN detail_pesanan dp ON pr.id_produk = dp.id_produk
            GROUP BY pr.id_produk, pr.nama_produk
            ORDER BY total_terjual DESC, pr.id_produk ASC
            LIMIT ?
        ";

        return DB::select($sql, [$limit]);
    }

    /**
     * Total pendapatan per kategori produk, khusus pesanan berstatus 'selesai'.
     * Menjawab Soal 3g (alias wajib: kategori, pendapatan).
     * Wajib menghitung dari detail_pesanan.harga_satuan (harga historis).
     */
    public function pendapatanPerKategori(string $status = 'selesai'): array
    {
        // -- Soal 3g
        $sql = "
            SELECT
                pr.kategori,
                SUM(dp.jumlah * dp.harga_satuan) AS pendapatan
            FROM pesanan ps
            INNER JOIN detail_pesanan dp ON ps.id_pesanan = dp.id_pesanan
            INNER JOIN produk pr ON dp.id_produk = pr.id_produk
            WHERE ps.status = ?
            GROUP BY pr.kategori
            ORDER BY pendapatan DESC
        ";

        return DB::select($sql, [$status]);
    }

    /**
     * Total pendapatan per bulan selama tahun terpilih (pesanan berstatus 'batal' tidak dihitung).
     * Menjawab Soal 3h (alias wajib: bulan, pendapatan).
     * Konsep: MONTH(), YEAR(), operator <> (tanpa subquery).
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
     * Data angka untuk 4 kartu ringkasan pada Dashboard Admin (persiapan T18).
     */
    public function ringkasanDashboard(int $tahun = 2026): array
    {
        $pesanan = DB::selectOne("SELECT COUNT(*) AS total FROM pesanan");
        $pelanggan = DB::selectOne("SELECT COUNT(*) AS total FROM pelanggan");

        // Total pendapatan tahun berjalan tanpa status 'batal' (selaras Soal 3h)
        $pendapatan = DB::selectOne("
            SELECT COALESCE(SUM(dp.jumlah * dp.harga_satuan), 0) AS total
            FROM pesanan ps
            INNER JOIN detail_pesanan dp ON ps.id_pesanan = dp.id_pesanan
            WHERE YEAR(ps.tgl_pesan) = ?
              AND ps.status <> 'batal'
        ", [$tahun]);

        // Jumlah produk belum pernah dipesan (selaras Soal 3e)
        $produkTakLaku = DB::selectOne("
            SELECT COUNT(pr.id_produk) AS total
            FROM produk pr
            LEFT JOIN detail_pesanan dp ON pr.id_produk = dp.id_produk
            WHERE dp.id_produk IS NULL
        ");

        return [
            'jumlah_pesanan'  => (int) ($pesanan->total ?? 0),
            'total_pendapatan'=> (float) ($pendapatan->total ?? 0),
            'total_pelanggan' => (int) ($pelanggan->total ?? 0),
            'produk_tak_laku' => (int) ($produkTakLaku->total ?? 0),
        ];
    }
}