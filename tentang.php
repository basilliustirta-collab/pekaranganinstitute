<?php
/**
 * Halaman Tentang Kami (Tentang) - Pekarangan Institute
 * Menceritakan visi, tiga nilai utama, dan tata kelola alokasi dana dari pengaturan.
 */

require_once __DIR__ . '/includes/fungsi.php';

$judul_halaman = 'Tentang Kami';
$menu_aktif = 'tentang';

$tentang_teks = ambil_pengaturan('tentang_teks', 'Pekarangan Institute adalah inisiatif kewirausahaan sosial yang bergerak di bidang pemberdayaan pekarangan produktif.');
$alokasi_operasional = (int)ambil_pengaturan('alokasi_operasional', '30');
$alokasi_sosial = (int)ambil_pengaturan('alokasi_sosial', '70');
$catatan_transparansi = ambil_pengaturan('catatan_transparansi', 'Alokasi dana di atas merupakan data simulasi/contoh.');

require_once __DIR__ . '/includes/header.php';
?>

<div class="kontainer">
    <div class="bagian-kepala">
        <h1>Tentang Pekarangan Institute</h1>
        <p>Membangun kemandirian pangan, martabat keluarga, dan solidaritas sosial berbasis komunitas pekarangan.</p>
    </div>

    <!-- 1. Cerita & Profil -->
    <article class="artikel-konten">
        <h2>Inisiatif Kami</h2>
        <p><?= nl2br(e($tentang_teks)) ?></p>
        <p>
            Kami meyakini bahwa pekarangan rumah bukan sekadar lahan kosong di sekitar tempat tinggal, melainkan ruang hidup strategis yang menyimpan potensi besar untuk pemenuhan gizi keluarga, pelestarian keanekaragaman hayati nabati lokal, serta penguatan ketahanan ekonomi mikro komunitas.
        </p>

        <!-- 2. Tiga Nilai Utama -->
        <h2>Tiga Pilar: Berbelanja, Berbagi, Berdampak</h2>
        <div class="langkah-grid" style="margin: 1.5rem 0;">
            <div class="langkah-kartu langkah-1">
                <div class="langkah-ikon" aria-hidden="true">&#128722;</div>
                <h3>1. Berbelanja</h3>
                <p>Membeli produk pekarangan berkualitas langsung dari pekebun dan pengrajin lokal dengan harga yang adil bagi produsen.</p>
            </div>
            <div class="langkah-kartu langkah-2">
                <div class="langkah-ikon" aria-hidden="true">&#129309;</div>
                <h3>2. Berbagi</h3>
                <p>Menyisihkan surplus usaha dan memfasilitasi donasi publik secara transparan untuk mendanai bibit, pelatihan, dan pendampingan.</p>
            </div>
            <div class="langkah-kartu langkah-3">
                <div class="langkah-ikon" aria-hidden="true">&#127793;</div>
                <h3>3. Berdampak</h3>
                <p>Menghasilkan perubahan terukur: keluarga dhuafa memiliki akses pangan segar mandiri dan komunitas menjadi lebih tangguh.</p>
            </div>
        </div>

        <!-- 3. Bagaimana Dana Dikelola -->
        <h2>
            Bagaimana Dana Dikelola
            <span class="lencana-contoh">DATA CONTOH</span>
        </h2>
        <p>
            Sebagai inisiatif kewirausahaan sosial, kami menjunjung tinggi keterbukaan dan akuntabilitas. Alokasi pemanfaatan dana komersial dan donasi sosial diatur secara proporsional:
        </p>

        <div class="kotak-alokasi">
            <div style="display: flex; justify-content: space-between; align-items: center; font-weight: 600;">
                <span>Distribusi Alokasi Dana</span>
                <span class="lencana-contoh">SIMULASI</span>
            </div>

            <!-- Bilah Proporsi Alokasi Dana -->
            <div class="alokasi-bar" role="img" aria-label="Grafik proporsi: <?= $alokasi_sosial ?>% Dana Sosial dan <?= $alokasi_operasional ?>% Operasional">
                <div class="alokasi-segmen-sosial" style="width: <?= $alokasi_sosial ?>%;"></div>
                <div class="alokasi-segmen-operasional" style="width: <?= $alokasi_operasional ?>%;"></div>
            </div>

            <div class="alokasi-keterangan">
                <div>
                    <span class="alokasi-titik alokasi-titik-sosial"></span>
                    <strong><?= $alokasi_sosial ?>% Dana Sosial & Pemberdayaan</strong>
                    <div style="color: var(--teks-redup); font-size: 0.8125rem;">
                        Pengadaan bibit, pelatihan budidaya, pendampingan warga dhuafa, dan program kemasyarakatan.
                    </div>
                </div>
                <div>
                    <span class="alokasi-titik alokasi-titik-operasional"></span>
                    <strong><?= $alokasi_operasional ?>% Operasional & Keberlanjutan</strong>
                    <div style="color: var(--teks-redup); font-size: 0.8125rem;">
                        Logistik, pemeliharaan sistem, pengemasan, administrasi, dan koordinasi relawan lapangan.
                    </div>
                </div>
            </div>

            <?php if (!empty($catatan_transparansi)): ?>
                <p style="margin-top: 1rem; font-size: 0.8125rem; color: var(--teks-redup); border-top: 1px dashed var(--garis); padding-top: 0.75rem;">
                    <em>Catatan: <?= e($catatan_transparansi) ?></em>
                </p>
            <?php endif; ?>
        </div>

        <p>
            Laporan pertanggungjawaban berkala dapat diakses secara terbuka oleh publik melalui halaman transparansi kami.
        </p>
        <div style="margin-top: 1.5rem;">
            <a href="<?= url('transparansi.php') ?>" class="tombol tombol-sekunder">
                Lihat Laporan Transparansi &rarr;
            </a>
        </div>
    </article>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
