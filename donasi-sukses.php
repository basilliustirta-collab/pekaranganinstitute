<?php
/**
 * Halaman Donasi Sukses - Pekarangan Institute
 * Menampilkan kode referensi donasi DON, program yang didukung, nominal donasi,
 * informasi rekening pembayaran, banner konfirmasi admin, dan tombol konfirmasi WhatsApp.
 */

require_once __DIR__ . '/includes/fungsi.php';

$kode = trim($_GET['kode'] ?? '');

$donasi = null;
if ($kode !== '') {
    try {
        $stmt = db()->prepare("
            SELECT d.kode, d.jumlah, d.status, d.anonim, d.is_contoh, d.dibuat_pada,
                   p.judul AS judul_program, p.slug AS slug_program
            FROM donasi d
            JOIN program p ON d.program_id = p.id
            WHERE d.kode = :kode
            LIMIT 1
        ");
        $stmt->execute([':kode' => $kode]);
        $donasi = $stmt->fetch() ?: null;
    } catch (Throwable $e) {
        $donasi = null;
    }
}

// Jika kode donasi tidak ditemukan, tampilkan 404 ramah
if (!$donasi) {
    http_response_code(404);
    $judul_halaman = 'Donasi Tidak Ditemukan';
    $menu_aktif = 'donasi';
    require_once __DIR__ . '/includes/header.php';
    ?>
    <div class="kontainer">
        <div class="kotak-404">
            <h1>404</h1>
            <h2>Donasi Tidak Ditemukan</h2>
            <p>Maaf, kode donasi yang Anda tuju tidak tercatat dalam sistem kami. Pastikan tautan yang Anda gunakan sudah benar.</p>
            <div>
                <a href="<?= url('program.php') ?>" class="tombol tombol-utama">&larr; Kembali ke Daftar Program</a>
            </div>
        </div>
    </div>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$judul_halaman = 'Instruksi Donasi - ' . $donasi['kode'];
$menu_aktif = 'donasi';
$deskripsi_halaman = 'Instruksi transfer dan konfirmasi pembayaran donasi sosial Pekarangan Institute.';

$info_pembayaran = ambil_pengaturan('info_pembayaran', '[Nama Bank] [Nomor Rekening] a.n. Pekarangan Institute');
$nomor_admin_wa  = ambil_pengaturan('whatsapp', '');

// Buat template pesan WhatsApp untuk konfirmasi donasi
$pesan_wa = sprintf(
    "Halo Admin Pekarangan Institute, saya telah mengirimkan donasi untuk program:\n\nKode Donasi: %s\nProgram: %s\nNominal: %s\n\nMohon diverifikasi pembayarannya. Terima kasih!",
    $donasi['kode'],
    $donasi['judul_program'],
    rupiah($donasi['jumlah'])
);

$tautan_konfirmasi_wa = tautan_wa($nomor_admin_wa, $pesan_wa);

require_once __DIR__ . '/includes/header.php';
?>

<div class="kontainer">
    <div class="kartu-sukses">
        <div class="sukses-ikon" aria-hidden="true" style="background: var(--oranye-muda); color: var(--oranye);">&#9829;</div>

        <div style="text-align: center; margin-bottom: 1.5rem;">
            <?php if (!empty($donasi['is_contoh'])): ?>
                <div style="margin-bottom: 0.5rem;">
                    <span class="lencana-contoh">DATA CONTOH</span>
                </div>
            <?php endif; ?>
            <h1 style="font-size: 1.75rem; margin-bottom: 0.5rem; color: var(--biru-tua);">Terima Kasih atas Kebaikan Anda</h1>
            <p style="color: var(--teks-redup);">
                Niat baik Anda telah kami catat dalam antrean verifikasi donasi. Silakan selesaikan proses transfer untuk mewujudkan dampak nyata.
            </p>
        </div>

        <!-- Kode Referensi Donasi -->
        <div class="kode-kotak" style="border-color: var(--oranye); background-color: var(--oranye-muda);">
            <div class="kode-kotak-label" style="color: var(--oranye);">Kode Referensi Donasi</div>
            <div class="kode-kotak-nilai" style="color: var(--oranye);"><?= e($donasi['kode']) ?></div>
            <span style="font-size: 0.8125rem; color: var(--teks-redup); display: block; margin-top: 0.25rem;">
                Gunakan kode ini saat melakukan konfirmasi kepada admin kami.
            </span>
        </div>

        <!-- Banner Verifikasi Donasi Sesuai Aturan Bisnis -->
        <div class="banner-peringatan" role="note">
            <span style="font-size: 1.25rem;" aria-hidden="true">&#9432;</span>
            <div>
                <strong>Donasi dihitung setelah dikonfirmasi admin</strong>
                <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem;">
                    Progres dana pada halaman program akan bertambah secara resmi setelah dana masuk dan diverifikasi oleh admin.
                </p>
            </div>
        </div>

        <!-- Rincian Donasi -->
        <h3 style="font-size: 1.125rem; margin-top: 1.5rem; margin-bottom: 0.75rem;">Rincian Donasi</h3>
        <table class="tabel-ringkasan" aria-label="Rincian Donasi">
            <thead>
                <tr>
                    <th scope="col">Keterangan</th>
                    <th scope="col" style="text-align: right;">Rincian</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Program Sosial</td>
                    <td><strong><?= e($donasi['judul_program']) ?></strong></td>
                </tr>
                <tr>
                    <td>Status Donasi</td>
                    <td>
                        <span class="lencana lencana-kuning">Menunggu Verifikasi</span>
                    </td>
                </tr>
                <tr class="baris-total">
                    <td>Nominal Donasi</td>
                    <td style="color: var(--oranye);"><?= rupiah($donasi['jumlah']) ?></td>
                </tr>
            </tbody>
        </table>

        <!-- Instruksi Pembayaran Rekening -->
        <div class="kotak-instruksi-bank">
            <h4>Rekening Resmi Donasi:</h4>
            <p style="margin-bottom: 0.5rem; color: var(--teks);">
                Silakan lakukan transfer sebesar <strong><?= rupiah($donasi['jumlah']) ?></strong> ke rekening resmi berikut:
            </p>
            <div style="background: var(--putih); border: 1px solid var(--garis); padding: 0.75rem 1rem; border-radius: var(--radius-sm); font-weight: 600; color: var(--biru-tua); margin-bottom: 0.5rem;">
                <?= nl2br(e($info_pembayaran)) ?>
            </div>
            <p style="font-size: 0.8125rem; color: var(--teks-redup); margin: 0;">
                Sertakan kode referensi <strong><?= e($donasi['kode']) ?></strong> pada catatan transfer bank.
            </p>
        </div>

        <!-- Tombol Konfirmasi WhatsApp -->
        <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-top: 1.5rem;">
            <a href="<?= e($tautan_konfirmasi_wa) ?>" target="_blank" rel="noopener noreferrer" class="tombol tombol-oranye tombol-blok">
                <span aria-hidden="true">&#9742;</span> Konfirmasi via WhatsApp
            </a>

            <div class="grup-tombol" style="justify-content: center; margin-top: 0.5rem;">
                <a href="<?= url('program-detail.php?slug=' . urlencode($donasi['slug_program'])) ?>" class="tombol tombol-sekunder">
                    Lihat Program
                </a>
                <a href="<?= url() ?>" class="tombol tombol-sekunder">
                    Halaman Utama
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
