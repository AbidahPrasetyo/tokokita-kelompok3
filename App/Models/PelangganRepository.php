<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class PelangganRepository
{
    /**
     * Daftar seluruh pelanggan beserta jumlah pesanannya (mendukung pencarian).
     */
    public function getDaftarPelanggan(?string $cari = null): array
    {
        $sql = "
            SELECT
                pl.id_pelanggan,
                pl.nama,
                pl.email,
                pl.kota,
                COUNT(ps.id_pesanan) AS jumlah_pesanan
            FROM pelanggan pl
            LEFT JOIN pesanan ps ON pl.id_pelanggan = ps.id_pelanggan
            WHERE 1 = 1
        ";

        $bindings = [];

        if (!empty($cari)) {
            $sql .= " AND (pl.id_pelanggan LIKE ? OR pl.nama LIKE ? OR pl.email LIKE ? OR pl.kota LIKE ?)";
            $like = '%' . $cari . '%';
            array_push($bindings, $like, $like, $like, $like);
        }

        $sql .= "
            GROUP BY pl.id_pelanggan, pl.nama, pl.email, pl.kota
            ORDER BY pl.id_pelanggan ASC
        ";

        return DB::select($sql, $bindings);
    }

    /**
     * Sebaran jumlah pelanggan per kota.
     * Menjawab Soal 3f (COUNT, GROUP BY, tanpa subquery).
     */
    public function sebaranPerKota(): array
    {
        // -- Soal 3f
        $sql = "
            SELECT
                kota,
                COUNT(id_pelanggan) AS jumlah_pelanggan
            FROM pelanggan
            GROUP BY kota
            ORDER BY jumlah_pelanggan DESC, kota ASC
        ";

        return DB::select($sql);
    }

    /**
     * Pelanggan prioritas: total belanja tahun 2026 lebih dari Rp10.000.000.
     * Menjawab Soal 3d (JOIN 3 tabel + HAVING, tanpa subquery).
     * Dapat dipanggil ulang oleh Anggota 3 di panel Dashboard.
     */
    public function pelangganPrioritas(int $tahun = 2026, float $batasMinimal = 10000000): array
    {
        // -- Soal 3d
        $sql = "
            SELECT
                pl.id_pelanggan,
                pl.nama,
                pl.email,
                pl.kota,
                SUM(dp.jumlah * dp.harga_satuan) AS total_belanja
            FROM pelanggan pl
            INNER JOIN pesanan ps ON pl.id_pelanggan = ps.id_pelanggan
            INNER JOIN detail_pesanan dp ON ps.id_pesanan = dp.id_pesanan
            WHERE YEAR(ps.tgl_pesan) = ?
              AND ps.status <> 'batal'
            GROUP BY pl.id_pelanggan, pl.nama, pl.email, pl.kota
            HAVING SUM(dp.jumlah * dp.harga_satuan) > ?
            ORDER BY total_belanja DESC
        ";

        return DB::select($sql, [$tahun, $batasMinimal]);
    }

    /**
     * Daftar pelanggan yang belum pernah memesan sama sekali (target promo).
     * Menjawab Soal 3i (LEFT JOIN + IS NULL, tanpa subquery).
     * Dapat dipanggil ulang oleh Anggota 3 di panel Dashboard.
     */
    public function pelangganBelumPernahMemesan(): array
    {
        // -- Soal 3i
        $sql = "
            SELECT
                pl.id_pelanggan,
                pl.nama,
                pl.email,
                pl.kota
            FROM pelanggan pl
            LEFT JOIN pesanan ps ON pl.id_pelanggan = ps.id_pelanggan
            WHERE ps.id_pesanan IS NULL
            ORDER BY pl.id_pelanggan ASC
        ";

        return DB::select($sql);
    }

    public function findById(string $idPelanggan): ?object
    {
        return DB::selectOne(
            "SELECT id_pelanggan, nama, email, kota FROM pelanggan WHERE id_pelanggan = ?",
            [$idPelanggan]
        );
    }

    public function emailSudahDipakai(string $email, ?string $kecualiId = null): bool
    {
        if ($kecualiId !== null) {
            $row = DB::selectOne(
                "SELECT COUNT(*) AS total FROM pelanggan WHERE email = ? AND id_pelanggan <> ?",
                [$email, $kecualiId]
            );
        } else {
            $row = DB::selectOne(
                "SELECT COUNT(*) AS total FROM pelanggan WHERE email = ?",
                [$email]
            );
        }

        return ((int) ($row->total ?? 0)) > 0;
    }

    /**
     * Generate ID otomatis format C07, C08, dst. (CHAR(3)).
     */
    public function generateIdBaru(): string
    {
        $last = DB::selectOne("SELECT id_pelanggan FROM pelanggan ORDER BY id_pelanggan DESC LIMIT 1");

        if (!$last) {
            return 'C01';
        }

        $angka = (int) substr($last->id_pelanggan, 1) + 1;
        return 'C' . str_pad((string) $angka, 2, '0', STR_PAD_LEFT);
    }

    public function insertPelanggan(string $id, string $nama, string $email, string $kota): void
    {
        DB::insert(
            "INSERT INTO pelanggan (id_pelanggan, nama, email, kota) VALUES (?, ?, ?, ?)",
            [$id, $nama, $email, $kota]
        );
    }

    public function updatePelanggan(string $id, string $nama, string $email, string $kota): int
    {
        return DB::update(
            "UPDATE pelanggan SET nama = ?, email = ?, kota = ? WHERE id_pelanggan = ?",
            [$nama, $email, $kota, $id]
        );
    }

    public function punyaPesanan(string $id): bool
    {
        $row = DB::selectOne(
            "SELECT COUNT(*) AS total FROM pesanan WHERE id_pelanggan = ?",
            [$id]
        );
        return ((int) ($row->total ?? 0)) > 0;
    }

    public function deletePelanggan(string $id): int
    {
        return DB::delete("DELETE FROM pelanggan WHERE id_pelanggan = ?", [$id]);
    }
}