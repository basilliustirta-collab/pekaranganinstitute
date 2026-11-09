<?php
/**
 * Aksi Pemrosesan Status Pesanan - Pekarangan Institute
 * Mengontrol alur status pesanan (state machine) yang valid dan
 * mengelola pemotongan serta pengembalian stok produk via transaksi basis data (PDO transaction).
 * Tanpa fitur hapus data.
 */

require_once __DIR__ . '/../includes/fungsi.php';
wajib_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !cek_csrf()) {
    pesan_flash('merah', 'Permintaan tidak valid atau token keamanan kedaluwarsa.');
    alihkan('admin/pesanan.php');
}

$id = (int)($_POST['id'] ?? 0);
$status_baru = trim($_POST['status_baru'] ?? '');

if ($id <= 0 || !in_array($status_baru, ['dibayar', 'diproses', 'dikirim', 'selesai', 'batal'], true)) {
    pesan_flash('merah', 'Parameter pembaruan status pesanan tidak valid.');
    alihkan('admin/pesanan.php');
}

$pdo = db();

try {
    $pdo->beginTransaction();

    // Kunci baris pesanan untuk menghindari race condition
    $stmt_pes = $pdo->prepare("
        SELECT id, kode, produk_id, nama_produk, jumlah, status 
        FROM pesanan 
        WHERE id = :id 
        FOR UPDATE
    ");
    $stmt_pes->execute([':id' => $id]);
    $pesanan = $stmt_pes->fetch();

    if (!$pesanan) {
        $pdo->rollBack();
        pesan_flash('merah', 'Data pesanan tidak ditemukan.');
        alihkan('admin/pesanan.php');
    }

    $status_lama = $pesanan['status'];

    // Peta transisi status yang sah sesuai aturan bisnis
    $transisi_sah = [
        'menunggu' => ['dibayar', 'batal'],
        'dibayar'  => ['diproses', 'batal'],
        'diproses' => ['dikirim'],
        'dikirim'  => ['selesai'],
        'selesai'  => [],
        'batal'    => []
    ];

    if (!in_array($status_baru, $transisi_sah[$status_lama] ?? [], true)) {
        $pdo->rollBack();
        pesan_flash('merah', 'Transisi status dari "' . $status_lama . '" ke "' . $status_baru . '" tidak diizinkan.');
        alihkan('admin/pesanan.php');
    }

    $produk_id = (int)$pesanan['produk_id'];
    $jumlah_pesan = (int)$pesanan['jumlah'];

    // 1. Kasus Menunggu -> Dibayar (Potong stok tepat satu kali)
    if ($status_lama === 'menunggu' && $status_baru === 'dibayar') {
        $stmt_prod = $pdo->prepare("SELECT id, nama, stok FROM produk WHERE id = :id FOR UPDATE");
        $stmt_prod->execute([':id' => $produk_id]);
        $produk = $stmt_prod->fetch();

        if (!$produk) {
            $pdo->rollBack();
            pesan_flash('merah', 'Produk terkait tidak ditemukan.');
            alihkan('admin/pesanan.php');
        }

        if ((int)$produk['stok'] < $jumlah_pesan) {
            $pdo->rollBack();
            pesan_flash('merah', 'Stok produk "' . $produk['nama'] . '" tidak mencukupi (stok tersedia: ' . (int)$produk['stok'] . ', dibutuhkan: ' . $jumlah_pesan . '). Status pesanan tidak dapat diubah menjadi dibayar.');
            alihkan('admin/pesanan.php');
        }

        // Potong stok
        $stmt_kurang_stok = $pdo->prepare("UPDATE produk SET stok = stok - :jumlah, diubah_pada = NOW() WHERE id = :id");
        $stmt_kurang_stok->execute([':jumlah' => $jumlah_pesan, ':id' => $produk_id]);

        // Perbarui status pesanan
        $stmt_upd = $pdo->prepare("UPDATE pesanan SET status = 'dibayar', diubah_pada = NOW() WHERE id = :id");
        $stmt_upd->execute([':id' => $id]);

        $pdo->commit();
        pesan_flash('hijau', 'Status pesanan ' . $pesanan['kode'] . ' berhasil diubah menjadi Dibayar. Stok produk "' . $produk['nama'] . '" dipotong ' . $jumlah_pesan . ' item.');
        alihkan('admin/pesanan.php');
    }

    // 2. Kasus Dibayar -> Batal (Kembalikan stok produk)
    if ($status_lama === 'dibayar' && $status_baru === 'batal') {
        $stmt_tambah_stok = $pdo->prepare("UPDATE produk SET stok = stok + :jumlah, diubah_pada = NOW() WHERE id = :id");
        $stmt_tambah_stok->execute([':jumlah' => $jumlah_pesan, ':id' => $produk_id]);

        $stmt_upd = $pdo->prepare("UPDATE pesanan SET status = 'batal', diubah_pada = NOW() WHERE id = :id");
        $stmt_upd->execute([':id' => $id]);

        $pdo->commit();
        pesan_flash('hijau', 'Pesanan ' . $pesanan['kode'] . ' dibatalkan. Stok produk sebanyak ' . $jumlah_pesan . ' item telah dikembalikan ke inventaris.');
        alihkan('admin/pesanan.php');
    }

    // 3. Transisi lainnya tanpa efek stok (menunggu->batal, dibayar->diproses, diproses->dikirim, dikirim->selesai)
    $stmt_upd = $pdo->prepare("UPDATE pesanan SET status = :status, diubah_pada = NOW() WHERE id = :id");
    $stmt_upd->execute([':status' => $status_baru, ':id' => $id]);

    $pdo->commit();

    $label_status = match ($status_baru) {
        'diproses' => 'Diproses',
        'dikirim'  => 'Dikirim',
        'selesai'  => 'Selesai',
        'batal'    => 'Dibatalkan',
        default    => $status_baru,
    };

    pesan_flash('hijau', 'Status pesanan ' . $pesanan['kode'] . ' berhasil diubah menjadi: ' . $label_status . '.');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    pesan_flash('merah', pesan_error_db('Terjadi kesalahan sistem', $e));
}

alihkan('admin/pesanan.php');
