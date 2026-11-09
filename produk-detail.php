<?php
/**
 * Detail Produk & Formulir Pemesanan Langsung
 * Pekarangan Institute
 *
 * Mengambil data produk berdasarkan slug (status 'tampil').
 * Memproses form pemesanan dengan validasi server, CSRF, honeypot, dan harga dari database.
 */

require_once __DIR__ . '/includes/fungsi.php';

$slug = trim($_GET['slug'] ?? '');

// Ambil produk berstatus 'tampil'
$produk = null;
if ($slug !== '') {
    try {
        $stmt = db()->prepare("
            SELECT id, nama, slug, kategori, harga, stok, deskripsi_singkat, deskripsi, penjual, foto, status, is_contoh
            FROM produk
            WHERE slug = :slug AND status = 'tampil'
            LIMIT 1
        ");
        $stmt->execute([':slug' => $slug]);
        $produk = $stmt->fetch() ?: null;
    } catch (Throwable $e) {
        $produk = null;
    }
}

// Jika produk tidak ditemukan atau bukan 'tampil', tampilkan 404 ramah
if (!$produk) {
    http_response_code(404);
    $judul_halaman = 'Produk Tidak Ditemukan';
    $menu_aktif = 'produk';
    require_once __DIR__ . '/includes/header.php';
    ?>
    <div class="kontainer">
        <div class="kotak-404">
            <h1>404</h1>
            <h2>Produk Tidak Ditemukan</h2>
            <p>Maaf, produk yang Anda cari tidak tersedia, belum dipublikasikan, atau tautan yang Anda ikuti keliru.</p>
            <div>
                <a href="<?= url('produk.php') ?>" class="tombol tombol-utama">&larr; Kembali ke Katalog Produk</a>
            </div>
        </div>
    </div>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$errors = [];
$nama_pemesan = '';
$whatsapp = '';
$alamat = '';
$jumlah = 1;
$catatan = '';

// Proses form pemesanan saat POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Cek Honeypot (jika diisi oleh bot, tolak diam-diam atau beri error)
    if (!empty($_POST['website'])) {
        http_response_code(400);
        exit('Permintaan tidak valid.');
    }

    // 2. Cek Token CSRF
    if (!cek_csrf()) {
        $errors[] = 'Sesi formulir telah kedaluwarsa atau tidak valid. Silakan muat ulang halaman.';
    }

    // 3. Ambil dan sanitasi input form
    $nama_pemesan = trim($_POST['nama_pemesan'] ?? '');
    $whatsapp     = trim($_POST['whatsapp'] ?? '');
    $alamat       = trim($_POST['alamat'] ?? '');
    $jumlah_input = trim($_POST['jumlah'] ?? '1');
    $catatan      = trim($_POST['catatan'] ?? '');

    // 4. Validasi server
    if ($nama_pemesan === '') {
        $errors['nama_pemesan'] = 'Nama pemesan wajib diisi.';
    } elseif (mb_strlen($nama_pemesan) > 150) {
        $errors['nama_pemesan'] = 'Nama pemesan maksimal 150 karakter.';
    }

    if ($whatsapp === '') {
        $errors['whatsapp'] = 'Nomor WhatsApp wajib diisi.';
    } elseif (!validasi_nomor_wa($whatsapp)) {
        $errors['whatsapp'] = 'Format nomor WhatsApp tidak valid. Gunakan format Indonesia (contoh: 081234567890).';
    }

    if ($alamat === '') {
        $errors['alamat'] = 'Alamat pengiriman lengkap wajib diisi.';
    } elseif (mb_strlen($alamat) > 1000) {
        $errors['alamat'] = 'Alamat maksimal 1000 karakter.';
    }

    if ($catatan !== '' && mb_strlen($catatan) > 500) {
        $errors['catatan'] = 'Catatan maksimal 500 karakter.';
    }

    // Validasi stok produk
    $stok_tersedia = (int)$produk['stok'];
    if ($stok_tersedia <= 0) {
        $errors['jumlah'] = 'Maaf, stok produk ini sedang habis.';
    } elseif (!filter_var($jumlah_input, FILTER_VALIDATE_INT) || (int)$jumlah_input < 1) {
        $errors['jumlah'] = 'Jumlah pesanan minimal 1 item.';
    } elseif ((int)$jumlah_input > $stok_tersedia) {
        $errors['jumlah'] = 'Jumlah pesanan melebihi stok yang tersedia (' . $stok_tersedia . ' item).';
    } else {
        $jumlah = (int)$jumlah_input;
    }

    // 5. Simpan pesanan jika tidak ada error
    if (empty($errors)) {
        // Ambil harga asli dari database (bukan dari input pengguna)
        $harga_satuan = (int)$produk['harga'];
        $total = $harga_satuan * $jumlah;

        // Buat kode pesanan unik (ORD-YYYYMMDD-XXXX)
        $kode = '';
        $terdaftar = true;
        for ($percobaan = 0; $percobaan < 5 && $terdaftar; $percobaan++) {
            $kode = buat_kode('ORD');
            $cek = db()->prepare("SELECT COUNT(*) FROM pesanan WHERE kode = :kode");
            $cek->execute([':kode' => $kode]);
            if ((int)$cek->fetchColumn() === 0) {
                $terdaftar = false;
            }
        }

        try {
            // Simpan pesanan dengan status 'menunggu' (stok belum dikurangi sesuai aturan bisnis)
            $stmt_simpan = db()->prepare("
                INSERT INTO pesanan (
                    kode, produk_id, nama_produk, harga_satuan, jumlah, total,
                    nama_pemesan, whatsapp, alamat, catatan, status, is_contoh, dibuat_pada
                ) VALUES (
                    :kode, :produk_id, :nama_produk, :harga_satuan, :jumlah, :total,
                    :nama_pemesan, :whatsapp, :alamat, :catatan, 'menunggu', :is_contoh, NOW()
                )
            ");
            $stmt_simpan->execute([
                ':kode'         => $kode,
                ':produk_id'    => (int)$produk['id'],
                ':nama_produk'  => $produk['nama'],
                ':harga_satuan' => $harga_satuan,
                ':jumlah'       => $jumlah,
                ':total'        => $total,
                ':nama_pemesan' => $nama_pemesan,
                ':whatsapp'     => $whatsapp,
                ':alamat'       => $alamat,
                ':catatan'      => $catatan !== '' ? $catatan : null,
                ':is_contoh'    => (int)$produk['is_contoh']
            ]);

            // Alihkan ke halaman pesanan-sukses
            alihkan('pesanan-sukses.php?kode=' . urlencode($kode));
            exit;
        } catch (Throwable $e) {
            $errors[] = 'Terjadi kesalahan sistem saat menyimpan pesanan. Silakan coba kembali.';
        }
    }
}

$judul_halaman = $produk['nama'];
$menu_aktif = 'produk';
$deskripsi_halaman = $produk['deskripsi_singkat'];
$habis = (int)$produk['stok'] <= 0;

require_once __DIR__ . '/includes/header.php';
?>

<div class="kontainer">
    <!-- Breadcrumb / Navigasi Balik -->
    <div style="margin-bottom: 1.5rem;">
        <a href="<?= url('produk.php') ?>" style="font-size: 0.875rem; font-weight: 500;">
            &larr; Kembali ke Semua Produk
        </a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="kotak-error-form" role="alert">
            <strong>Mohon periksa kembali formulir pemesanan:</strong>
            <ul>
                <?php foreach ($errors as $pesan_err): ?>
                    <li><?= e($pesan_err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="detail-grid">
        <!-- Kolom Gambar -->
        <div class="detail-gambar">
            <div class="foto-wadah">
                <?php if (!empty($produk['foto']) && file_exists(__DIR__ . '/uploads/' . $produk['foto'])): ?>
                    <img src="<?= url('uploads/' . e($produk['foto'])) ?>" alt="<?= e($produk['nama']) ?>">
                <?php else: ?>
                    <div class="foto-kosong">[Foto Produk]</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Kolom Konten & Formulir -->
        <div class="detail-konten">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; gap: 0.5rem; flex-wrap: wrap;">
                <span class="kartu-kategori"><?= e($produk['kategori']) ?></span>
                <?php if (!empty($produk['is_contoh'])): ?>
                    <span class="lencana-contoh">DATA CONTOH</span>
                <?php endif; ?>
            </div>

            <h1 style="font-size: 1.875rem; margin-bottom: 0.5rem;"><?= e($produk['nama']) ?></h1>

            <div class="detail-harga"><?= rupiah($produk['harga']) ?></div>

            <div class="detail-meta">
                <div class="detail-meta-item">
                    <span class="detail-meta-label">Ketersediaan Stok:</span>
                    <span class="detail-meta-nilai">
                        <?php if ($habis): ?>
                            <span class="lencana lencana-merah">Stok habis</span>
                        <?php else: ?>
                            <span class="lencana lencana-hijau">Tersedia <?= (int)$produk['stok'] ?> item</span>
                        <?php endif; ?>
                    </span>
                </div>
                <?php if (!empty($produk['penjual'])): ?>
                    <div class="detail-meta-item">
                        <span class="detail-meta-label">Pekebun / Mitra:</span>
                        <span class="detail-meta-nilai"><?= e($produk['penjual']) ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="detail-deskripsi">
                <h3 style="font-size: 1.0625rem; margin-bottom: 0.5rem;">Deskripsi Produk</h3>
                <p><?= nl2br(e($produk['deskripsi'])) ?></p>
            </div>

            <!-- Formulir Pemesanan Langsung -->
            <div class="kotak-pesan" id="form-pesan">
                <h3>Formulir Pemesanan</h3>

                <?php if ($habis): ?>
                    <div class="banner-peringatan" style="margin: 0;">
                        <span>&#9888;</span>
                        <div>
                            <strong>Stok Saat Ini Tidak Tersedia</strong>
                            <p style="margin: 0; font-size: 0.875rem; color: #7A5500;">
                                Produk ini sedang habis terjual. Silakan cek kembali dalam waktu dekat atau pilih produk binaan lainnya.
                            </p>
                        </div>
                    </div>
                <?php else: ?>
                    <p style="font-size: 0.875rem; color: var(--teks-redup); margin-bottom: 1.25rem;">
                        Pemesanan diproses satu per satu tanpa keranjang. Pembayaran dilakukan via transfer manual dan dikonfirmasi admin.
                    </p>

                    <form action="<?= url('produk-detail.php?slug=' . urlencode($produk['slug'])) ?>#form-pesan" method="POST" novalidate>
                        <!-- Token CSRF -->
                        <input type="hidden" name="csrf_token" value="<?= e(token_csrf()) ?>">

                        <!-- Honeypot Jebakan Bot -->
                        <div class="kolom-jebakan" aria-hidden="true">
                            <label for="website">Abaikan kolom ini</label>
                            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="form-grup">
                            <label for="nama_pemesan" class="form-label">
                                Nama Lengkap Pemesan <span class="tanda-wajib">*</span>
                            </label>
                            <input type="text" id="nama_pemesan" name="nama_pemesan" 
                                   value="<?= e($nama_pemesan) ?>" 
                                   class="form-kontrol <?= isset($errors['nama_pemesan']) ? 'form-kontrol-error' : '' ?>" 
                                   placeholder="Contoh: Budi Santoso" maxlength="150" required>
                            <?php if (isset($errors['nama_pemesan'])): ?>
                                <span class="form-pesan-error"><?= e($errors['nama_pemesan']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-grup">
                            <label for="whatsapp" class="form-label">
                                Nomor WhatsApp Aktif <span class="tanda-wajib">*</span>
                            </label>
                            <input type="tel" id="whatsapp" name="whatsapp" 
                                   value="<?= e($whatsapp) ?>" 
                                   class="form-kontrol <?= isset($errors['whatsapp']) ? 'form-kontrol-error' : '' ?>" 
                                   placeholder="Contoh: 081234567890" maxlength="30" required>
                            <span class="form-petunjuk">Digunakan untuk konfirmasi pesanan dan instruksi pembayaran.</span>
                            <?php if (isset($errors['whatsapp'])): ?>
                                <span class="form-pesan-error"><?= e($errors['whatsapp']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-grup">
                            <label for="alamat" class="form-label">
                                Alamat Pengiriman Lengkap <span class="tanda-wajib">*</span>
                            </label>
                            <textarea id="alamat" name="alamat" 
                                      class="form-kontrol <?= isset($errors['alamat']) ? 'form-kontrol-error' : '' ?>" 
                                      placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan, kota/kabupaten, dan kode pos" 
                                      rows="3" required><?= e($alamat) ?></textarea>
                            <?php if (isset($errors['alamat'])): ?>
                                <span class="form-pesan-error"><?= e($errors['alamat']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-grup">
                            <label for="jumlah" class="form-label">
                                Jumlah Item <span class="tanda-wajib">*</span>
                            </label>
                            <input type="number" id="jumlah" name="jumlah" 
                                   value="<?= (int)$jumlah ?>" 
                                   min="1" max="<?= (int)$produk['stok'] ?>" 
                                   class="form-kontrol <?= isset($errors['jumlah']) ? 'form-kontrol-error' : '' ?>" 
                                   style="max-width: 140px;" required>
                            <span class="form-petunjuk">Maksimal pemesanan: <?= (int)$produk['stok'] ?> item.</span>
                            <?php if (isset($errors['jumlah'])): ?>
                                <span class="form-pesan-error"><?= e($errors['jumlah']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-grup">
                            <label for="catatan" class="form-label">Catatan Tambahan (Opsional)</label>
                            <input type="text" id="catatan" name="catatan" 
                                   value="<?= e($catatan) ?>" 
                                   class="form-kontrol <?= isset($errors['catatan']) ? 'form-kontrol-error' : '' ?>" 
                                   placeholder="Contoh: Tolong bungkus rapat" maxlength="500">
                            <?php if (isset($errors['catatan'])): ?>
                                <span class="form-pesan-error"><?= e($errors['catatan']) ?></span>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="tombol tombol-utama tombol-blok" style="margin-top: 1rem;">
                            Kirim Pesanan Sekarang
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
