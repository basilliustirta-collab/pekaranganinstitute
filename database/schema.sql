-- Skema Database Pekarangan Institute
-- Karakter set utf8mb4 untuk kompatibilitas penuh multibyte
CREATE DATABASE IF NOT EXISTS pekarangan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pekarangan;

-- 1. Tabel Administrator
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    dibuat_pada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabel Produk
CREATE TABLE IF NOT EXISTS produk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    kategori VARCHAR(100) NOT NULL,
    harga INT NOT NULL CHECK (harga >= 0),
    stok INT NOT NULL DEFAULT 0 CHECK (stok >= 0),
    deskripsi_singkat VARCHAR(255) NOT NULL,
    deskripsi TEXT NOT NULL,
    penjual VARCHAR(150) NOT NULL,
    foto VARCHAR(255) NULL,
    status ENUM('draf', 'tampil', 'arsip') NOT NULL DEFAULT 'draf',
    is_contoh TINYINT(1) NOT NULL DEFAULT 0,
    dibuat_pada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    diubah_pada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_produk_status (status),
    INDEX idx_produk_kategori (kategori)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabel Program Sosial
CREATE TABLE IF NOT EXISTS program (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    ringkasan VARCHAR(255) NOT NULL,
    cerita TEXT NOT NULL,
    target_dana INT NOT NULL CHECK (target_dana > 0),
    tenggat DATE NULL,
    lokasi VARCHAR(150) NOT NULL,
    penerima_manfaat VARCHAR(255) NOT NULL,
    foto_sampul VARCHAR(255) NULL,
    status ENUM('draf', 'aktif', 'selesai') NOT NULL DEFAULT 'draf',
    is_contoh TINYINT(1) NOT NULL DEFAULT 0,
    dibuat_pada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    diubah_pada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_program_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabel Pesanan Produk (Terpisah dari Donasi)
CREATE TABLE IF NOT EXISTS pesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(30) NOT NULL UNIQUE,
    produk_id INT NOT NULL,
    nama_produk VARCHAR(200) NOT NULL,
    harga_satuan INT NOT NULL CHECK (harga_satuan >= 0),
    jumlah INT NOT NULL CHECK (jumlah > 0),
    total INT NOT NULL CHECK (total >= 0),
    nama_pemesan VARCHAR(150) NOT NULL,
    whatsapp VARCHAR(30) NOT NULL,
    alamat TEXT NOT NULL,
    catatan TEXT NULL,
    status ENUM('menunggu', 'dibayar', 'diproses', 'dikirim', 'selesai', 'batal') NOT NULL DEFAULT 'menunggu',
    is_contoh TINYINT(1) NOT NULL DEFAULT 0,
    dibuat_pada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    diubah_pada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_pesanan_status (status),
    INDEX idx_pesanan_produk_id (produk_id),
    CONSTRAINT fk_pesanan_produk FOREIGN KEY (produk_id) REFERENCES produk(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabel Donasi Program (Terpisah dari Pesanan)
CREATE TABLE IF NOT EXISTS donasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(30) NOT NULL UNIQUE,
    program_id INT NOT NULL,
    nama_donatur VARCHAR(150) NOT NULL,
    whatsapp VARCHAR(30) NOT NULL,
    jumlah INT NOT NULL CHECK (jumlah > 0),
    anonim TINYINT(1) NOT NULL DEFAULT 0,
    pesan TEXT NULL,
    status ENUM('menunggu', 'berhasil', 'gagal') NOT NULL DEFAULT 'menunggu',
    is_contoh TINYINT(1) NOT NULL DEFAULT 0,
    dibuat_pada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    diubah_pada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_donasi_status (status),
    INDEX idx_donasi_program_id (program_id),
    CONSTRAINT fk_donasi_program FOREIGN KEY (program_id) REFERENCES program(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tabel Laporan Transparansi
CREATE TABLE IF NOT EXISTS laporan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    periode VARCHAR(100) NOT NULL,
    ringkasan TEXT NOT NULL,
    berkas VARCHAR(255) NOT NULL,
    status ENUM('draf', 'tampil') NOT NULL DEFAULT 'draf',
    is_contoh TINYINT(1) NOT NULL DEFAULT 0,
    dibuat_pada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_laporan_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Tabel Pengaturan Sistem
CREATE TABLE IF NOT EXISTS pengaturan (
    kunci VARCHAR(50) NOT NULL PRIMARY KEY,
    nilai TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
