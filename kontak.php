<?php
/**
 * Halaman Kontak - Pekarangan Institute
 * Menampilkan alamat resmi, tautan WhatsApp, dan email tanpa formulir pesan langsung.
 */

require_once __DIR__ . '/includes/fungsi.php';

$judul_halaman = 'Hubungi Kami';
$menu_aktif = 'kontak';
$deskripsi_halaman = 'Hubungi tim Pekarangan Institute untuk pertanyaan seputar produk, donasi, program pemberdayaan, dan kemitraan.';

$whatsapp = ambil_pengaturan('whatsapp', '08xx-xxxx-xxxx');
$email = ambil_pengaturan('email', 'kontak@pekarangan.org');
$alamat = ambil_pengaturan('alamat', '[Alamat Kantor/Sekretariat Contoh]');

// Format nomor WhatsApp untuk tautan https://wa.me/
$wa_digit = preg_replace('/\D/', '', $whatsapp);
if (str_starts_with($wa_digit, '0')) {
    $wa_link = '62' . substr($wa_digit, 1);
} else {
    $wa_link = $wa_digit;
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="kontainer">
    <div class="bagian-kepala">
        <h1>Hubungi Kami</h1>
        <p>Punya pertanyaan mengenai produk, donasi sosial, atau ingin berkolaborasi? Kami siap menyambut Anda.</p>
    </div>

    <div class="kontak-grid">
        <!-- Kartu WhatsApp -->
        <div class="kontak-kartu">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                <span class="kartu-kategori">Layanan Cepat</span>
                <span class="lencana-contoh">DATA CONTOH</span>
            </div>
            <h3>
                <span aria-hidden="true">&#128172;</span>
                WhatsApp Resmi
            </h3>
            <p>
                Konfirmasi pemesanan produk, pertanyaan donasi, dan layanan informasi pelanggan melalui pesan WhatsApp:
            </p>
            <p style="font-size: 1.125rem; font-weight: 700; color: var(--biru);">
                <?= e($whatsapp) ?>
            </p>
            <div class="kartu-kaki">
                <?php if (!empty($wa_link) && strlen($wa_link) >= 10): ?>
                    <a href="https://wa.me/<?= e($wa_link) ?>" target="_blank" rel="noopener noreferrer" class="tombol tombol-utama tombol-blok">
                        Hubungi via WhatsApp
                    </a>
                <?php else: ?>
                    <button type="button" class="tombol tombol-utama tombol-blok" disabled title="Nomor WhatsApp masih berupa placeholder contoh">
                        Nomor Contoh
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Kartu Email -->
        <div class="kontak-kartu">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                <span class="kartu-kategori">Korespondensi</span>
            </div>
            <h3>
                <span aria-hidden="true">&#9993;</span>
                Surat Elektronik (Email)
            </h3>
            <p>
                Untuk penawaran kerja sama, proposal kemitraan institusi, dan surat resmi tertulis:
            </p>
            <p style="font-size: 1.0625rem; font-weight: 700; color: var(--biru);">
                <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a>
            </p>
            <div class="kartu-kaki">
                <a href="mailto:<?= e($email) ?>" class="tombol tombol-sekunder tombol-blok">
                    Kirim Email
                </a>
            </div>
        </div>

        <!-- Kartu Alamat Kantor -->
        <div class="kontak-kartu">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                <span class="kartu-kategori">Kantor & Kebun</span>
                <span class="lencana-contoh">DATA CONTOH</span>
            </div>
            <h3>
                <span aria-hidden="true">&#127969;</span>
                Alamat Sekretariat
            </h3>
            <p>
                Kunjungi sekretariat dan kebun percontohan kami pada jam kerja (Senin - Jumat, 08.00 - 16.00 WIB):
            </p>
            <p style="font-size: 0.9375rem; color: var(--teks);">
                <?= e($alamat) ?>
            </p>
            <div class="kartu-kaki">
                <span class="tombol tombol-sekunder tombol-blok" style="cursor: default; opacity: 0.85;">
                    Sekretariat Komunitas
                </span>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
