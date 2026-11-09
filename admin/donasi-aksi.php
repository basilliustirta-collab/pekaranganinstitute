<?php
/**
 * Aksi Pemrosesan Donasi - Pekarangan Institute
 * Menangani perubahan status donasi (menunggu, berhasil, gagal).
 * Tanpa fitur hapus data untuk menjaga transparansi dan akuntabilitas publik.
 */

require_once __DIR__ . '/../includes/fungsi.php';
wajib_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !cek_csrf()) {
    pesan_flash('merah', 'Permintaan tidak valid atau token keamanan kedaluwarsa.');
    alihkan('admin/donasi.php');
}

$id = (int)($_POST['id'] ?? 0);
$status_baru = trim($_POST['status_baru'] ?? '');

if ($id <= 0 || !in_array($status_baru, ['menunggu', 'berhasil', 'gagal'], true)) {
    pesan_flash('merah', 'Parameter perubahan status donasi tidak valid.');
    alihkan('admin/donasi.php');
}

try {
    $stmt_cek = db()->prepare("SELECT kode, status FROM donasi WHERE id = :id LIMIT 1");
    $stmt_cek->execute([':id' => $id]);
    $donasi = $stmt_cek->fetch();

    if (!$donasi) {
        pesan_flash('merah', 'Data donasi tidak ditemukan.');
        alihkan('admin/donasi.php');
    }

    $stmt_upd = db()->prepare("UPDATE donasi SET status = :status, diubah_pada = NOW() WHERE id = :id");
    $stmt_upd->execute([':status' => $status_baru, ':id' => $id]);

    $label_status = match ($status_baru) {
        'berhasil' => 'Berhasil (Terkonfirmasi Masuk)',
        'gagal'    => 'Gagal / Dibatalkan',
        default    => 'Menunggu Verifikasi',
    };

    pesan_flash('hijau', 'Status donasi ' . $donasi['kode'] . ' berhasil diubah menjadi: ' . $label_status . '. Capaian program otomatis diperbarui.');
} catch (Throwable $e) {
    pesan_flash('merah', pesan_error_db('Gagal memperbarui status donasi', $e));
}

alihkan('admin/donasi.php');
