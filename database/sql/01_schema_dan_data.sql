-- =================================================================
-- Basis Data Lanjut - Mini Proyek Aplikasi Web (Kasus 3: TokoKita)
-- File: 01_schema_dan_data.sql
-- =================================================================

DROP DATABASE IF EXISTS db_tokodaring;
CREATE DATABASE db_tokodaring;
USE db_tokodaring;

-- 1. Tabel Pelanggan
CREATE TABLE pelanggan (
    id_pelanggan CHAR(3) PRIMARY KEY,
    nama VARCHAR(50) NOT NULL,
    kota VARCHAR(30),
    email VARCHAR(50) UNIQUE
) ENGINE=InnoDB;

-- 2. Tabel Produk
CREATE TABLE produk (
    id_produk CHAR(3) PRIMARY KEY,
    nama_produk VARCHAR(50) NOT NULL,
    kategori VARCHAR(30) NOT NULL,
    harga DECIMAL(12,2) NOT NULL,
    stok INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- 3. Tabel Pesanan
CREATE TABLE pesanan (
    id_pesanan INT PRIMARY KEY,
    id_pelanggan CHAR(3) NOT NULL,
    tgl_pesan DATE NOT NULL,
    status ENUM('diproses', 'dikirim', 'selesai', 'batal') NOT NULL DEFAULT 'diproses',
    FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id_pelanggan)
) ENGINE=InnoDB;

-- 4. Tabel Detail Pesanan
CREATE TABLE detail_pesanan (
    id_pesanan INT NOT NULL,
    id_produk CHAR(3) NOT NULL,
    jumlah INT NOT NULL,
    harga_satuan DECIMAL(12,2) NOT NULL,
    PRIMARY KEY (id_pesanan, id_produk),
    FOREIGN KEY (id_pesanan) REFERENCES pesanan(id_pesanan),
    FOREIGN KEY (id_produk) REFERENCES produk(id_produk)
) ENGINE=InnoDB;

-- =================================================================
-- DATA AWAL
-- =================================================================

INSERT INTO pelanggan VALUES
('C01', 'Rina Marlina', 'Jakarta', 'rina@mail.com'),
('C02', 'Yoga Pratama', 'Bandung', 'yoga@mail.com'),
('C03', 'Lina Kusuma', 'Jakarta', 'lina@mail.com'),
('C04', 'Bayu Saputra', 'Malang', 'bayu@mail.com'),
('C05', 'Tika Amelia', 'Yogyakarta', 'tika@mail.com'),
('C06', 'Dimas Ardian', 'Semarang', 'dimas@mail.com');

INSERT INTO produk VALUES
('P01', 'Laptop 14 inci', 'Elektronik', 7500000, 10),
('P02', 'Mouse Nirkabel', 'Elektronik', 150000, 50),
('P03', 'Monitor 24 inci', 'Elektronik', 2100000, 15),
('P04', 'Headphone Bluetooth', 'Elektronik', 1250000, 20),
('P05', 'Kaos Polos', 'Fashion', 85000, 100),
('P06', 'Sepatu Lari', 'Fashion', 450000, 30),
('P07', 'Buku Catatan A5', 'Alat Tulis', 25000, 200),
('P08', 'Tas Ransel', 'Fashion', 300000, 25);

INSERT INTO pesanan VALUES
(1001, 'C01', '2026-01-15', 'selesai'),
(1002, 'C02', '2026-02-20', 'selesai'),
(1003, 'C01', '2026-03-05', 'selesai'),
(1004, 'C03', '2026-03-28', 'batal'),
(1005, 'C04', '2026-06-12', 'selesai'),
(1006, 'C05', '2026-09-09', 'dikirim'),
(1007, 'C01', '2026-10-30', 'selesai'),
(1008, 'C02', '2026-11-10', 'diproses');

INSERT INTO detail_pesanan VALUES
(1001, 'P01', 1, 7500000),
(1001, 'P02', 1, 150000),
(1002, 'P02', 1, 150000),
(1002, 'P05', 3, 85000),
(1002, 'P06', 1, 450000),
(1002, 'P07', 5, 25000),
(1003, 'P03', 1, 2100000),
(1003, 'P04', 1, 1250000),
(1004, 'P01', 1, 7500000),
(1005, 'P05', 2, 85000),
(1005, 'P06', 2, 450000),
(1006, 'P04', 1, 1200000),
(1006, 'P07', 10, 25000),
(1007, 'P01', 1, 7300000),
(1007, 'P02', 2, 150000),
(1008, 'P03', 1, 2100000),
(1008, 'P05', 4, 85000);