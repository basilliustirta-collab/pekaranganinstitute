<?php
/**
 * Halaman Beranda (Index) - Pekarangan Institute
 * Menampilkan Hero, Tiga Langkah, Produk Terbaru, Program Berjalan, dan Ajakan Akhir.
 */

require_once __DIR__ . '/includes/fungsi.php';

$judul_halaman = 'Beranda';
$menu_aktif = 'beranda';
$deskripsi_halaman = ambil_pengaturan('hero_teks');

// Ambil 3 produk terbaru yang berstatus 'tampil'
$daftar_produk = [];
try {
    $stmt_produk = db()->prepare("
        SELECT id, nama, slug, kategori, harga, stok, deskripsi_singkat, foto, is_contoh
        FROM produk
        WHERE status = 'tampil'
        ORDER BY dibuat_pada DESC
        LIMIT 3
    ");
    $stmt_produk->execute();
    $daftar_produk = $stmt_produk->fetchAll();
} catch (Throwable $e) {
    $daftar_produk = [];
}

// Ambil 2 program aktif terbaru
$daftar_program = [];
try {
    $stmt_program = db()->prepare("
        SELECT id, judul, slug, ringkasan, target_dana, foto_sampul, is_contoh
        FROM program
        WHERE status = 'aktif'
        ORDER BY dibuat_pada DESC
        LIMIT 2
    ");
    $stmt_program->execute();
    $daftar_program = $stmt_program->fetchAll();
} catch (Throwable $e) {
    $daftar_program = [];
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="kontainer">
    <!-- 1. Hero Section -->
    <section class="hero" aria-labelledby="hero-judul">
        <div class="hero-grid">
            <div class="hero-konten">
                <span class="hero-label">BERBELANJA &bull; BERBAGI &bull; BERDAMPAK</span>
                <h1 class="hero-judul" id="hero-judul">
                    <?= e(ambil_pengaturan('hero_judul', 'Berbelanja, Berbagi, Berdampak.')) ?>
                </h1>
                <p class="hero-teks">
                    <?= e(ambil_pengaturan('hero_teks', 'Inisiatif kewirausahaan sosial yang menghubungkan produk hasil pekarangan dengan pemberdayaan dan program sosial kemasyarakatan.')) ?>
                </p>
                <div class="grup-tombol">
                    <a href="<?= url('produk.php') ?>" class="tombol tombol-utama">Belanja Sekarang</a>
                    <a href="<?= url('program.php') ?>" class="tombol tombol-sekunder">Lihat Program</a>
                </div>
            </div>
            <div class="hero-media">
                <div class="foto-wadah">
                    <div class="foto-kosong" aria-label="Placeholder foto hero">[Foto Pekarangan]</div>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. Tiga Langkah (Prinsip Pekarangan) -->
    <section class="bagian" aria-labelledby="judul-langkah">
        <div class="bagian-kepala">
            <h2 id="judul-langkah">Bagaimana Kami Bekerja</h2>
            <p>Setiap transaksi bernilai ganda: memberdayakan pekebun lokal sekaligus mengalirkan kebaikan untuk sesama.</p>
        </div>
        <div class="langkah-grid">
            <div class="langkah-kartu langkah-1">
                <div class="langkah-ikon" aria-hidden="true">&#128722;</div>
                <span class="langkah-nomor">Langkah 1</span>
                <h3>Berbelanja</h3>
                <p>Beli produk berkualitas hasil olahan komunitas pekarangan dan mitra lokal secara adil.</p>
            </div>
            <div class="langkah-kartu langkah-2">
                <div class="langkah-ikon" aria-hidden="true">&#129309;</div>
                <span class="langkah-nomor">Langkah 2</span>
                <h3>Berbagi</h3>
                <p>Sebagian hasil penjualan dan donasi Anda dialokasikan langsung untuk program sosial produktif.</p>
            </div>
            <div class="langkah-kartu langkah-3">
                <div class="langkah-ikon" aria-hidden="true">&#127793;</div>
                <span class="langkah-nomor">Langkah 3</span>
                <h3>Berdampak</h3>
                <p>Menguatkan kemandirian pangan keluarga dhuafa dan menciptakan lingkungan yang lestari.</p>
            </div>
        </div>
    </section>

    <!-- 3. Produk Terbaru -->
    <section class="bagian" aria-labelledby="judul-produk-terbaru">
        <div class="bagian-kepala">
            <h2 id="judul-produk-terbaru">Produk Pilihan Terbaru</h2>
            <p>Dukung kemandirian pangan keluarga melalui produk segar dan olahan alami langsung dari pekarangan.</p>
        </div>

        <?php if (!empty($daftar_produk)): ?>
            <div class="grid-kartu-3">
                <?php foreach ($daftar_produk as $item): ?>
                    <article class="kartu">
                        <div class="foto-wadah">
                            <?php if (!empty($item['foto']) && file_exists(__DIR__ . '/uploads/' . $item['foto'])): ?>
                                <img src="<?= url('uploads/' . e($item['foto'])) ?>" alt="<?= e($item['nama']) ?>">
                            <?php else: ?>
                                <div class="foto-kosong">[Foto]</div>
                            <?php endif; ?>
                        </div>
                        <div class="kartu-badan">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.375rem;">
                                <span class="kartu-kategori"><?= e($item['kategori']) ?></span>
                                <?php if (!empty($item['is_contoh'])): ?>
                                    <span class="lencana-contoh">DATA CONTOH</span>
                                <?php endif; ?>
                            </div>
                            <h3 class="kartu-judul">
                                <a href="<?= url('produk-detail.php?slug=' . urlencode($item['slug'])) ?>">
                                    <?= e($item['nama']) ?>
                                </a>
                            </h3>
                            <p class="kartu-ringkasan"><?= e($item['deskripsi_singkat']) ?></p>
                            <div class="kartu-harga"><?= rupiah($item['harga']) ?></div>
                            <div class="kartu-kaki">
                                <a href="<?= url('produk-detail.php?slug=' . urlencode($item['slug'])) ?>" class="tombol tombol-sekunder tombol-blok">
                                    Lihat Detail
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="bagian-aksi">
                <a href="<?= url('produk.php') ?>" class="tombol tombol-sekunder">Lihat Semua Produk &rarr;</a>
            </div>
        <?php else: ?>
            <div class="kotak-kosong">
                <p>Belum ada produk yang ditampilkan saat ini.</p>
            </div>
        <?php endif; ?>
    </section>

    <!-- 4. Program Berjalan -->
    <section class="bagian" aria-labelledby="judul-program-berjalan">
        <div class="bagian-kepala">
            <h2 id="judul-program-berjalan">Program Sosial Berjalan</h2>
            <p>Salurkan kepedulian Anda untuk program pemberdayaan dan ketahanan komunitas.</p>
        </div>

        <?php if (!empty($daftar_program)): ?>
            <div class="grid-kartu-2">
                <?php foreach ($daftar_program as $prog): ?>
                    <?php $progres = progres_program((int)$prog['id']); ?>
                    <article class="kartu">
                        <div class="foto-wadah">
                            <?php if (!empty($prog['foto_sampul']) && file_exists(__DIR__ . '/uploads/' . $prog['foto_sampul'])): ?>
                                <img src="<?= url('uploads/' . e($prog['foto_sampul'])) ?>" alt="<?= e($prog['judul']) ?>">
                            <?php else: ?>
                                <div class="foto-kosong">[Foto]</div>
                            <?php endif; ?>
                        </div>
                        <div class="kartu-badan">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.375rem;">
                                <span class="kartu-kategori">Program Sosial</span>
                                <?php if (!empty($prog['is_contoh'])): ?>
                                    <span class="lencana-contoh">DATA CONTOH</span>
                                <?php endif; ?>
                            </div>
                            <h3 class="kartu-judul">
                                <a href="<?= url('program-detail.php?slug=' . urlencode($prog['slug'])) ?>">
                                    <?= e($prog['judul']) ?>
                                </a>
                            </h3>
                            <p class="kartu-ringkasan"><?= e($prog['ringkasan']) ?></p>

                            <!-- Bilah Progres Donasi -->
                            <div class="progress-wadah">
                                <div class="progress-info">
                                    <span class="progress-info-terkumpul"><?= rupiah($progres['terkumpul']) ?> terkumpul</span>
                                    <span class="progress-info-target">Target: <?= rupiah($progres['target']) ?></span>
                                </div>
                                <div class="progress-track" role="progressbar" aria-valuenow="<?= (int)$progres['persen'] ?>" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-fill" style="width: <?= (int)$progres['persen'] ?>%;"></div>
                                </div>
                                <div style="font-size: 0.75rem; color: var(--teks-redup); margin-top: 0.25rem;">
                                    Tercapai <?= (int)$progres['persen'] ?>% &bull; <?= (int)$progres['jumlah_donatur'] ?> Donatur
                                </div>
                            </div>

                            <div class="kartu-kaki">
                                <a href="<?= url('donasi.php?program=' . urlencode($prog['slug'])) ?>" class="tombol tombol-oranye tombol-blok">
                                    Donasi Sekarang
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="bagian-aksi">
                <a href="<?= url('program.php') ?>" class="tombol tombol-sekunder">Lihat Semua Program &rarr;</a>
            </div>
        <?php else: ?>
            <div class="kotak-kosong">
                <p>Belum ada program aktif saat ini.</p>
            </div>
        <?php endif; ?>
    </section>

    <!-- 5. Ajakan Akhir (CTA) -->
    <section class="bagian">
        <div class="blok-ajakan">
            <h2>Mari Bergabung dalam Gerakan Kebaikan</h2>
            <p>Dukungan Anda dalam bentuk pembelian produk maupun donasi sukarela langsung memberikan dampak nyata bagi keluarga dan lingkungan binaan kami.</p>
            <div class="grup-tombol">
                <a href="<?= url('produk.php') ?>" class="tombol tombol-utama">Mulai Belanja</a>
                <a href="<?= url('donasi.php') ?>" class="tombol tombol-oranye">Berdonasi Sekarang</a>
            </div>
            <div>
                <a href="<?= url('transparansi.php') ?>" class="blok-ajakan-tautan">
                    Pelajari bagaimana dana dikelola dan disalurkan &rarr;
                </a>
            </div>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
