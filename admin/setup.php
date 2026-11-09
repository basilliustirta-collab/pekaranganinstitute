<?php
/**
 * Setup Admin Pertama - Pekarangan Institute
 * Hanya dapat diakses satu kali saat tabel admin masih kosong.
 * Membuat akun administrator awal dengan validasi password ketat & indikator kekuatan interaktif.
 */

require_once __DIR__ . '/../includes/fungsi.php';

mulai_sesi_aman();

// Periksa apakah sudah ada data di tabel admin
$jumlah_admin = 0;
try {
    $stmt = db()->query("SELECT COUNT(*) FROM admin");
    $jumlah_admin = (int)$stmt->fetchColumn();
} catch (Throwable $e) {
    http_response_code(500);
    exit('Terjadi kesalahan pada basis data.');
}

// Jika sudah ada admin, tangani mode lokal (reset demo) atau tampilkan 404 demi keamanan
if ($jumlah_admin > 0) {
    // Mode pengembangan lokal: izinkan reset akun demo untuk menguji coba setup baru
    if (defined('APP_ENV') && APP_ENV === 'lokal') {
        if ((($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['aksi_reset_admin']) && cek_csrf()) || isset($_GET['reset']) || isset($_GET['ulang'])) {
            try {
                db()->exec("DELETE FROM admin");
                pesan_flash('hijau', 'Akun admin berhasil direset. Silakan buat akun admin baru dengan aturan kata sandi yang telah diperbarui.');
                alihkan('admin/setup.php');
                exit;
            } catch (Throwable $e) {}
        }
    }

    http_response_code(404);
    $admin_terdaftar = '';
    try {
        $admin_terdaftar = db()->query("SELECT email FROM admin LIMIT 1")->fetchColumn() ?: '';
    } catch (Throwable $e) {}
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Halaman Tidak Tersedia - Pekarangan Institute</title>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
        <link rel="stylesheet" href="<?= url('assets/css/admin.css') ?>">
    </head>
    <body style="display:flex;align-items:center;justify-content:center;min-height:100vh;background:var(--latar);padding:1.5rem;">
        <div style="background:var(--putih);padding:2.5rem;border-radius:var(--radius);max-width:520px;text-align:center;box-shadow:var(--bayangan);border:1px solid var(--garis);">
            <div style="font-size:2.5rem;margin-bottom:0.5rem;">🔒</div>
            <h1 style="font-size:1.5rem;margin-bottom:0.5rem;color:var(--biru-tua);">Akun Administrator Sudah Terdaftar</h1>
            <p style="color:var(--teks-redup);margin-bottom:1.5rem;font-size:0.9375rem;">
                Sistem mendeteksi telah ada akun pengelola aktif (<code><?= e($admin_terdaftar) ?></code>). Demi keamanan sistem, halaman setup awal dinonaktifkan secara otomatis.
            </p>
            <div style="display:flex;flex-direction:column;gap:0.75rem;">
                <a href="<?= url('login.php') ?>" class="tombol tombol-utama tombol-blok">
                    Masuk ke Halaman Login Admin &rarr;
                </a>
                <a href="<?= url('index.php') ?>" class="tombol tombol-sekunder tombol-blok">
                    Kembali ke Beranda
                </a>
            </div>

            <?php if (defined('APP_ENV') && APP_ENV === 'lokal'): ?>
                <div style="margin-top:1.75rem;padding:1.25rem;background:#F8FAFC;border:1.5px dashed var(--garis);border-radius:var(--radius-sm);text-align:left;">
                    <div style="display:flex;align-items:center;gap:0.5rem;font-weight:700;font-size:0.8125rem;color:var(--biru-tua);margin-bottom:0.375rem;">
                        <span>⚙️ Mode Pengembangan Lokal:</span>
                    </div>
                    <p style="font-size:0.8125rem;color:var(--teks-redup);margin-bottom:0.875rem;">
                        Ingin menguji coba langsung formulir setup dengan <strong>aturan password baru (8 huruf, huruf besar-kecil, angka, & meter keamanan)</strong>?
                    </p>
                    <form action="<?= url('admin/setup.php') ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= token_csrf() ?>">
                        <input type="hidden" name="aksi_reset_admin" value="1">
                        <button type="submit" class="tombol tombol-oranye tombol-sm" style="width:100%;" onclick="return confirm('Reset akun admin demo ini agar formulir setup dengan aturan password baru dapat diuji coba?');">
                            Reset Akun Demo & Uji Coba Setup Baru &rarr;
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$errors = [];
$nama = '';
$email = '';
$flash = pesan_flash();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!cek_csrf()) {
        $errors['umum'] = 'Permintaan tidak valid atau sesi telah kedaluwarsa. Silakan muat ulang halaman.';
    } else {
        $nama = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        $konfirmasi_password = (string)($_POST['konfirmasi_password'] ?? '');

        // Validasi Nama Lengkap
        if ($nama === '') {
            $errors['nama'] = 'Nama lengkap wajib diisi.';
        } elseif (mb_strlen($nama) > 100) {
            $errors['nama'] = 'Nama terlalu panjang (maksimal 100 karakter).';
        }

        // Validasi Alamat Email
        if ($email === '') {
            $errors['email'] = 'Alamat email wajib diisi.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format alamat email tidak valid.';
        }

        // Validasi Aturan Kata Sandi Ketat
        if ($password === '') {
            $errors['password'] = 'Kata sandi wajib diisi.';
        } else {
            $syarat_kurang = [];
            if (strlen($password) < 8) {
                $syarat_kurang[] = 'minimal 8 karakter';
            }
            if (!preg_match('/[A-Z]/', $password)) {
                $syarat_kurang[] = 'setidaknya 1 huruf besar (A-Z)';
            }
            if (!preg_match('/[a-z]/', $password)) {
                $syarat_kurang[] = 'setidaknya 1 huruf kecil (a-z)';
            }
            if (!preg_match('/[0-9]/', $password)) {
                $syarat_kurang[] = 'setidaknya 1 angka (0-9)';
            }

            if (!empty($syarat_kurang)) {
                $errors['password'] = 'Kata sandi belum memenuhi aturan: harus mengandung ' . implode(', ', $syarat_kurang) . '.';
            }
        }

        // Validasi Konfirmasi Kata Sandi
        if ($konfirmasi_password === '') {
            $errors['konfirmasi_password'] = 'Ulangi kata sandi wajib diisi.';
        } elseif ($password !== $konfirmasi_password) {
            $errors['konfirmasi_password'] = 'Ulangi kata sandi tidak cocok dengan kata sandi di atas.';
        }

        if (empty($errors)) {
            try {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt_insert = db()->prepare("
                    INSERT INTO admin (nama, email, password_hash, dibuat_pada)
                    VALUES (:nama, :email, :hash, NOW())
                ");
                $stmt_insert->execute([
                    ':nama'  => $nama,
                    ':email' => $email,
                    ':hash'  => $hash,
                ]);

                pesan_flash('hijau', 'Akun administrator berhasil dibuat dengan kata sandi yang kuat! Silakan masuk.');
                alihkan('login.php');
                exit;
            } catch (Throwable $e) {
                $errors['umum'] = pesan_error_db('Gagal menyimpan data admin', $e);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Admin Pertama - Pekarangan Institute</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= url('assets/css/admin.css') ?>">
</head>
<body class="halaman-auth">
    <div class="kartu-auth" style="max-width: 480px;">
        <div class="kartu-auth-kepala">
            <div class="kartu-auth-logo">P</div>
            <h1 class="kartu-auth-judul">Setup Admin Pertama</h1>
            <p class="kartu-auth-sub">Buat akun pengelola utama Pekarangan Institute</p>
        </div>

        <!-- Pengingat Tetap Keamanan -->
        <div class="kotak-peringatan" role="note" style="margin-bottom:1.25rem;">
            <strong>Pengingat Keamanan:</strong> Demi keamanan sistem, setelah akun ini berhasil dibuat, disarankan untuk menghapus atau memindahkan berkas <code>admin/setup.php</code> dari server.
        </div>

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

        <?php if (!empty($errors['umum'])): ?>
            <div class="kotak-error" role="alert" style="margin-bottom:1.25rem;">
                <?= e($errors['umum']) ?>
            </div>
        <?php endif; ?>

        <form action="<?= url('admin/setup.php') ?>" method="POST" novalidate>
            <input type="hidden" name="csrf_token" value="<?= token_csrf() ?>">

            <!-- Nama Lengkap -->
            <div class="admin-form-grup">
                <label for="nama">Nama Lengkap <span class="wajib">*</span></label>
                <input type="text" id="nama" name="nama" class="admin-input" value="<?= e($nama) ?>" required autocomplete="name" placeholder="Nama Administrator" autofocus>
                <?php if (!empty($errors['nama'])): ?>
                    <div class="pesan-error"><?= e($errors['nama']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Email -->
            <div class="admin-form-grup">
                <label for="email">Alamat Email <span class="wajib">*</span></label>
                <input type="email" id="email" name="email" class="admin-input" value="<?= e($email) ?>" required autocomplete="email" placeholder="admin@pekarangan.org">
                <?php if (!empty($errors['email'])): ?>
                    <div class="pesan-error"><?= e($errors['email']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Kata Sandi -->
            <div class="admin-form-grup">
                <label for="password">Kata Sandi <span class="wajib">*</span></label>
                <div class="input-password-wadah">
                    <input type="password" id="password" name="password" class="admin-input" required autocomplete="new-password" placeholder="Masukkan kata sandi">
                    <button type="button" class="tombol-toggle-pwd" aria-label="Tampilkan kata sandi">Tampilkan</button>
                </div>

                <!-- Indikator Bar Tingkat Keamanan -->
                <div class="meter-password-wadah" aria-live="polite">
                    <div class="meter-password-track" title="Tingkat Keamanan Kata Sandi">
                        <div class="meter-password-segmen" id="segmen-1"></div>
                        <div class="meter-password-segmen" id="segmen-2"></div>
                        <div class="meter-password-segmen" id="segmen-3"></div>
                    </div>
                    <div class="meter-password-info">
                        <span class="meter-password-label">Kekuatan Kata Sandi:</span>
                        <span id="status-kekuatan-teks" class="meter-password-status status-kekuatan-kosong">Belum diisi</span>
                    </div>
                </div>

                <!-- Checklist Aturan Kata Sandi -->
                <ul class="syarat-password-daftar" aria-label="Aturan Keamanan Kata Sandi">
                    <li class="syarat-password-item" id="syarat-panjang">
                        <span class="syarat-ikon">&bull;</span>
                        <span>Minimal 8 karakter</span>
                    </li>
                    <li class="syarat-password-item" id="syarat-huruf">
                        <span class="syarat-ikon">&bull;</span>
                        <span>Kombinasi huruf besar (A-Z) & huruf kecil (a-z)</span>
                    </li>
                    <li class="syarat-password-item" id="syarat-angka">
                        <span class="syarat-ikon">&bull;</span>
                        <span>Sertakan setidaknya 1 angka (0-9)</span>
                    </li>
                </ul>

                <?php if (!empty($errors['password'])): ?>
                    <div class="pesan-error"><?= e($errors['password']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Konfirmasi Password -->
            <div class="admin-form-grup">
                <label for="konfirmasi_password">Ulangi Kata Sandi <span class="wajib">*</span></label>
                <div class="input-password-wadah">
                    <input type="password" id="konfirmasi_password" name="konfirmasi_password" class="admin-input" required autocomplete="new-password" placeholder="Ulangi kata sandi">
                    <button type="button" class="tombol-toggle-pwd" aria-label="Tampilkan kata sandi">Tampilkan</button>
                </div>
                <div id="status-konfirmasi-teks" class="status-konfirmasi-password"></div>
                <?php if (!empty($errors['konfirmasi_password'])): ?>
                    <div class="pesan-error"><?= e($errors['konfirmasi_password']) ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="tombol tombol-utama" style="width:100%;margin-top:0.75rem;">
                Buat Akun Administrator
            </button>
        </form>

        <div class="kartu-auth-kaki">
            <a href="<?= url('index.php') ?>">&larr; Kembali ke beranda</a>
        </div>
    </div>

    <script src="<?= url('assets/js/admin.js') ?>"></script>
</body>
</html>
