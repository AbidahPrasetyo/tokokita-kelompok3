<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Throwable;

class PesananRepository
{
    /**
     * Daftar pesanan beserta total nilainya.
     * Menjawab Soal 3b (alias kolom: id_pesanan, nama, tgl_pesan, total_nilai).
     */
    public function daftarPesanan(): array
    {
        // -- Soal 3b
        $sql = "
            SELECT
                ps.id_pesanan,
                pl.nama,
                ps.tgl_pesan,
                ps.status,
                SUM(dp.jumlah * dp.harga_satuan) AS total_nilai
            FROM pesanan ps
            INNER JOIN pelanggan pl ON ps.id_pelanggan = pl.id_pelanggan
            INNER JOIN detail_pesanan dp ON ps.id_pesanan = dp.id_pesanan
            GROUP BY ps.id_pesanan, pl.nama, ps.tgl_pesan, ps.status
            ORDER BY ps.id_pesanan ASC
        ";

        return DB::select($sql);
    }

    /**
     * Contoh kerangka transaksi checkout (START TRANSACTION -> COMMIT / ROLLBACK).
     */
    public function buatPesananCheckout(int $idPesanan, string $idPelanggan, string $tglPesan, array $items): void
    {
        DB::beginTransaction();

        try {
            // 1. INSERT ke tabel pesanan
            DB::insert(
                "INSERT INTO pesanan (id_pesanan, id_pelanggan, tgl_pesan, status) VALUES (?, ?, ?, 'diproses')",
                [$idPesanan, $idPelanggan, $tglPesan]
            );

            // 2. Loop tiap item keranjang: INSERT detail & UPDATE stok produk
            foreach ($items as $item) {
                DB::insert(
                    "INSERT INTO detail_pesanan (id_pesanan, id_produk, jumlah, harga_satuan) VALUES (?, ?, ?, ?)",
                    [$idPesanan, $item['id_produk'], $item['jumlah'], $item['harga_kini']]
                );

                DB::update(
                    "UPDATE produk SET stok = stok - ? WHERE id_produk = ? AND stok >= ?",
                    [$item['jumlah'], $item['id_produk'], $item['jumlah']]
                );
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}