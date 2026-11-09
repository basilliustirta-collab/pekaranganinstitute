<?php
/**
 * Katalog Produk - Pekarangan Institute
 * Menampilkan produk berstatus 'tampil' dengan fitur pencarian nama, filter kategori, pengurutan, dan paginasi.
 */

require_once __DIR__ . '/includes/fungsi.php';

$judul_halaman = 'Katalog Produk';
$menu_aktif = 'produk';
$deskripsi_halaman = 'Daftar produk berkualitas hasil budidaya pekarangan dan mitra binaan Pekarangan Institute.';

// Ambil daftar kategori unik dari produk berstatus 'tampil'
$daftar_kategori = [];
try {
    $stmt_kat = db()->query("
        SELECT DISTINCT kategori 
        FROM produk 
        WHERE status = 'tampil' 
        ORDER BY kategori ASC
    ");
    $daftar_kategori = $stmt_kat->fetchAll(PDO::FETCH_COLUMN) ?: [];
} catch (Throwable $e) {
    $daftar_kategori = [];
}

// Tangani parameter filter, pencarian, urutan, dan paginasi
$cari = trim($_GET['cari'] ?? $_GET['q'] ?? '');
$kategori = trim($_GET['kategori'] ?? '');
$urutan = trim($_GET['urutan'] ?? 'terbaru');
$halaman_ke = max(1, (int)($_GET['halaman'] ?? 1));
$per_halaman = 9;

// Bangun klausul WHERE dinamis dengan prepared statement
$kondisi = ["status = 'tampil'"];
$params = [];

if ($cari !== '') {
    $kondisi[] = "nama LIKE :cari";
    $params[':cari'] = '%' . $cari . '%';
}

if ($kategori !== '' && in_array($kategori, $daftar_kategori, true)) {
    $kondisi[] = "kategori = :kategori";
    $params[':kategori'] = $kategori;
}

$where_sql = implode(' AND ', $kondisi);

// Tentukan klausa pengurutan
$order_sql = 'dibuat_pada DESC';
if ($urutan === 'harga-naik') {
    $order_sql = 'harga ASC, dibuat_pada DESC';
} elseif ($urutan === 'harga-turun') {
    $order_sql = 'harga DESC, dibuat_pada DESC';
} else {
    $urutan = 'terbaru';
}

// Hitung total item yang sesuai
$total_item = 0;
try {
    $stmt_hitung = db()->prepare("SELECT COUNT(*) FROM produk WHERE {$where_sql}");
    $stmt_hitung->execute($params);
    $total_item = (int)$stmt_hitung->fetchColumn();
} catch (Throwable $e) {
    $total_item = 0;
}

// Bangun format URL untuk paginasi (mempertahankan query string aktif)
$parameter_url = [];
if ($cari !== '') {
    $parameter_url['cari'] = $cari;
}
if ($kategori !== '') {
    $parameter_url['kategori'] = $kategori;
}
if ($urutan !== 'terbaru') {
    $parameter_url['urutan'] = $urutan;
}
$parameter_url['halaman'] = '%d';
$url_format = url('produk.php?' . http_build_query($parameter_url));

// Hitung data paginasi
$paginasi = paginasi($total_item, $per_halaman, $halaman_ke, $url_format);

// Ambil data produk untuk halaman saat ini
$daftar_produk = [];
try {
    $sql_data = "
        SELECT id, nama, slug, kategori, harga, stok, deskripsi_singkat, penjual, foto, is_contoh
        FROM produk
        WHERE {$where_sql}
        ORDER BY {$order_sql}
        LIMIT :offset, :per_halaman
    ";
    $stmt_produk = db()->prepare($sql_data);
    foreach ($params as $k => $v) {
        $stmt_produk->bindValue($k, $v);
    }
    $stmt_produk->bindValue(':offset', $paginasi['offset'], PDO::PARAM_INT);
    $stmt_produk->bindValue(':per_halaman', $per_halaman, PDO::PARAM_INT);
    $stmt_produk->execute();
    $daftar_produk = $stmt_produk->fetchAll();
} catch (Throwable $e) {
    $daftar_produk = [];
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="kontainer">
    <div class="bagian-kepala">
        <h1>Katalog Produk</h1>
        <p>Setiap pembelian produk mendukung langsung kesejahteraan petani pekarangan dan program sosial kami.</p>
    </div>

    <!-- Bilah Filter & Pencarian -->
    <form action="<?= url('produk.php') ?>" method="GET" class="bar-filter" role="search" aria-label="Filter Produk">
        <div class="form-grup filter-cari">
            <label for="cari" class="form-label">Cari Produk</label>
            <input type="text" id="cari" name="cari" value="<?= e($cari) ?>" class="form-kontrol" placeholder="Ketik nama produk...">
        </div>

        <div class="form-grup filter-pilih">
            <label for="kategori" class="form-label">Kategori</label>
            <select id="kategori" name="kategori" class="form-kontrol">
                <option value="">Semua Kategori</option>
                <?php foreach ($daftar_kategori as $kat): ?>
                    <option value="<?= e($kat) ?>" <?= $kategori === $kat ? 'selected' : '' ?>>
                        <?= e($kat) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-grup filter-pilih">
            <label for="urutan" class="form-label">Urutkan</label>
            <select id="urutan" name="urutan" class="form-kontrol">
                <option value="terbaru" <?= $urutan === 'terbaru' ? 'selected' : '' ?>>Terbaru</option>
                <option value="harga-naik" <?= $urutan === 'harga-naik' ? 'selected' : '' ?>>Harga: Rendah ke Tinggi</option>
                <option value="harga-turun" <?= $urutan === 'harga-turun' ? 'selected' : '' ?>>Harga: Tinggi ke Rendah</option>
            </select>
        </div>

        <div class="filter-tombol grup-tombol">
            <button type="submit" class="tombol tombol-utama">Terapkan</button>
            <?php if ($cari !== '' || $kategori !== '' || $urutan !== 'terbaru'): ?>
                <a href="<?= url('produk.php') ?>" class="tombol tombol-sekunder" title="Hapus semua filter">Reset</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Grid Produk -->
    <?php if (!empty($daftar_produk)): ?>
        <div class="grid-kartu-3">
            <?php foreach ($daftar_produk as $item): ?>
                <?php
                $habis = (int)$item['stok'] <= 0;
                ?>
                <article class="kartu">
                    <div class="foto-wadah">
                        <?php if (!empty($item['foto']) && file_exists(__DIR__ . '/uploads/' . $item['foto'])): ?>
                            <img src="<?= url('uploads/' . e($item['foto'])) ?>" alt="<?= e($item['nama']) ?>">
                        <?php else: ?>
                            <div class="foto-kosong">[Foto]</div>
                        <?php endif; ?>
                    </div>
                    <div class="kartu-badan">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.375rem; gap: 0.5rem; flex-wrap: wrap;">
                            <span class="kartu-kategori"><?= e($item['kategori']) ?></span>
                            <?php if (!empty($item['is_contoh'])): ?>
                                <span class="lencana-contoh">DATA CONTOH</span>
                            <?php endif; ?>
                        </div>

                        <h2 class="kartu-judul">
                            <a href="<?= url('produk-detail.php?slug=' . urlencode($item['slug'])) ?>">
                                <?= e($item['nama']) ?>
                            </a>
                        </h2>

                        <p class="kartu-ringkasan"><?= e($item['deskripsi_singkat']) ?></p>

                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <div class="kartu-harga"><?= rupiah($item['harga']) ?></div>
                            <?php if ($habis): ?>
                                <span class="lencana lencana-merah">Stok habis</span>
                            <?php else: ?>
                                <span style="font-size: 0.8125rem; color: var(--teks-redup);">Stok: <?= (int)$item['stok'] ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="kartu-kaki">
                            <a href="<?= url('produk-detail.php?slug=' . urlencode($item['slug'])) ?>" class="tombol <?= $habis ? 'tombol-sekunder' : 'tombol-utama' ?> tombol-blok">
                                <?= $habis ? 'Lihat Detail' : 'Pesan Sekarang' ?>
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <!-- Paginasi -->
        <?php if ($paginasi['total_halaman'] > 1): ?>
            <nav class="paginasi-wadah" aria-label="Paginasi Halaman Produk">
                <?php if ($paginasi['ada_sebelum']): ?>
                    <a href="<?= e($paginasi['url_sebelum']) ?>" class="paginasi-link" aria-label="Halaman Sebelumnya">&larr; Sebelumnya</a>
                <?php else: ?>
                    <span class="paginasi-link paginasi-mati" aria-disabled="true">&larr; Sebelumnya</span>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $paginasi['total_halaman']; $i++): ?>
                    <?php if ($i === $paginasi['halaman']): ?>
                        <span class="paginasi-link paginasi-aktif" aria-current="page"><?= $i ?></span>
                    <?php else: ?>
                        <a href="<?= e(sprintf($url_format, $i)) ?>" class="paginasi-link"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($paginasi['ada_berikut']): ?>
                    <a href="<?= e($paginasi['url_berikut']) ?>" class="paginasi-link" aria-label="Halaman Selanjutnya">Selanjutnya &rarr;</a>
                <?php else: ?>
                    <span class="paginasi-link paginasi-mati" aria-disabled="true">Selanjutnya &rarr;</span>
                <?php endif; ?>
            </nav>
        <?php endif; ?>

    <?php else: ?>
        <div class="kotak-kosong">
            <h3>Belum ada produk.</h3>
            <p>
                <?php if ($cari !== '' || $kategori !== ''): ?>
                    Tidak ada produk yang cocok dengan kriteria pencarian Anda. Silakan coba kata kunci atau filter lain.
                <?php else: ?>
                    Produk katalog sedang dipersiapkan oleh tim mitra.
                <?php endif; ?>
            </p>
            <?php if ($cari !== '' || $kategori !== '' || $urutan !== 'terbaru'): ?>
                <div style="margin-top: 1rem;">
                    <a href="<?= url('produk.php') ?>" class="tombol tombol-sekunder">Reset Pencarian</a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
