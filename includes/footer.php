<?php
/**
 * Footer Halaman Publik Pekarangan Institute
 * Menampilkan informasi kontak, navigasi cepat (termasuk login admin), dan penanda data contoh.
 */

require_once __DIR__ . '/fungsi.php';

$nama_situs = ambil_pengaturan('nama_situs', 'Pekarangan Institute');
$tagline_situs = ambil_pengaturan('tagline', 'Berbelanja, Berbagi, Berdampak');
$alamat = ambil_pengaturan('alamat', '');
$email = ambil_pengaturan('email', '');
$whatsapp = ambil_pengaturan('whatsapp', '');
?>
    </main>

    <!-- Footer Utama -->
    <footer class="footer-utama">
        <div class="kontainer">
            <div class="footer-grid">
                <!-- Kolom Tentang / Brand -->
                <div class="footer-tentang">
                    <div class="logo-tautan">
                        <span class="logo-teks-simbol" aria-hidden="true">&#9851;</span>
                        <span><?= e(strtoupper($nama_situs)) ?></span>
                    </div>
                    <p><?= e($tagline_situs) ?></p>
                    <p style="font-size: 0.875rem; color: var(--teks-redup); margin-top: 0.5rem;">
                        Pemberdayaan pekarangan produktif dan penguatan ekonomi komunitas menuju ketahanan pangan keluarga mandiri.
                    </p>
                </div>

                <!-- Kolom Tautan Cepat -->
                <div class="footer-kolom">
                    <h3 class="footer-judul">Navigasi</h3>
                    <ul class="footer-menu">
                        <li><a href="<?= url() ?>">Beranda</a></li>
                        <li><a href="<?= url('produk.php') ?>">Katalog Produk</a></li>
                        <li><a href="<?= url('program.php') ?>">Program Sosial</a></li>
                        <li><a href="<?= url('transparansi.php') ?>">Transparansi Dana</a></li>
                        <li><a href="<?= url('tentang.php') ?>">Tentang Kami</a></li>
                        <li><a href="<?= url('kontak.php') ?>">Hubungi Kami</a></li>
                        <li><a href="<?= url('login.php') ?>" style="color:var(--biru);font-weight:600;">Login Admin (Kelola Web)</a></li>
                    </ul>
                </div>

                <!-- Kolom Kontak Resmi -->
                <div class="footer-kolom footer-kontak">
                    <h3 class="footer-judul">Kontak</h3>
                    <?php if (!empty($alamat)): ?>
                        <p><strong>Alamat:</strong> <?= e($alamat) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($email)): ?>
                        <p><strong>Email:</strong> <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></p>
                    <?php endif; ?>
                    <?php if (!empty($whatsapp)): ?>
                        <p><strong>WhatsApp:</strong> <?= e($whatsapp) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Catatan Data Contoh & Hak Cipta -->
            <div class="footer-bawah">
                <div>
                    &copy; <?= date('Y') ?> <?= e($nama_situs) ?>. Hak Cipta Dilindungi.
                </div>
                <div class="footer-catatan-contoh">
                    <span class="lencana-contoh">DATA CONTOH</span>
                    Situs ini merupakan purwarupa (prototype). Seluruh data bertanda <strong>DATA CONTOH</strong> disajikan sebagai simulasi dan bukan data riil.
                </div>
            </div>
        </div>
    </footer>
</div><!-- /.wadah-situs -->

    <!-- Berkas JavaScript Interaksi Sederhana -->
    <script src="<?= url('assets/js/app.js') ?>"></script>
</body>
</html>
