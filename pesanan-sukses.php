<?php
/**
 * Halaman Pesanan Berhasil - Pekarangan Institute
 * Menampilkan ringkasan pesanan, kode referensi ORD, petunjuk pembayaran, dan konfirmasi WhatsApp.
 * Melindungi privasi pemesan: tidak menampilkan nomor WhatsApp dan alamat lengkap.
 */

require_once __DIR__ . '/includes/fungsi.php';

$kode = trim($_GET['kode'] ?? '');

$pesanan = null;
if ($kode !== '') {
    try {
        $stmt = db()->prepare("
            SELECT kode, nama_produk, harga_satuan, jumlah, total, status, is_contoh, dibuat_pada
            FROM pesanan
            WHERE kode = :kode
            LIMIT 1
        ");
        $stmt->execute([':kode' => $kode]);
        $pesanan = $stmt->fetch() ?: null;
    } catch (Throwable $e) {
        $pesanan = null;
    }
}

// Jika kode tidak ditemukan, tampilkan 404 ramah
if (!$pesanan) {
    http_response_code(404);
    $judul_halaman = 'Pesanan Tidak Ditemukan';
    $menu_aktif = 'produk';
    require_once __DIR__ . '/includes/header.php';
    ?>
    <div class="kontainer">
        <div class="kotak-404">
            <h1>404</h1>
            <h2>Pesanan Tidak Ditemukan</h2>
            <p>Maaf, kode pesanan yang Anda tuju tidak tercatat dalam sistem kami. Pastikan tautan yang Anda gunakan sudah benar.</p>
            <div>
                <a href="<?= url('produk.php') ?>" class="tombol tombol-utama">&larr; Kembali ke Katalog Produk</a>
            </div>
        </div>
    </div>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$judul_halaman = 'Pesanan Diterima - ' . $pesanan['kode'];
$menu_aktif = 'produk';
$deskripsi_halaman = 'Pesanan Anda telah kami terima dan menunggu verifikasi pembayaran.';

$info_pembayaran = ambil_pengaturan('info_pembayaran', '[Nama Bank] [Nomor Rekening] a.n. Pekarangan Institute');
$nomor_admin_wa  = ambil_pengaturan('whatsapp', '');

// Buat template teks untuk konfirmasi via WhatsApp
$pesan_wa = sprintf(
    "Halo Admin Pekarangan Institute, saya ingin mengonfirmasi pembayaran untuk pesanan:\n\nKode Pesanan: %s\nProduk: %s\nJumlah: %d item\nTotal: %s\n\nMohon petunjuk verifikasi pembayarannya. Terima kasih!",
    $pesanan['kode'],
    $pesanan['nama_produk'],
    (int)$pesanan['jumlah'],
    rupiah($pesanan['total'])
);

$tautan_konfirmasi_wa = tautan_wa($nomor_admin_wa, $pesan_wa);

require_once __DIR__ . '/includes/header.php';
?>

<div class="kontainer">
    <div class="kartu-sukses">
        <div class="sukses-ikon" aria-hidden="true">&#10003;</div>

        <div style="text-align: center; margin-bottom: 1.5rem;">
            <?php if (!empty($pesanan['is_contoh'])): ?>
                <div style="margin-bottom: 0.5rem;">
                    <span class="lencana-contoh">DATA CONTOH</span>
                </div>
            <?php endif; ?>
            <h1 style="font-size: 1.75rem; margin-bottom: 0.5rem; color: var(--biru-tua);">Pesanan Berhasil Dibuat</h1>
            <p style="color: var(--teks-redup);">
                Terima kasih! Pesanan Anda telah tercatat di sistem kami dan sedang menunggu proses verifikasi pembayaran.
            </p>
        </div>

        <!-- Kode Pesanan Unik -->
        <div class="kode-kotak">
            <div class="kode-kotak-label">Kode Pesanan Anda</div>
            <div class="kode-kotak-nilai"><?= e($pesanan['kode']) ?></div>
            <span style="font-size: 0.8125rem; color: var(--teks-redup); display: block; margin-top: 0.25rem;">
                Simpan kode ini sebagai bukti dan referensi konfirmasi pembayaran Anda.
            </span>
        </div>

        <!-- Banner Verifikasi Manual Sesuai Aturan Bisnis -->
        <div class="banner-peringatan" role="note">
            <span style="font-size: 1.25rem;" aria-hidden="true">&#9432;</span>
            <div>
                <strong>Pembayaran diverifikasi manual oleh admin</strong>
                <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem;">
                    Stok produk akan berkurang secara otomatis setelah status pembayaran Anda diverifikasi oleh tim kami.
                </p>
            </div>
        </div>

        <!-- Ringkasan Pesanan (Tanpa PII Alamat/WA Lengkap) -->
        <h3 style="font-size: 1.125rem; margin-top: 1.5rem; margin-bottom: 0.75rem;">Ringkasan Pesanan</h3>
        <table class="tabel-ringkasan" aria-label="Rincian Pembelian">
            <thead>
                <tr>
                    <th scope="col">Keterangan</th>
                    <th scope="col" style="text-align: right;">Rincian</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Produk</td>
                    <td><strong><?= e($pesanan['nama_produk']) ?></strong></td>
                </tr>
                <tr>
                    <td>Harga Satuan</td>
                    <td><?= rupiah($pesanan['harga_satuan']) ?></td>
                </tr>
                <tr>
                    <td>Jumlah</td>
                    <td><?= (int)$pesanan['jumlah'] ?> item</td>
                </tr>
                <tr>
                    <td>Status Pesanan</td>
                    <td>
                        <span class="lencana lencana-kuning">Menunggu Pembayaran</span>
                    </td>
                </tr>
                <tr class="baris-total">
                    <td>Total Tagihan</td>
                    <td style="color: var(--biru);"><?= rupiah($pesanan['total']) ?></td>
                </tr>
            </tbody>
        </table>

        <!-- Instruksi Pembayaran Bank -->
        <div class="kotak-instruksi-bank">
            <h4>Instruksi Pembayaran Transfer:</h4>
            <p style="margin-bottom: 0.5rem; color: var(--teks);">
                Silakan lakukan transfer sebesar <strong><?= rupiah($pesanan['total']) ?></strong> ke rekening resmi berikut:
            </p>
            <div style="background: var(--putih); border: 1px solid var(--garis); padding: 0.75rem 1rem; border-radius: var(--radius-sm); font-weight: 600; color: var(--biru-tua); margin-bottom: 0.5rem;">
                <?= nl2br(e($info_pembayaran)) ?>
            </div>
            <p style="font-size: 0.8125rem; color: var(--teks-redup); margin: 0;">
                Sertakan kode <strong><?= e($pesanan['kode']) ?></strong> pada berita transfer jika memungkinkan.
            </p>
        </div>

        <!-- Tombol Aksi Konfirmasi WhatsApp -->
        <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-top: 1.5rem;">
            <a href="<?= e($tautan_konfirmasi_wa) ?>" target="_blank" rel="noopener noreferrer" class="tombol tombol-utama tombol-blok" style="background-color: #25D366; border-color: #25D366; color: #FFFFFF;">
                <span aria-hidden="true">&#9742;</span> Konfirmasi via WhatsApp
            </a>

            <div class="grup-tombol" style="justify-content: center; margin-top: 0.5rem;">
                <a href="<?= url('produk.php') ?>" class="tombol tombol-sekunder">
                    Lanjut Belanja
                </a>
                <a href="<?= url() ?>" class="tombol tombol-sekunder">
                    Halaman Utama
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
