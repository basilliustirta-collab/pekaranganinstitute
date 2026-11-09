<?php
/**
 * Halaman Login Administrator - Pekarangan Institute
 * Digunakan oleh administrator untuk masuk dan mengelola halaman serta konten web pengguna.
 * Dilengkapi proteksi brute force (5x gagal -> kunci 60 detik), CSRF token,
 * pencegahan open-redirect, dan deteksi akun awal (setup).
 */

require_once __DIR__ . '/includes/fungsi.php';

mulai_sesi_aman();

// Validasi parameter ?next= (cegah open redirect ke luar situs)
$next_mentah = trim($_GET['next'] ?? $_POST['next'] ?? '');
$next = '';
if ($next_mentah !== '' && !preg_match('#^(?:[a-z]+:)?//#i', $next_mentah) && !str_starts_with($next_mentah, 'javascript:') && !str_contains($next_mentah, "\n") && !str_contains($next_mentah, "\r")) {
    $next = $next_mentah;
}

// Jika sudah login, langsung alihkan ke halaman yang dituju atau dashboard admin
if (admin_sedang_login()) {
    alihkan($next !== '' ? $next : 'admin/index.php');
}

// Cek apakah ada akun administrator terdaftar
$jumlah_admin = 0;
try {
    $stmt_cek = db()->query("SELECT COUNT(*) FROM admin");
    $jumlah_admin = (int)$stmt_cek->fetchColumn();
} catch (Throwable $e) {
    $jumlah_admin = 0;
}
$belum_ada_admin = ($jumlah_admin === 0);

// Cek penguncian akibat brute force
$sekarang = time();
$waktu_terkunci = (int)($_SESSION['login_terkunci_sampai'] ?? 0);
$terkunci = $waktu_terkunci > $sekarang;
$sisa_kunci = $terkunci ? ($waktu_terkunci - $sekarang) : 0;

$error = '';
$email = '';
$flash = pesan_flash();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if ($terkunci) {
        $error = "Terlalu banyak percobaan gagal. Silakan tunggu {$sisa_kunci} detik sebelum mencoba kembali.";
    } elseif (!cek_csrf()) {
        $error = "Permintaan tidak valid atau sesi telah kedaluwarsa. Silakan muat ulang halaman.";
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        $admin = null;
        if ($email !== '' && $password !== '') {
            try {
                $stmt = db()->prepare("SELECT id, nama, email, password_hash FROM admin WHERE email = :email LIMIT 1");
                $stmt->execute([':email' => $email]);
                $admin = $stmt->fetch();
            } catch (Throwable $e) {
                $admin = null;
            }
        }

        if ($admin && password_verify($password, $admin['password_hash'])) {
            // Berhasil login: reset proteksi brute force
            unset($_SESSION['login_gagal_hitung'], $_SESSION['login_terkunci_sampai']);

            // Regenerasi ID sesi untuk mencegah session fixation
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int)$admin['id'];
            $_SESSION['admin_nama'] = $admin['nama'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['terakhir_aktif'] = time();

            pesan_flash('hijau', 'Selamat datang kembali, ' . $admin['nama'] . '! Anda sekarang berada di panel pengelola web.');
            alihkan($next !== '' ? $next : 'admin/index.php');
        } else {
            // Gagal login: tingkatkan penghitung kegagalan
            $hitung = (int)($_SESSION['login_gagal_hitung'] ?? 0) + 1;
            $_SESSION['login_gagal_hitung'] = $hitung;

            if ($hitung >= 5) {
                $_SESSION['login_terkunci_sampai'] = time() + 60;
                $_SESSION['login_gagal_hitung'] = 0;
                $error = "Terlalu banyak percobaan gagal. Silakan tunggu 60 detik sebelum mencoba kembali.";
            } else {
                $error = "Alamat email atau kata sandi tidak cocok.";
            }
        }
    }
}

$nama_situs = ambil_pengaturan('nama_situs', 'Pekarangan Institute');
$tagline_situs = ambil_pengaturan('tagline', 'Berbelanja, Berbagi, Berdampak');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Kelola Web - <?= e($nama_situs) ?></title>
    <meta name="robots" content="noindex, nofollow">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= url('assets/css/admin.css') ?>">
</head>
<body class="halaman-auth">
    <div class="kartu-auth" style="max-width: 440px;">
        <div class="kartu-auth-kepala">
            <div class="kartu-auth-logo">P</div>
            <h1 class="kartu-auth-judul">Login Admin</h1>
            <p class="kartu-auth-sub">Kelola Halaman & Konten Web Pengguna</p>
        </div>

        <!-- Banner jika belum ada admin terdaftar -->
        <?php if ($belum_ada_admin): ?>
            <div class="banner-peringatan" style="margin-bottom: 1.25rem; font-size: 0.875rem;">
                <div>
                    <strong>Setup Akun Diperlukan:</strong> Belum ada akun administrator yang dibuat pada sistem ini.
                    <div style="margin-top: 0.5rem;">
                        <a href="<?= url('admin/setup.php') ?>" class="tombol tombol-oranye tombol-sm" style="display:inline-flex;">
                            Inisialisasi Admin Utama &rarr;
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Pesan Flash -->
        <?php if ($flash): ?>
            <?php
            $kelas_flash = match ($flash['tipe']) {
                'hijau', 'sukses' => 'kotak-sukses',
                'kuning', 'peringatan' => 'kotak-peringatan',
                default => 'kotak-error',
            };
            ?>
            <div class="<?= $kelas_flash ?>" role="alert" style="margin-bottom:1.25rem;">
                <?= e($flash['pesan']) ?>
            </div>
        <?php endif; ?>

        <!-- Pesan Error Login -->
        <?php if (!empty($error)): ?>
            <div class="kotak-error" role="alert" style="margin-bottom:1.25rem;">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form action="<?= url('login.php' . ($next !== '' ? '?next=' . urlencode($next) : '')) ?>" method="POST" novalidate>
            <input type="hidden" name="csrf_token" value="<?= token_csrf() ?>">
            <?php if ($next !== ''): ?>
                <input type="hidden" name="next" value="<?= e($next) ?>">
            <?php endif; ?>

            <!-- Alamat Email -->
            <div class="admin-form-grup">
                <label for="email">Alamat Email Administrator</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="admin-input" 
                    value="<?= e($email) ?>" 
                    required 
                    autocomplete="email" 
                    placeholder="admin@pekarangan.org"
                    <?= $terkunci ? 'disabled' : '' ?>
                    autofocus
                >
            </div>

            <!-- Kata Sandi -->
            <div class="admin-form-grup">
                <label for="password">Kata Sandi</label>
                <div class="input-password-wadah">
                    <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="admin-input" 
                    required 
                    autocomplete="current-password"
                    placeholder="Masukkan kata sandi"
                    <?= $terkunci ? 'disabled' : '' ?>
                >
                    <button type="button" class="tombol-toggle-pwd" aria-label="Tampilkan atau sembunyikan kata sandi">Tampilkan</button>
                </div>
            </div>

            <button type="submit" class="tombol tombol-utama" style="width:100%;margin-top:0.75rem;" <?= $terkunci ? 'disabled' : '' ?>>
                Masuk ke Panel Pengelola
            </button>
        </form>

        <!-- Keterangan Lingkup Pengelolaan -->
        <div style="margin-top: 1.5rem; padding: 0.875rem 1rem; background: var(--latar); border: 1px solid var(--garis); border-radius: var(--radius-sm); font-size: 0.8125rem; color: var(--teks-redup);">
            <strong style="color: var(--teks); display: block; margin-bottom: 0.375rem;">Fungsi Panel Administrator:</strong>
            <ul style="padding-left: 1.25rem; margin: 0; line-height: 1.5;">
                <li>Kelola katalog produk, harga, dan stok</li>
                <li>Kelola program sosial & target dana</li>
                <li>Verifikasi donasi & pesanan masuk</li>
                <li>Publikasi laporan transparansi dana</li>
                <li>Perbarui identitas web & konten beranda</li>
            </ul>
        </div>

        <div class="kartu-auth-kaki" style="margin-top: 1.25rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.875rem;">
            <a href="<?= url() ?>">&larr; Kembali ke Beranda</a>
            <span style="color: var(--teks-redup);"><?= e($nama_situs) ?></span>
        </div>
    </div>

    <script src="<?= url('assets/js/app.js') ?>"></script>
    <script src="<?= url('assets/js/admin.js') ?>"></script>
</body>
</html>
