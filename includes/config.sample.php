<?php
/**
 * Konfigurasi Sistem Pekarangan Institute
 *
 * Salin file ini menjadi 'config.php' di folder yang sama (includes/config.php)
 * dan sesuaikan pengaturan sesuai dengan server lokal/produksi Anda.
 * File config.php bersifat rahasia dan tidak boleh di-commit ke repositori git.
 */

// Konfigurasi Database MySQL/MariaDB
define('DB_HOST', 'localhost');
define('DB_NAMA', 'pekarangan');
define('DB_USER', 'root');
define('DB_PASS', '');

// URL Dasar Aplikasi (sesuaikan dengan nama folder di web server)
// Contoh: '/pekarangan-v2' atau '/pekarangan' atau '' jika di root domain
define('BASE_URL', '/pekarangan-v2');

// Lingkungan Aplikasi: 'lokal' atau 'produksi'
// Pada 'lokal', error PHP akan ditampilkan untuk memudahkan pengembangan.
// Pada 'produksi', error disembunyikan demi keamanan.
define('APP_ENV', 'lokal');

// Batas Ukuran Unggahan Berkas (dalam satuan byte)
define('BATAS_UNGGAH_GAMBAR', 2 * 1024 * 1024); // 2 MB
define('BATAS_UNGGAH_BERKAS', 5 * 1024 * 1024); // 5 MB

// Zona Waktu Baku
date_default_timezone_set('Asia/Jakarta');

// Penanganan Tampilan Error
if (APP_ENV === 'lokal') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}
