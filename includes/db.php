<?php
/**
 * Koneksi Database Menggunakan PDO
 * Mengembalikan satu koneksi PDO (singleton) dengan opsi keamanan dan charset utf8mb4.
 */

// Muat konfigurasi bila belum dimuat
if (!defined('DB_HOST')) {
    $berkas_config = __DIR__ . '/config.php';
    if (!file_exists($berkas_config)) {
        http_response_code(500);
        echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Konfigurasi Belum Ada</title><style>body{font-family:sans-serif;background:#F6F8FB;color:#1B2430;padding:2rem;}div{max-width:540px;margin:3rem auto;background:#fff;padding:2rem;border-radius:14px;border:1px solid #E2E8F0;box-shadow:0 4px 16px rgba(0,0,0,0.06);}</style></head><body><div><h2>Konfigurasi Belum Siap</h2><p>Berkas <code>includes/config.php</code> belum ditemukan.</p><p>Silakan salin berkas <code>includes/config.sample.php</code> menjadi <code>includes/config.php</code> dan sesuaikan kredensial database Anda.</p></div></body></html>';
        exit;
    }
    require_once $berkas_config;
}

/**
 * Mengembalikan objek koneksi PDO
 *
 * @return PDO
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAMA . ';charset=utf8mb4';
        $opsi = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $opsi);
        } catch (PDOException $e) {
            http_response_code(500);
            if (defined('APP_ENV') && APP_ENV === 'lokal') {
                echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Koneksi Database Gagal</title><style>body{font-family:sans-serif;background:#F6F8FB;color:#1B2430;padding:2rem;}div{max-width:640px;margin:3rem auto;background:#fff;padding:2rem;border-radius:14px;border:1px solid #E2E8F0;}</style></head><body><div><h2>Koneksi Database Gagal (Mode Lokal)</h2><p>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p><p>Pastikan MySQL berjalan dan nama database sesuai di <code>includes/config.php</code>.</p></div></body></html>';
            } else {
                echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Database Belum Siap</title><style>body{font-family:sans-serif;background:#F6F8FB;color:#1B2430;padding:2rem;}div{max-width:540px;margin:3rem auto;background:#fff;padding:2rem;border-radius:14px;border:1px solid #E2E8F0;text-align:center;}</style></head><body><div><h2>Database Belum Siap</h2><p>Database belum siap, cek config.php atau hubungi pengelola sistem.</p></div></body></html>';
            }
            exit;
        }
    }

    return $pdo;
}
