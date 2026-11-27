<?php

namespace App\Models; // Ubah ke App\Repositories jika repo Ketua memakai app/Repositories

use Illuminate\Support\Facades\DB;

class ProdukRepository
{
    /**
     * Query khusus Soal 3e: produk yang belum pernah dipesan sama sekali.
     * Konsep: LEFT JOIN + IS NULL (tanpa subquery).
     * Dapat dipanggil ulang oleh Anggota 3 di kartu Dashboard.
     */
    public function produkBelumPernahDipesan(): array
    {
        // -- Soal 3e
        $sql = "
            SELECT
                p.id_produk,
                p.nama_produk,
                p.kategori,
                p.harga,
                p.stok
            FROM produk p
            LEFT JOIN detail_pesanan dp ON p.id_produk = dp.id_produk
            WHERE dp.id_produk IS NULL
            ORDER BY p.id_produk ASC
        ";

        return DB::select($sql);
    }

    /**
     * Daftar produk untuk tabel Admin (mendukung pencarian, filter Soal 3e, status aktif, dan paginasi).
     */
    public function getDaftarProduk(?string $cari, bool $hanyaBelumDipesan, int $perPage, int $offset): array
    {
        $bindings = [];

        if ($hanyaBelumDipesan) {
            // -- Soal 3e (dengan filter pencarian & paginasi)
            $sql = "
                SELECT
                    p.id_produk,
                    p.nama_produk,
                    p.kategori,
                    p.harga,
                    p.stok,
                    0 AS jumlah_transaksi,
                    CASE WHEN pn.id_produk IS NULL THEN 1 ELSE 0 END AS is_aktif
                FROM produk p
                LEFT JOIN detail_pesanan dp ON p.id_produk = dp.id_produk
                LEFT JOIN produk_nonaktif pn ON p.id_produk = pn.id_produk
                WHERE dp.id_produk IS NULL
            ";

            if (!empty($cari)) {
                $sql .= " AND (p.id_produk LIKE ? OR p.nama_produk LIKE ? OR p.kategori LIKE ?)";
                $like = '%' . $cari . '%';
                array_push($bindings, $like, $like, $like);
            }

            $sql .= " ORDER BY p.id_produk ASC LIMIT ? OFFSET ?";
            array_push($bindings, $perPage, $offset);

            return DB::select($sql, $bindings);
        }

        $sql = "
            SELECT
                p.id_produk,
                p.nama_produk,
                p.kategori,
                p.harga,
                p.stok,
                COUNT(dp.id_produk) AS jumlah_transaksi,
                CASE WHEN pn.id_produk IS NULL THEN 1 ELSE 0 END AS is_aktif
            FROM produk p
            LEFT JOIN detail_pesanan dp ON p.id_produk = dp.id_produk
            LEFT JOIN produk_nonaktif pn ON p.id_produk = pn.id_produk
            WHERE 1 = 1
        ";

        if (!empty($cari)) {
            $sql .= " AND (p.id_produk LIKE ? OR p.nama_produk LIKE ? OR p.kategori LIKE ?)";
            $like = '%' . $cari . '%';
            array_push($bindings, $like, $like, $like);
        }

        $sql .= "
            GROUP BY p.id_produk, p.nama_produk, p.kategori, p.harga, p.stok, pn.id_produk
            ORDER BY p.id_produk ASC
            LIMIT ? OFFSET ?
        ";
        array_push($bindings, $perPage, $offset);

        return DB::select($sql, $bindings);
    }

    /**
     * Hitung total baris untuk kebutuhan paginasi (tanpa subquery).
     */
    public function countDaftarProduk(?string $cari, bool $hanyaBelumDipesan): int
    {
        $bindings = [];

        if ($hanyaBelumDipesan) {
            // -- Soal 3e
            $sql = "
                SELECT COUNT(p.id_produk) AS total
                FROM produk p
                LEFT JOIN detail_pesanan dp ON p.id_produk = dp.id_produk
                WHERE dp.id_produk IS NULL
            ";
        } else {
            $sql = "SELECT COUNT(p.id_produk) AS total FROM produk p WHERE 1 = 1";
        }

        if (!empty($cari)) {
            $sql .= " AND (p.id_produk LIKE ? OR p.nama_produk LIKE ? OR p.kategori LIKE ?)";
            $like = '%' . $cari . '%';
            array_push($bindings, $like, $like, $like);
        }

        $row = DB::selectOne($sql, $bindings);
        return (int) ($row->total ?? 0);
    }

    /**
     * Ambil satu produk berdasarkan id_produk.
     */
    public function findById(string $idProduk): ?object
    {
        return DB::selectOne(
            "SELECT id_produk, nama_produk, kategori, harga, stok FROM produk WHERE id_produk = ?",
            [$idProduk]
        );
    }

    /**
     * Generate ID otomatis format P09, P10, dst. (CHAR(3)).
     */
    public function generateIdBaru(): string
    {
        $last = DB::selectOne("SELECT id_produk FROM produk ORDER BY id_produk DESC LIMIT 1");

        if (!$last) {
            return 'P01';
        }

        $angka = (int) substr($last->id_produk, 1) + 1;
        return 'P' . str_pad((string) $angka, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Tambah produk baru.
     */
    public function insertProduk(string $idProduk, string $nama, string $kategori, float $harga, int $stok): void
    {
        DB::insert(
            "INSERT INTO produk (id_produk, nama_produk, kategori, harga, stok) VALUES (?, ?, ?, ?, ?)",
            [$idProduk, $nama, $kategori, $harga, $stok]
        );
    }

    /**
     * Ubah data produk.
     */
    public function updateProduk(string $idProduk, string $nama, string $kategori, float $harga, int $stok): int
    {
        return DB::update(
            "UPDATE produk SET nama_produk = ?, kategori = ?, harga = ?, stok = ? WHERE id_produk = ?",
            [$nama, $kategori, $harga, $stok, $idProduk]
        );
    }

    /**
     * Cek apakah produk sudah pernah muncul di detail_pesanan.
     */
    public function pernahDipesan(string $idProduk): bool
    {
        $row = DB::selectOne(
            "SELECT COUNT(*) AS total FROM detail_pesanan WHERE id_produk = ?",
            [$idProduk]
        );
        return ((int) ($row->total ?? 0)) > 0;
    }

    /**
     * Hapus produk (aman karena dicek FK dan dilengkapi catch di Controller).
     */
    public function deleteProduk(string $idProduk): int
    {
        return DB::delete("DELETE FROM produk WHERE id_produk = ?", [$idProduk]);
    }

    /**
     * Toggle status aktif/nonaktif produk menggunakan tabel produk_nonaktif.
     * Mengembalikan true jika sekarang aktif, false jika dinonaktifkan.
     */
    public function toggleAktif(string $idProduk): bool
    {
        $ada = DB::selectOne(
            "SELECT id_produk FROM produk_nonaktif WHERE id_produk = ?",
            [$idProduk]
        );

        if ($ada) {
            DB::delete("DELETE FROM produk_nonaktif WHERE id_produk = ?", [$idProduk]);
            return true;
        }

        DB::insert("INSERT INTO produk_nonaktif (id_produk) VALUES (?)", [$idProduk]);
        return false;
    }
}