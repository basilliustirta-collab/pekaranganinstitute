<?php
/**
 * Daftar Program Sosial - Pekarangan Institute
 * Menampilkan program berstatus 'aktif' dan 'selesai' dengan progres dana aktual (donasi berhasil).
 */

require_once __DIR__ . '/includes/fungsi.php';

$judul_halaman = 'Program Sosial';
$menu_aktif = 'program';
$deskripsi_halaman = 'Inisiatif dan program sosial kemasyarakatan Pekarangan Institute untuk ketahanan pangan dan pemberdayaan komunitas.';

// Ambil program berstatus 'aktif' dan 'selesai'
$daftar_program = [];
try {
    $stmt = db()->query("
        SELECT id, judul, slug, ringkasan, target_dana, tenggat, lokasi, penerima_manfaat, foto_sampul, status, is_contoh, dibuat_pada
        FROM program
        WHERE status IN ('aktif', 'selesai')
        ORDER BY CASE WHEN status = 'aktif' THEN 1 ELSE 2 END, dibuat_pada DESC
    ");
    $daftar_program = $stmt->fetchAll() ?: [];
} catch (Throwable $e) {
    $daftar_program = [];
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="kontainer">
    <div class="bagian-kepala">
        <h1>Program Sosial & Pemberdayaan</h1>
        <p>
            Setiap rupiah donasi disalurkan secara transparan untuk memfasilitasi bibit, sarana kebun produktif, serta pendampingan warga menuju ketahanan pangan mandiri.
        </p>
    </div>

    <?php if (!empty($daftar_program)): ?>
        <div class="grid-kartu-2">
            <?php foreach ($daftar_program as $prog): ?>
                <?php
                $progres = progres_program((int)$prog['id']);
                $target = (int)$prog['target_dana'];
                $terkumpul = (int)$progres['terkumpul'];
                $persen_asli = $target > 0 ? (int)round(($terkumpul / $target) * 100) : 0;
                $persen_visual = min(100, $persen_asli);
                $adalah_aktif = ($prog['status'] === 'aktif');
                ?>
                <article class="kartu">
                    <div class="foto-wadah">
                        <?php if (!empty($prog['foto_sampul']) && file_exists(__DIR__ . '/uploads/' . $prog['foto_sampul'])): ?>
                            <img src="<?= url('uploads/' . e($prog['foto_sampul'])) ?>" alt="<?= e($prog['judul']) ?>">
                        <?php else: ?>
                            <div class="foto-kosong">[Foto Program]</div>
                        <?php endif; ?>
                    </div>

                    <div class="kartu-badan">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; gap: 0.5rem; flex-wrap: wrap;">
                            <div>
                                <?php if ($adalah_aktif): ?>
                                    <span class="lencana lencana-hijau">Aktif</span>
                                <?php else: ?>
                                    <span class="lencana lencana-kuning">Selesai</span>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($prog['is_contoh'])): ?>
                                <span class="lencana-contoh">DATA CONTOH</span>
                            <?php endif; ?>
                        </div>

                        <h2 class="kartu-judul">
                            <a href="<?= url('program-detail.php?slug=' . urlencode($prog['slug'])) ?>">
                                <?= e($prog['judul']) ?>
                            </a>
                        </h2>

                        <p class="kartu-ringkasan"><?= e($prog['ringkasan']) ?></p>

                        <!-- Informasi Lokasi & Tenggat Waktu -->
                        <div style="font-size: 0.8125rem; color: var(--teks-redup); margin-bottom: 0.75rem; display: flex; flex-direction: column; gap: 0.25rem;">
                            <div>
                                <strong>Lokasi:</strong> <?= e($prog['lokasi']) ?>
                            </div>
                            <?php if (!empty($prog['tenggat'])): ?>
                                <div>
                                    <strong>Tenggat:</strong> <?= tanggal_id($prog['tenggat']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Bilah Progres Donasi (Visual dibatasi maks 100%, angka asli tetap ditampilkan) -->
                        <div class="progress-wadah">
                            <div class="progress-info">
                                <span class="progress-info-terkumpul"><?= rupiah($terkumpul) ?></span>
                                <span class="progress-info-target">Target: <?= rupiah($target) ?></span>
                            </div>
                            <div class="progress-track" role="progressbar" aria-valuenow="<?= $persen_visual ?>" aria-valuemin="0" aria-valuemax="100" aria-label="Progres donasi program">
                                <div class="progress-fill" style="width: <?= $persen_visual ?>%;"></div>
                            </div>
                            <div style="font-size: 0.75rem; color: var(--teks-redup); margin-top: 0.375rem; display: flex; justify-content: space-between;">
                                <span>Tercapai <strong><?= $persen_asli ?>%</strong></span>
                                <span><?= (int)$progres['jumlah_donatur'] ?> Donatur</span>
                            </div>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="kartu-kaki">
                            <div class="grup-tombol">
                                <?php if ($adalah_aktif): ?>
                                    <a href="<?= url('donasi.php?program=' . urlencode($prog['slug'])) ?>" class="tombol tombol-oranye" style="flex: 1 1 auto;">
                                        Donasi Sekarang
                                    </a>
                                <?php endif; ?>
                                <a href="<?= url('program-detail.php?slug=' . urlencode($prog['slug'])) ?>" class="tombol tombol-sekunder" style="<?= $adalah_aktif ? 'flex: 1 1 auto;' : 'width: 100%;' ?>">
                                    Detail Program
                                </a>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="kotak-kosong">
            <h3>Belum ada program saat ini.</h3>
            <p>Program sosial sedang dipersiapkan oleh tim fasilitator Pekarangan Institute.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
