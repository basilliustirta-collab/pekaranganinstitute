<?php
/**
 * Halaman Transparansi Dana & Akuntabilitas Publik
 * Pekarangan Institute
 *
 * Menampilkan:
 * 1. Rekapitulasi Penjualan Produk & Donasi Terkumpul secara TERPISAH (tidak pernah dijumlahkan).
 * 2. Visualisasi Alokasi Dana dari Pengaturan (CSS Bar murni tanpa library grafik).
 * 3. Donasi per Program Sosial (tabel progres donasi berhasil).
 * 4. Daftar Laporan Transparansi Berkala yang berstatus 'tampil'.
 * 5. Catatan transparansi dan label DATA CONTOH.
 */

require_once __DIR__ . '/includes/fungsi.php';

$judul_halaman = 'Transparansi Dana';
$menu_aktif = 'transparansi';
$deskripsi_halaman = 'Laporan akuntabilitas dana penjualan produk dan donasi masyarakat Pekarangan Institute secara terbuka dan transparan.';

// 1. Rekap Penjualan Produk (hanya pesanan berstatus dibayar, diproses, dikirim, selesai)
$rekap_penjualan = ['total_penjualan' => 0, 'total_transaksi' => 0, 'ada_contoh' => 0];
try {
    $stmt_jual = db()->query("
        SELECT 
            COALESCE(SUM(total), 0) AS total_penjualan,
            COUNT(id) AS total_transaksi,
            COALESCE(MAX(is_contoh), 0) AS ada_contoh
        FROM pesanan
        WHERE status IN ('dibayar', 'diproses', 'dikirim', 'selesai')
    ");
    $rekap_penjualan = $stmt_jual->fetch() ?: $rekap_penjualan;
} catch (Throwable $e) {
    // Penanganan error fallback
}

// 2. Rekap Donasi Program (hanya donasi berstatus 'berhasil')
$rekap_donasi = ['total_donasi' => 0, 'total_donatur' => 0, 'ada_contoh' => 0];
try {
    $stmt_donasi = db()->query("
        SELECT 
            COALESCE(SUM(jumlah), 0) AS total_donasi,
            COUNT(id) AS total_donatur,
            COALESCE(MAX(is_contoh), 0) AS ada_contoh
        FROM donasi
        WHERE status = 'berhasil'
    ");
    $rekap_donasi = $stmt_donasi->fetch() ?: $rekap_donasi;
} catch (Throwable $e) {
    // Penanganan error fallback
}

// 3. Alokasi Dana dari Pengaturan
$alokasi_sosial = (int)ambil_pengaturan('alokasi_sosial', '70');
$alokasi_operasional = (int)ambil_pengaturan('alokasi_operasional', '30');
$catatan_transparansi = ambil_pengaturan('catatan_transparansi', 'Alokasi dana operasional dan dana sosial di atas merupakan simulasi akuntabilitas dana.');

// Normalisasi persentase bila total tidak tepat 100
$total_alokasi = $alokasi_sosial + $alokasi_operasional;
if ($total_alokasi <= 0) {
    $alokasi_sosial = 70;
    $alokasi_operasional = 30;
}

// 4. Daftar Program Sosial & Donasi Masuk
$daftar_program = [];
try {
    $stmt_prog = db()->query("
        SELECT id, judul, slug, target_dana, status, is_contoh
        FROM program
        WHERE status IN ('aktif', 'selesai')
        ORDER BY CASE WHEN status = 'aktif' THEN 1 ELSE 2 END, dibuat_pada DESC
    ");
    $daftar_program = $stmt_prog->fetchAll() ?: [];
} catch (Throwable $e) {
    $daftar_program = [];
}

// 5. Daftar Laporan Publik Berstatus 'tampil'
$daftar_laporan = [];
try {
    $stmt_lap = db()->query("
        SELECT id, judul, periode, ringkasan, berkas, is_contoh, dibuat_pada
        FROM laporan
        WHERE status = 'tampil'
        ORDER BY dibuat_pada DESC
    ");
    $daftar_laporan = $stmt_lap->fetchAll() ?: [];
} catch (Throwable $e) {
    $daftar_laporan = [];
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="kontainer">
    <div class="bagian-kepala">
        <h1>Transparansi & Akuntabilitas</h1>
        <p>
            Prinsip utama Pekarangan Institute adalah keterbukaan. Kami memisahkan secara ketat antara dana usaha komersial dan dana amanah donasi publik.
        </p>
    </div>

    <!-- 1. Dua Ringkasan Terpisah & Berlabel Jelas (Aturan Bisnis: DILARANG Dijumlahkan) -->
    <section class="bagian" aria-labelledby="judul-ringkasan-dana">
        <h2 id="judul-ringkasan-dana" style="font-size: 1.25rem; margin-bottom: 1rem; color: var(--teks);">
            Ringkasan Penerimaan Dana (Terpisah)
        </h2>

        <div class="transparansi-rekap">
            <!-- Kartu Penjualan Produk -->
            <div class="rekap-kartu rekap-kartu-penjualan">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                    <span class="rekap-label">Penjualan Produk Pekarangan</span>
                    <?php if (!empty($rekap_penjualan['ada_contoh'])): ?>
                        <span class="lencana-contoh">DATA CONTOH</span>
                    <?php endif; ?>
                </div>
                <div class="rekap-angka"><?= rupiah($rekap_penjualan['total_penjualan']) ?></div>
                <div class="rekap-keterangan">
                    Dari <strong><?= (int)$rekap_penjualan['total_transaksi'] ?></strong> pesanan terverifikasi (status dibayar, diproses, dikirim, atau selesai). Dana hasil penjualan digunakan untuk operasional mitra dan pemberdayaan pekebun binaan.
                </div>
            </div>

            <!-- Kartu Donasi Terkumpul -->
            <div class="rekap-kartu rekap-kartu-donasi">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                    <span class="rekap-label">Donasi Program Sosial</span>
                    <?php if (!empty($rekap_donasi['ada_contoh'])): ?>
                        <span class="lencana-contoh">DATA CONTOH</span>
                    <?php endif; ?>
                </div>
                <div class="rekap-angka"><?= rupiah($rekap_donasi['total_donasi']) ?></div>
                <div class="rekap-keterangan">
                    Dari <strong><?= (int)$rekap_donasi['total_donatur'] ?></strong> donasi berhasil terverifikasi admin. Seluruh dana donasi disalurkan utuh untuk program sosial kemasyarakatan yang berjalan.
                </div>
            </div>
        </div>
    </section>

    <!-- 2. Alokasi Pengelolaan Dana (Batang Horizontal CSS) -->
    <section class="bagian" aria-labelledby="judul-alokasi-dana">
        <div class="artikel-konten">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.5rem;">
                <h2 id="judul-alokasi-dana" style="margin: 0; font-size: 1.25rem;">Simulasi Alokasi Pengelolaan Dana</h2>
                <span class="lencana-contoh">DATA CONTOH</span>
            </div>
            <p style="color: var(--teks-redup); font-size: 0.9375rem;">
                Komitmen pembagian alokasi dana dari setiap pendapatan usaha dan kemitraan Pekarangan Institute:
            </p>

            <!-- Batang Horizontal CSS Murni -->
            <div class="alokasi-bar" role="progressbar" aria-label="Alokasi Dana" aria-valuenow="<?= $alokasi_sosial ?>" aria-valuemin="0" aria-valuemax="100">
                <div class="alokasi-segmen-sosial" style="width: <?= $alokasi_sosial ?>%;" title="Sosial: <?= $alokasi_sosial ?>%"></div>
                <div class="alokasi-segmen-operasional" style="width: <?= $alokasi_operasional ?>%;" title="Operasional: <?= $alokasi_operasional ?>%"></div>
            </div>

            <div class="alokasi-keterangan">
                <div>
                    <span class="alokasi-titik alokasi-titik-sosial"></span>
                    <strong><?= $alokasi_sosial ?>%</strong> Dana Program Sosial & Pemberdayaan Komunitas
                </div>
                <div>
                    <span class="alokasi-titik alokasi-titik-operasional"></span>
                    <strong><?= $alokasi_operasional ?>%</strong> Operasional Kemitraan, Edukasi, & Pengembangan
                </div>
            </div>

            <?php if (!empty($catatan_transparansi)): ?>
                <div style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--garis); font-size: 0.8125rem; color: var(--teks-redup);">
                    <strong>Catatan:</strong> <?= e($catatan_transparansi) ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- 3. Rincian Donasi per Program Sosial -->
    <section class="bagian" aria-labelledby="judul-donasi-program">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
            <h2 id="judul-donasi-program" style="font-size: 1.25rem; margin: 0;">Penyaluran Donasi per Program</h2>
            <span style="font-size: 0.8125rem; color: var(--teks-redup);">Hanya menghitung donasi berstatus berhasil</span>
        </div>

        <?php if (!empty($daftar_program)): ?>
            <div class="tabel-responsif">
                <table class="tabel-data" aria-label="Tabel Donasi per Program">
                    <thead>
                        <tr>
                            <th scope="col">Nama Program</th>
                            <th scope="col">Status</th>
                            <th scope="col" style="text-align: right;">Target Dana</th>
                            <th scope="col" style="text-align: right;">Terkumpul</th>
                            <th scope="col" style="min-width: 140px;">Capaian (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($daftar_program as $p): ?>
                            <?php
                            $prog_info = progres_program((int)$p['id']);
                            $target = (int)$p['target_dana'];
                            $terkumpul = (int)$prog_info['terkumpul'];
                            $persen_asli = $target > 0 ? (int)round(($terkumpul / $target) * 100) : 0;
                            $persen_visual = min(100, $persen_asli);
                            ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 600;">
                                        <a href="<?= url('program-detail.php?slug=' . urlencode($p['slug'])) ?>">
                                            <?= e($p['judul']) ?>
                                        </a>
                                    </div>
                                    <?php if (!empty($p['is_contoh'])): ?>
                                        <span class="lencana-contoh" style="margin-top: 0.25rem;">DATA CONTOH</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($p['status'] === 'aktif'): ?>
                                        <span class="lencana lencana-hijau">Aktif</span>
                                    <?php else: ?>
                                        <span class="lencana lencana-kuning">Selesai</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right; font-weight: 500;">
                                    <?= rupiah($target) ?>
                                </td>
                                <td style="text-align: right; font-weight: 700; color: var(--oranye);">
                                    <?= rupiah($terkumpul) ?>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <div class="progress-track" style="flex: 1; height: 8px; margin: 0;" role="progressbar" aria-valuenow="<?= $persen_visual ?>" aria-valuemin="0" aria-valuemax="100">
                                            <div class="progress-fill" style="width: <?= $persen_visual ?>%;"></div>
                                        </div>
                                        <span style="font-size: 0.75rem; font-weight: 600; min-width: 38px; text-align: right;">
                                            <?= $persen_asli ?>%
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="kotak-kosong">
                <p>Belum ada program sosial yang terdata.</p>
            </div>
        <?php endif; ?>
    </section>

    <!-- 4. Daftar Laporan Transparansi Berkala -->
    <section class="bagian" aria-labelledby="judul-laporan-transparansi">
        <h2 id="judul-laporan-transparansi" style="font-size: 1.25rem; margin-bottom: 1rem;">Laporan Transparansi Berkala</h2>

        <?php if (!empty($daftar_laporan)): ?>
            <div class="laporan-daftar">
                <?php foreach ($daftar_laporan as $lap): ?>
                    <?php
                    $jalur_berkas = __DIR__ . '/uploads/' . $lap['berkas'];
                    $ada_berkas = file_exists($jalur_berkas);
                    ?>
                    <article class="laporan-kartu">
                        <div class="laporan-info">
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem; flex-wrap: wrap;">
                                <span class="lencana lencana-biru"><?= e($lap['periode']) ?></span>
                                <?php if (!empty($lap['is_contoh'])): ?>
                                    <span class="lencana-contoh">DATA CONTOH</span>
                                <?php endif; ?>
                            </div>
                            <h3 style="font-size: 1.125rem; margin-bottom: 0.375rem;"><?= e($lap['judul']) ?></h3>
                            <p style="font-size: 0.875rem; color: var(--teks-redup); margin: 0;">
                                <?= e($lap['ringkasan']) ?>
                            </p>
                        </div>

                        <div>
                            <?php if ($ada_berkas): ?>
                                <a href="<?= url('uploads/' . e($lap['berkas'])) ?>" class="tombol tombol-sekunder" target="_blank" rel="noopener noreferrer">
                                    Unduh Berkas (PDF)
                                </a>
                            <?php else: ?>
                                <span class="tombol tombol-sekunder" style="opacity: 0.6; cursor: not-allowed;" title="Berkas laporan contoh belum diunggah ke folder uploads">
                                    Unduh Berkas (Simulasi)
                                </span>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="kotak-kosong">
                <h3>Belum ada laporan yang dipublikasikan saat ini.</h3>
                <p>Laporan audit dan pertanggungjawaban kegiatan akan diperbarui secara berkala pada setiap akhir kuartal.</p>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
