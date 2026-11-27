<?php

namespace App\Models; // Ubah ke App\Repositories jika memakai folder Repositories

use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class EtalaseRepository
{
    /**
     * Ambil daftar kategori unik dari produk yang masih aktif.
     */
    public function daftarKategori(): array
    {
        $sql = "
            SELECT DISTINCT p.kategori
            FROM produk p
            LEFT JOIN produk_nonaktif pn ON p.id_produk = pn.id_produk
            WHERE pn.id_produk IS NULL
            ORDER BY p.kategori ASC
        ";

        return DB::select($sql);
    }

    /**
     * Filter katalog produk (Soal 3a: BETWEEN, AND, tanpa subquery).
     * Otomatis menyembunyikan produk yang dinonaktifkan oleh Admin.
     */
    public function getProdukKatalog(
        ?string $kategori = null,
        ?float $hargaMin = null,
        ?float $hargaMax = null,
        ?string $cari = null,
        string $urut = 'termurah'
    ): array {
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
            LEFT JOIN produk_nonaktif pn ON p.id_produk = pn.id_produk
            WHERE pn.id_produk IS NULL
              AND p.harga BETWEEN ? AND ?
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

    /**
     * Ambil 1 produk aktif berdasarkan ID (untuk validasi keranjang).
     */
    public function findProdukAktif(string $idProduk): ?object
    {
        $sql = "
            SELECT p.id_produk, p.nama_produk, p.kategori, p.harga, p.stok
            FROM produk p
            LEFT JOIN produk_nonaktif pn ON p.id_produk = pn.id_produk
            WHERE p.id_produk = ?
              AND pn.id_produk IS NULL
        ";

        return DB::selectOne($sql, [$idProduk]);
    }

    /**
     * Daftar pelanggan untuk pilihan dropdown saat checkout.
     */
    public function daftarPelanggan(): array
    {
        return DB::select("SELECT id_pelanggan, nama, kota FROM pelanggan ORDER BY nama ASC");
    }

    /**
     * Proses checkout lengkap dalam SATU transaksi atomik (START TRANSACTION -> COMMIT / ROLLBACK).
     * Mengembalikan id_pesanan yang baru dibuat.
     */
    public function prosesCheckout(string $idPelanggan, array $keranjang, string $tglPesan): int
    {
        DB::beginTransaction();

        try {
            // 1. Pastikan pelanggan valid
            $pelanggan = DB::selectOne(
                "SELECT id_pelanggan FROM pelanggan WHERE id_pelanggan = ?",
                [$idPelanggan]
            );
            if (!$pelanggan) {
                throw new RuntimeException('Pelanggan yang dipilih tidak ditemukan.');
            }

            // 2. Generate id_pesanan baru secara aman (kunci baris agar tidak bentrok)
            $rowMax    = DB::selectOne("SELECT COALESCE(MAX(id_pesanan), 1000) + 1 AS next_id FROM pesanan FOR UPDATE");
            $idPesanan = (int) $rowMax->next_id;

            // 3. INSERT ke tabel master pesanan (status awal wajib 'diproses')
            DB::insert(
                "INSERT INTO pesanan (id_pesanan, id_pelanggan, tgl_pesan, status) VALUES (?, ?, ?, 'diproses')",
                [$idPesanan, $idPelanggan, $tglPesan]
            );

            // 4. Loop setiap item: kunci baris produk, cek stok real-time, INSERT detail, UPDATE stok
            foreach ($keranjang as $item) {
                $idProduk = $item['id_produk'];
                $jumlah   = (int) $item['jumlah'];

                if ($jumlah < 1) {
                    throw new RuntimeException('Jumlah pesanan minimal 1 unit.');
                }

                // Ambil harga kini & stok terbaru langsung dari DB dengan lock FOR UPDATE
                $produkDb = DB::selectOne(
                    "SELECT p.id_produk, p.nama_produk, p.harga, p.stok
                     FROM produk p
                     LEFT JOIN produk_nonaktif pn ON p.id_produk = pn.id_produk
                     WHERE p.id_produk = ? AND pn.id_produk IS NULL
                     FOR UPDATE",
                    [$idProduk]
                );

                if (!$produkDb) {
                    throw new RuntimeException("Produk {$item['nama_produk']} sudah tidak tersedia atau dinonaktifkan.");
                }

                if ((int) $produkDb->stok < $jumlah) {
                    throw new RuntimeException(
                        "Stok {$produkDb->nama_produk} tidak mencukupi (tersisa {$produkDb->stok}, diminta {$jumlah})."
                    );
                }

                // INSERT detail_pesanan dengan harga_satuan = produk.harga terkini
                DB::insert(
                    "INSERT INTO detail_pesanan (id_pesanan, id_produk, jumlah, harga_satuan) VALUES (?, ?, ?, ?)",
                    [$idPesanan, $idProduk, $jumlah, $produkDb->harga]
                );

                // UPDATE pengurangan stok secara kondisional
                $affected = DB::update(
                    "UPDATE produk SET stok = stok - ? WHERE id_produk = ? AND stok >= ?",
                    [$jumlah, $idProduk, $jumlah]
                );

                if ($affected !== 1) {
                    throw new RuntimeException("Gagal memperbarui stok untuk produk {$produkDb->nama_produk}.");
                }
            }

            DB::commit();
            return $idPesanan;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}