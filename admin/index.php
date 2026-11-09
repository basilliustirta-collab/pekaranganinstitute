<?php
/**
 * Dashboard Administrator - Pekarangan Institute
 * Ringkasan 4 kartu terpisah (Penjualan, Donasi, Pesanan Menunggu, Donasi Menunggu),
 * data terbaru, aksi cepat, dan label data contoh transparan.
 */

require_once __DIR__ . '/../includes/fungsi.php';
wajib_login();

$menu_aktif = 'dashboard';
$judul_halaman = 'Dashboard';

// 1. Ringkasan Penjualan (Pesanan Dibayar ke atas)
$rekap_penjualan = ['total' => 0, 'jumlah' => 0, 'ada_contoh' => 0];
try {
    $stmt = db()->query("
        SELECT 
            COALESCE(SUM(total), 0) AS total, 
            COUNT(id) AS jumlah, 
            COALESCE(MAX(is_contoh), 0) AS ada_contoh 
        FROM pesanan 
        WHERE status IN ('dibayar', 'diproses', 'dikirim', 'selesai')
    ");
    $rekap_penjualan = $stmt->fetch() ?: $rekap_penjualan;
} catch (Throwable $e) {}

// 2. Ringkasan Donasi (Berhasil)
$rekap_donasi = ['total' => 0, 'jumlah' => 0, 'ada_contoh' => 0];
try {
    $stmt = db()->query("
        SELECT 
            COALESCE(SUM(jumlah), 0) AS total, 
            COUNT(id) AS jumlah, 
            COALESCE(MAX(is_contoh), 0) AS ada_contoh 
        FROM donasi 
        WHERE status = 'berhasil'
    ");
    $rekap_donasi = $stmt->fetch() ?: $rekap_donasi;
} catch (Throwable $e) {}

// 3. Pesanan Menunggu
$rekap_pesanan_menunggu = ['jumlah' => 0, 'ada_contoh' => 0];
try {
    $stmt = db()->query("
        SELECT 
            COUNT(id) AS jumlah, 
            COALESCE(MAX(is_contoh), 0) AS ada_contoh 
        FROM pesanan 
        WHERE status = 'menunggu'
    ");
    $rekap_pesanan_menunggu = $stmt->fetch() ?: $rekap_pesanan_menunggu;
} catch (Throwable $e) {}

// 4. Donasi Menunggu
$rekap_donasi_menunggu = ['jumlah' => 0, 'ada_contoh' => 0];
try {
    $stmt = db()->query("
        SELECT 
            COUNT(id) AS jumlah, 
            COALESCE(MAX(is_contoh), 0) AS ada_contoh 
        FROM donasi 
        WHERE status = 'menunggu'
    ");
    $rekap_donasi_menunggu = $stmt->fetch() ?: $rekap_donasi_menunggu;
} catch (Throwable $e) {}

// 5 Pesanan Terbaru
$pesanan_terbaru = [];
try {
    $stmt = db()->query("
        SELECT id, kode, nama_produk, jumlah, total, nama_pemesan, status, is_contoh, dibuat_pada 
        FROM pesanan 
        ORDER BY dibuat_pada DESC, id DESC 
        LIMIT 5
    ");
    $pesanan_terbaru = $stmt->fetchAll() ?: [];
} catch (Throwable $e) {}

// 5 Donasi Terbaru
$donasi_terbaru = [];
try {
    $stmt = db()->query("
        SELECT d.id, d.kode, d.nama_donatur, d.jumlah, d.anonim, d.status, d.is_contoh, d.dibuat_pada, p.judul AS nama_program 
        FROM donasi d 
        LEFT JOIN program p ON d.program_id = p.id 
        ORDER BY d.dibuat_pada DESC, d.id DESC 
        LIMIT 5
    ");
    $donasi_terbaru = $stmt->fetchAll() ?: [];
} catch (Throwable $e) {}

require_once __DIR__ . '/../includes/admin-header.php';
?>

<div class="admin-halaman-header">
    <div>
        <h1 class="admin-halaman-judul">Halo, <?= e($_SESSION['admin_nama'] ?? 'Admin') ?> 👋</h1>
        <p class="admin-halaman-subjudul">Ringkasan aktivitas transaksi dan donasi Pekarangan Institute hari ini.</p>
    </div>
</div>

<!-- 4 Kartu Ringkasan Terpisah -->
<section class="grid-ringkasan-4" aria-label="Ringkasan Utama">
    <!-- Kartu 1: Penjualan -->
    <div class="kartu-ringkasan kartu-penjualan">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <span class="kartu-ringkasan-label">Penjualan Produk</span>
            <?php if (!empty($rekap_penjualan['ada_contoh'])): ?>
                <span class="lencana-contoh">DATA CONTOH</span>
            <?php endif; ?>
        </div>
        <div class="kartu-ringkasan-nilai"><?= rupiah($rekap_penjualan['total']) ?></div>
        <div class="kartu-ringkasan-sub"><?= (int)$rekap_penjualan['jumlah'] ?> transaksi pesanan dibayar</div>
    </div>

    <!-- Kartu 2: Donasi -->
    <div class="kartu-ringkasan kartu-donasi">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <span class="kartu-ringkasan-label">Donasi Berhasil</span>
            <?php if (!empty($rekap_donasi['ada_contoh'])): ?>
                <span class="lencana-contoh">DATA CONTOH</span>
            <?php endif; ?>
        </div>
        <div class="kartu-ringkasan-nilai"><?= rupiah($rekap_donasi['total']) ?></div>
        <div class="kartu-ringkasan-sub"><?= (int)$rekap_donasi['jumlah'] ?> donasi terkonfirmasi</div>
    </div>

    <!-- Kartu 3: Pesanan Menunggu -->
    <div class="kartu-ringkasan kartu-pesanan-menunggu">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <span class="kartu-ringkasan-label">Pesanan Menunggu</span>
            <?php if (!empty($rekap_pesanan_menunggu['ada_contoh'])): ?>
                <span class="lencana-contoh">DATA CONTOH</span>
            <?php endif; ?>
        </div>
        <div class="kartu-ringkasan-nilai"><?= (int)$rekap_pesanan_menunggu['jumlah'] ?></div>
        <div class="kartu-ringkasan-sub">Perlu verifikasi pembayaran</div>
    </div>

    <!-- Kartu 4: Donasi Menunggu -->
    <div class="kartu-ringkasan kartu-donasi-menunggu">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <span class="kartu-ringkasan-label">Donasi Menunggu</span>
            <?php if (!empty($rekap_donasi_menunggu['ada_contoh'])): ?>
                <span class="lencana-contoh">DATA CONTOH</span>
            <?php endif; ?>
        </div>
        <div class="kartu-ringkasan-nilai"><?= (int)$rekap_donasi_menunggu['jumlah'] ?></div>
        <div class="kartu-ringkasan-sub">Perlu konfirmasi mutasi</div>
    </div>
</section>

<!-- Tombol Cepat -->
<section class="admin-tombol-cepat" aria-label="Aksi Cepat">
    <a href="<?= url('admin/produk-form.php') ?>" class="tombol tombol-utama">+ Tambah Produk</a>
    <a href="<?= url('admin/program-form.php') ?>" class="tombol tombol-utama">+ Buat Program</a>
    <a href="<?= url('admin/pesanan.php') ?>" class="tombol tombol-sekunder">Lihat Pesanan</a>
    <a href="<?= url('admin/donasi.php') ?>" class="tombol tombol-sekunder">Lihat Donasi</a>
</section>

<!-- Grid 2 Kolom: Pesanan Terbaru & Donasi Terbaru -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 1.5rem;">
    <!-- 5 Pesanan Terbaru -->
    <section class="admin-panel" aria-labelledby="judul-pesanan-terbaru">
        <div class="admin-panel-kepala">
            <h2 id="judul-pesanan-terbaru" class="admin-panel-judul">Pesanan Terbaru</h2>
            <a href="<?= url('admin/pesanan.php') ?>" style="font-size:0.875rem;font-weight:600;">Lihat Semua &rarr;</a>
        </div>
        <div style="padding: 0;">
            <?php if (empty($pesanan_terbaru)): ?>
                <p style="padding: 1.5rem; color: var(--teks-redup); text-align: center;">Belum ada pesanan.</p>
            <?php else: ?>
                <div class="admin-tabel-wadah" style="border:none;box-shadow:none;">
                    <table class="admin-tabel">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Pemesan</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pesanan_terbaru as $p): ?>
                                <tr>
                                    <td data-label="Kode">
                                        <strong><?= e($p['kode']) ?></strong>
                                        <?php if (!empty($p['is_contoh'])): ?>
                                            <br><span class="lencana-contoh" style="font-size:0.65rem;">CONTOH</span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Pemesan">
                                        <?= e($p['nama_pemesan']) ?>
                                        <div style="font-size:0.75rem;color:var(--teks-redup);"><?= e($p['nama_produk']) ?> (<?= (int)$p['jumlah'] ?>x)</div>
                                    </td>
                                    <td data-label="Total"><?= rupiah($p['total']) ?></td>
                                    <td data-label="Status">
                                        <span class="badge-status status-<?= e($p['status']) ?>"><?= e($p['status']) ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- 5 Donasi Terbaru -->
    <section class="admin-panel" aria-labelledby="judul-donasi-terbaru">
        <div class="admin-panel-kepala">
            <h2 id="judul-donasi-terbaru" class="admin-panel-judul">Donasi Terbaru</h2>
            <a href="<?= url('admin/donasi.php') ?>" style="font-size:0.875rem;font-weight:600;">Lihat Semua &rarr;</a>
        </div>
        <div style="padding: 0;">
            <?php if (empty($donasi_terbaru)): ?>
                <p style="padding: 1.5rem; color: var(--teks-redup); text-align: center;">Belum ada donasi.</p>
            <?php else: ?>
                <div class="admin-tabel-wadah" style="border:none;box-shadow:none;">
                    <table class="admin-tabel">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Donatur</th>
                                <th>Nominal</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($donasi_terbaru as $d): ?>
                                <tr>
                                    <td data-label="Kode">
                                        <strong><?= e($d['kode']) ?></strong>
                                        <?php if (!empty($d['is_contoh'])): ?>
                                            <br><span class="lencana-contoh" style="font-size:0.65rem;">CONTOH</span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Donatur">
                                        <?= !empty($d['anonim']) ? 'Anonim (' . e($d['nama_donatur']) . ')' : e($d['nama_donatur']) ?>
                                        <div style="font-size:0.75rem;color:var(--teks-redup);"><?= e($d['nama_program'] ?? 'Program Umum') ?></div>
                                    </td>
                                    <td data-label="Nominal"><?= rupiah($d['jumlah']) ?></td>
                                    <td data-label="Status">
                                        <span class="badge-status status-<?= e($d['status']) ?>"><?= e($d['status']) ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
