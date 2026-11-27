<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class PesananRepository
{
    /**
     * Peta transisi status pesanan yang diizinkan (Aturan Bisnis T12).
     */
    public const TRANSISI_STATUS = [
        'diproses' => ['dikirim', 'batal'],
        'dikirim'  => ['selesai'],
        'selesai'  => [],
        'batal'    => [],
    ];

    /**
     * Daftar pesanan beserta total nilai transaksi & jumlah jenis produk.
     * Menjawab Soal 3b (SUM(jumlah * harga_satuan)) dan Soal 3j (COUNT + HAVING > 3).
     * Bebas subquery & aman untuk MySQL 8 ONLY_FULL_GROUP_BY.
     */
    public function getDaftarPesanan(
        ?string $cari = null,
        ?string $status = null,
        bool $hanyaLebihDariTigaJenis = false
    ): array {
        // -- Soal 3b & 3j
        $sql = "
            SELECT
                ps.id_pesanan,
                ps.tgl_pesan,
                ps.status,
                pl.id_pelanggan,
                pl.nama AS nama_pelanggan,
                pl.kota,
                COUNT(dp.id_produk) AS jumlah_jenis,
                SUM(dp.jumlah) AS total_unit,
                SUM(dp.jumlah * dp.harga_satuan) AS total_nilai
            FROM pesanan ps
            INNER JOIN pelanggan pl ON ps.id_pelanggan = pl.id_pelanggan
            INNER JOIN detail_pesanan dp ON ps.id_pesanan = dp.id_pesanan
            WHERE 1 = 1
        ";

        $bindings = [];

        if (!empty($cari)) {
            $sql .= " AND (CAST(ps.id_pesanan AS CHAR) LIKE ? OR pl.nama LIKE ?)";
            $like = '%' . $cari . '%';
            array_push($bindings, $like, $like);
        }

        if (!empty($status)) {
            $sql .= " AND ps.status = ?";
            $bindings[] = $status;
        }

        $sql .= "
            GROUP BY ps.id_pesanan, ps.tgl_pesan, ps.status, pl.id_pelanggan, pl.nama, pl.kota
        ";

        if ($hanyaLebihDariTigaJenis) {
            // -- Soal 3j
            $sql .= " HAVING COUNT(dp.id_produk) > 3";
        }

        $sql .= " ORDER BY ps.id_pesanan ASC";

        return DB::select($sql, $bindings);
    }

    /**
     * Query khusus Soal 3j: daftar pesanan yang memiliki lebih dari 3 jenis produk.
     * Konsep: COUNT, GROUP BY, HAVING (tanpa subquery).
     */
    public function pesananLebihDariTigaJenis(): array
    {
        // -- Soal 3j
        $sql = "
            SELECT
                ps.id_pesanan,
                pl.nama AS nama_pelanggan,
                COUNT(dp.id_produk) AS jumlah_jenis
            FROM pesanan ps
            INNER JOIN pelanggan pl ON ps.id_pelanggan = pl.id_pelanggan
            INNER JOIN detail_pesanan dp ON ps.id_pesanan = dp.id_pesanan
            GROUP BY ps.id_pesanan, pl.nama
            HAVING COUNT(dp.id_produk) > 3
            ORDER BY jumlah_jenis DESC, ps.id_pesanan ASC
        ";

        return DB::select($sql);
    }

    /**
     * Ambil data master 1 pesanan beserta ringkasan nilai dan jumlah jenis produk.
     */
    public function findMasterById(int $idPesanan): ?object
    {
        // -- Soal 3b & 3j (master detail)
        $sql = "
            SELECT
                ps.id_pesanan,
                ps.tgl_pesan,
                ps.status,
                pl.id_pelanggan,
                pl.nama AS nama_pelanggan,
                pl.email AS email_pelanggan,
                pl.kota AS kota_pelanggan,
                COUNT(dp.id_produk) AS jumlah_jenis,
                SUM(dp.jumlah) AS total_unit,
                SUM(dp.jumlah * dp.harga_satuan) AS total_nilai
            FROM pesanan ps
            INNER JOIN pelanggan pl ON ps.id_pelanggan = pl.id_pelanggan
            INNER JOIN detail_pesanan dp ON ps.id_pesanan = dp.id_pesanan
            WHERE ps.id_pesanan = ?
            GROUP BY ps.id_pesanan, ps.tgl_pesan, ps.status, pl.id_pelanggan, pl.nama, pl.email, pl.kota
        ";

        return DB::selectOne($sql, [$idPesanan]);
    }

    /**
     * Ambil daftar baris item detail_pesanan untuk 1 pesanan.
     * Wajib memakai dp.harga_satuan (harga historis saat transaksi).
     */
    public function getDetailItems(int $idPesanan): array
    {
        $sql = "
            SELECT
                dp.id_produk,
                pr.nama_produk,
                pr.kategori,
                dp.jumlah,
                dp.harga_satuan,
                (dp.jumlah * dp.harga_satuan) AS subtotal
            FROM detail_pesanan dp
            INNER JOIN produk pr ON dp.id_produk = pr.id_produk
            WHERE dp.id_pesanan = ?
            ORDER BY dp.id_produk ASC
        ";

        return DB::select($sql, [$idPesanan]);
    }

    /**
     * Ambil daftar status tujuan yang sah dari status saat ini.
     */
    public function getStatusBerikutnya(string $statusSekarang): array
    {
        return self::TRANSISI_STATUS[$statusSekarang] ?? [];
    }

    /**
     * Ubah status pesanan dengan validasi diagram status (T12).
     * Jika status berubah dari 'diproses' ke 'batal', stok produk otomatis dikembalikan.
     */
    public function ubahStatus(int $idPesanan, string $statusBaru): void
    {
        DB::beginTransaction();

        try {
            $pesanan = DB::selectOne(
                "SELECT id_pesanan, status FROM pesanan WHERE id_pesanan = ? FOR UPDATE",
                [$idPesanan]
            );

            if (!$pesanan) {
                throw new RuntimeException("Pesanan #{$idPesanan} tidak ditemukan.");
            }

            $statusLama    = $pesanan->status;
            $statusDiizin  = $this->getStatusBerikutnya($statusLama);

            if (empty($statusDiizin)) {
                throw new RuntimeException(
                    "Pesanan berstatus '{$statusLama}' sudah terkunci permanen dan tidak dapat diubah lagi."
                );
            }

            if (!in_array($statusBaru, $statusDiizin, true)) {
                $opsi = implode(' atau ', $statusDiizin);
                throw new RuntimeException(
                    "Transisi tidak sah: dari '{$statusLama}' hanya boleh diubah ke {$opsi}."
                );
            }

            DB::update(
                "UPDATE pesanan SET status = ? WHERE id_pesanan = ?",
                [$statusBaru, $idPesanan]
            );

            // Jika dibatalkan dari 'diproses', kembalikan stok setiap produk dalam transaksi yang sama
            if ($statusLama === 'diproses' && $statusBaru === 'batal') {
                $items = DB::select(
                    "SELECT id_produk, jumlah FROM detail_pesanan WHERE id_pesanan = ?",
                    [$idPesanan]
                );

                foreach ($items as $item) {
                    DB::update(
                        "UPDATE produk SET stok = stok + ? WHERE id_produk = ?",
                        [$item->jumlah, $item->id_produk]
                    );
                }
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}