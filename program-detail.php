<?php
/**
 * Detail Program Sosial & Daftar Donatur
 * Pekarangan Institute
 *
 * Menampilkan cerita program yang faktual dan objektif tanpa eksploitasi emosional.
 * Menampilkan progres dana riil (donasi berhasil) dan daftar donatur (donasi anonim disamarkan).
 */

require_once __DIR__ . '/includes/fungsi.php';

$slug = trim($_GET['slug'] ?? '');

$program = null;
if ($slug !== '') {
    try {
        $stmt = db()->prepare("
            SELECT id, judul, slug, ringkasan, cerita, target_dana, tenggat, lokasi, penerima_manfaat, foto_sampul, status, is_contoh
            FROM program
            WHERE slug = :slug AND status IN ('aktif', 'selesai')
            LIMIT 1
        ");
        $stmt->execute([':slug' => $slug]);
        $program = $stmt->fetch() ?: null;
    } catch (Throwable $e) {
        $program = null;
    }
}

// Jika program tidak ditemukan, tampilkan 404 ramah
if (!$program) {
    http_response_code(404);
    $judul_halaman = 'Program Tidak Ditemukan';
    $menu_aktif = 'program';
    require_once __DIR__ . '/includes/header.php';
    ?>
    <div class="kontainer">
        <div class="kotak-404">
            <h1>404</h1>
            <h2>Program Tidak Ditemukan</h2>
            <p>Maaf, program sosial yang Anda cari tidak ditemukan atau sudah tidak tersedia.</p>
            <div>
                <a href="<?= url('program.php') ?>" class="tombol tombol-utama">&larr; Kembali ke Daftar Program</a>
            </div>
        </div>
    </div>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$judul_halaman = $program['judul'];
$menu_aktif = 'program';
$deskripsi_halaman = $program['ringkasan'];

$progres = progres_program((int)$program['id']);
$target = (int)$program['target_dana'];
$terkumpul = (int)$progres['terkumpul'];
$persen_asli = $target > 0 ? (int)round(($terkumpul / $target) * 100) : 0;
$persen_visual = min(100, $persen_asli);
$adalah_aktif = ($program['status'] === 'aktif');

// Ambil riwayat donatur terbaru yang berstatus 'berhasil' saja
$daftar_donatur = [];
try {
    $stmt_donatur = db()->prepare("
        SELECT id, nama_donatur, jumlah, anonim, pesan, dibuat_pada
        FROM donasi
        WHERE program_id = :program_id AND status = 'berhasil'
        ORDER BY dibuat_pada DESC
        LIMIT 10
    ");
    $stmt_donatur->execute([':program_id' => (int)$program['id']]);
    $daftar_donatur = $stmt_donatur->fetchAll() ?: [];
} catch (Throwable $e) {
    $daftar_donatur = [];
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="kontainer">
    <!-- Breadcrumb -->
    <div style="margin-bottom: 1.5rem;">
        <a href="<?= url('program.php') ?>" style="font-size: 0.875rem; font-weight: 500;">
            &larr; Kembali ke Semua Program
        </a>
    </div>

    <div class="detail-grid">
        <!-- Kolom Kiri: Sampul & Cerita Program -->
        <div class="detail-gambar">
            <div class="foto-wadah" style="aspect-ratio: 16 / 10; margin-bottom: 1.5rem;">
                <?php if (!empty($program['foto_sampul']) && file_exists(__DIR__ . '/uploads/' . $program['foto_sampul'])): ?>
                    <img src="<?= url('uploads/' . e($program['foto_sampul'])) ?>" alt="<?= e($program['judul']) ?>">
                <?php else: ?>
                    <div class="foto-kosong">[Foto Sampul Program]</div>
                <?php endif; ?>
            </div>

            <article class="artikel-konten" style="margin-bottom: 1.5rem;">
                <h2 style="font-size: 1.375rem; margin-bottom: 1rem;">Cerita & Sasaran Program</h2>
                <div style="line-height: 1.8; color: var(--teks);">
                    <?= nl2br(e($program['cerita'])) ?>
                </div>

                <div style="margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--garis); font-size: 0.875rem; color: var(--teks-redup);">
                    <p style="margin-bottom: 0.5rem;">
                        <strong>Penerima Manfaat:</strong> <?= e($program['penerima_manfaat']) ?>
                    </p>
                    <p style="margin: 0;">
                        <em>* Sesuai etika perlindungan hak privasi martabat komunitas dan aturan perlindungan anak, kami tidak mempublikasikan nama perorangan atau foto anak penerima manfaat.</em>
                    </p>
                </div>
            </article>

            <!-- Daftar Donatur Terbaru (Hanya Berstatus Berhasil) -->
            <section class="artikel-konten" aria-labelledby="judul-donatur-terbaru">
                <h3 id="judul-donatur-terbaru" style="font-size: 1.125rem; margin-bottom: 0.5rem;">
                    Donatur Terbaru (<?= (int)$progres['jumlah_donatur'] ?>)
                </h3>
                <p style="font-size: 0.875rem; color: var(--teks-redup); margin-bottom: 1rem;">
                    Dukungan nyata yang telah diverifikasi dan masuk ke rekening program.
                </p>

                <?php if (!empty($daftar_donatur)): ?>
                    <ul class="donatur-daftar">
                        <?php foreach ($daftar_donatur as $d): ?>
                            <?php
                            $nama_tampil = !empty($d['anonim']) ? 'Anonim' : $d['nama_donatur'];
                            ?>
                            <li class="donatur-item">
                                <div class="donatur-kepala">
                                    <span><?= e($nama_tampil) ?></span>
                                    <span style="color: var(--oranye); font-weight: 700;"><?= rupiah($d['jumlah']) ?></span>
                                </div>
                                <div style="font-size: 0.75rem; color: var(--teks-redup);">
                                    <?= tanggal_id($d['dibuat_pada']) ?>
                                </div>
                                <?php if (!empty($d['pesan'])): ?>
                                    <div class="donatur-pesan">
                                        &ldquo;<?= e($d['pesan']) ?>&rdquo;
                                    </div>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="kotak-kosong" style="padding: 1.5rem;">
                        <p style="margin: 0; font-size: 0.875rem;">Belum ada donasi yang tercatat untuk program ini. Jadilah donatur pertama!</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>

        <!-- Kolom Kanan: Ringkasan Dana & Tombol Donasi -->
        <div class="detail-konten">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; gap: 0.5rem; flex-wrap: wrap;">
                <div>
                    <?php if ($adalah_aktif): ?>
                        <span class="lencana lencana-hijau">Aktif</span>
                    <?php else: ?>
                        <span class="lencana lencana-kuning">Selesai</span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($program['is_contoh'])): ?>
                    <span class="lencana-contoh">DATA CONTOH</span>
                <?php endif; ?>
            </div>

            <h1 style="font-size: 1.75rem; margin-bottom: 0.75rem;"><?= e($program['judul']) ?></h1>
            <p style="color: var(--teks-redup); margin-bottom: 1.5rem; font-size: 0.9375rem;">
                <?= e($program['ringkasan']) ?>
            </p>

            <!-- Kartu Progres Dana -->
            <div style="background: var(--latar); border: 1px solid var(--garis); border-radius: var(--radius-sm); padding: 1.25rem; margin-bottom: 1.5rem;">
                <div style="font-size: 0.8125rem; font-weight: 600; color: var(--teks-redup); text-transform: uppercase; margin-bottom: 0.25rem;">
                    Dana Terkumpul
                </div>
                <div style="font-size: 1.875rem; font-weight: 700; color: var(--teks); margin-bottom: 0.5rem;">
                    <?= rupiah($terkumpul) ?>
                </div>

                <div class="progress-track" role="progressbar" aria-valuenow="<?= $persen_visual ?>" aria-valuemin="0" aria-valuemax="100" style="height: 12px; margin-bottom: 0.5rem;">
                    <div class="progress-fill" style="width: <?= $persen_visual ?>%;"></div>
                </div>

                <div style="display: flex; justify-content: space-between; font-size: 0.875rem; color: var(--teks-redup);">
                    <span>Tercapai <strong><?= $persen_asli ?>%</strong></span>
                    <span>Target: <strong><?= rupiah($target) ?></strong></span>
                </div>
            </div>

            <!-- Detail Metadata Program -->
            <div class="detail-meta">
                <div class="detail-meta-item">
                    <span class="detail-meta-label">Lokasi Program:</span>
                    <span class="detail-meta-nilai"><?= e($program['lokasi']) ?></span>
                </div>
                <?php if (!empty($program['tenggat'])): ?>
                    <div class="detail-meta-item">
                        <span class="detail-meta-label">Tenggat Waktu:</span>
                        <span class="detail-meta-nilai"><?= tanggal_id($program['tenggat']) ?></span>
                    </div>
                <?php endif; ?>
                <div class="detail-meta-item">
                    <span class="detail-meta-label">Total Donatur:</span>
                    <span class="detail-meta-nilai"><?= (int)$progres['jumlah_donatur'] ?> orang</span>
                </div>
            </div>

            <!-- Tombol Aksi / Informasi Status -->
            <div style="margin-top: 1.5rem;">
                <?php if ($adalah_aktif): ?>
                    <a href="<?= url('donasi.php?program=' . urlencode($program['slug'])) ?>" class="tombol tombol-oranye tombol-blok">
                        Donasi Sekarang
                    </a>
                    <span style="display: block; text-align: center; font-size: 0.8125rem; color: var(--teks-redup); margin-top: 0.5rem;">
                        Konfirmasi pembayaran aman dan terverifikasi via transfer bank manual.
                    </span>
                <?php else: ?>
                    <div class="banner-peringatan" style="margin: 0;">
                        <span>&#9888;</span>
                        <div>
                            <strong>Penggalangan Dana Telah Selesai</strong>
                            <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem;">
                                Target program ini telah terpenuhi atau masa penggalangan telah ditutup. Terima kasih atas partisipasi seluruh donatur!
                            </p>
                        </div>
                    </div>
                    <div style="margin-top: 1rem;">
                        <a href="<?= url('program.php') ?>" class="tombol tombol-sekunder tombol-blok">
                            Lihat Program Aktif Lainnya
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
