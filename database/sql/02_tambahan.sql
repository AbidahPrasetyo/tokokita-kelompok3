-- Tabel pendamping untuk opsi "Nonaktifkan Produk" tanpa mengubah struktur tabel inti produk
CREATE TABLE IF NOT EXISTS produk_nonaktif (
    id_produk CHAR(3) PRIMARY KEY,
    dinonaktifkan_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_produk) REFERENCES produk(id_produk) ON DELETE CASCADE
) ENGINE=InnoDB;