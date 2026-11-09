<?php
/**
 * Kumpulan Fungsi Pembantu (Helper) Pekarangan Institute
 * Fungsi sederhana, singkat, dan terhindar dari ketergantungan berlebih.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

/**
 * Mencegah XSS dengan mengubah karakter khusus ke entitas HTML
 */
function e(?string $nilai): string
{
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Format angka ke mata uang Rupiah
 */
function rupiah(int|float|null $angka): string
{
    return 'Rp ' . number_format((float)($angka ?? 0), 0, ',', '.');
}

/**
 * Menghasilkan URL lengkap berbasis BASE_URL
 */
function url(string $jalur = ''): string
{
    $basis = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
    $bersih = ltrim($jalur, '/');
    return $bersih === '' ? ($basis ?: '/') : $basis . '/' . $bersih;
}

/**
 * Mengalihkan halaman ke URL internal yang aman
 */
function alihkan(string $ke): void
{
    // Cegah redirect keluar domain (open redirect)
    // Abaikan jika menuju skema eksternal (http, https, //, javascript)
    if (preg_match('#^(?:[a-z]+:)?//#i', $ke) || str_starts_with($ke, 'javascript:')) {
        $ke = 'index.php';
    }

    $basis = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
    if ($basis !== '' && str_starts_with($ke, $basis . '/')) {
        header('Location: ' . $ke);
    } else {
        header('Location: ' . url($ke));
    }
    exit;
}

/**
 * Format pesan error database aman yang menyembunyikan detail teknis di mode produksi
 */
function pesan_error_db(string $pesan_aman, Throwable $e): string
{
    if (defined('APP_ENV') && APP_ENV === 'lokal') {
        return $pesan_aman . ': ' . $e->getMessage();
    }
    return $pesan_aman . '. Silakan coba lagi atau hubungi pengelola sistem.';
}

/**
 * Menyimpan atau mengambil pesan kilas (flash message) dari sesi
 */
function pesan_flash(?string $tipe = null, ?string $pesan = null): ?array
{
    mulai_sesi_aman();
    if ($tipe !== null && $pesan !== null) {
        $_SESSION['flash'] = ['tipe' => $tipe, 'pesan' => $pesan];
        return null;
    }
    if (isset($_SESSION['flash'])) {
        $data = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $data;
    }
    return null;
}

/**
 * Membuat atau mengambil token CSRF dari sesi
 */
function token_csrf(): string
{
    mulai_sesi_aman();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Memvalidasi token CSRF dari kiriman form
 */
function cek_csrf(?string $token = null): bool
{
    mulai_sesi_aman();
    $token_sesi = $_SESSION['csrf_token'] ?? '';
    $token_kiriman = $token ?? $_POST['csrf_token'] ?? '';
    return !empty($token_sesi) && hash_equals($token_sesi, (string)$token_kiriman);
}

/**
 * Membuat slug teks yang aman untuk URL
 */
function buat_slug(string $teks): string
{
    $slug = preg_replace('~[^\pL\d]+~u', '-', $teks);
    $slug = iconv('utf-8', 'us-ascii//TRANSLIT', $slug) ?: $slug;
    $slug = preg_replace('~[^-\w]+~', '', $slug);
    $slug = trim($slug, '-');
    $slug = preg_replace('~-+~', '-', $slug);
    $slug = strtolower($slug);
    return empty($slug) ? 'item-' . time() : $slug;
}

/**
 * Membuat kode unik untuk pesanan (ORD) atau donasi (DON)
 */
function buat_kode(string $awalan): string
{
    $tanggal = date('Ymd');
    $karakter = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
    $acak = '';
    $panjang = strlen($karakter);
    for ($i = 0; $i < 4; $i++) {
        $acak .= $karakter[random_int(0, $panjang - 1)];
    }
    return sprintf('%s-%s-%s', strtoupper($awalan), $tanggal, $acak);
}

/**
 * Mengambil nilai pengaturan berdasarkan kunci dengan cache memori lokal
 */
function ambil_pengaturan(string $kunci, ?string $bawaan = null): ?string
{
    static $cache = null;

    if ($cache === null) {
        $cache = [];
        try {
            $stmt = db()->query("SELECT kunci, nilai FROM pengaturan");
            while ($baris = $stmt->fetch()) {
                $cache[$baris['kunci']] = $baris['nilai'];
            }
        } catch (Throwable $e) {
            // Jika tabel belum siap, kembalikan nilai bawaan
            return $bawaan;
        }
    }

    return $cache[$kunci] ?? $bawaan;
}

/**
 * Menghitung progres dana terkumpul suatu program dari donasi berhasil
 */
function progres_program(int $program_id): array
{
    try {
        $stmt_prog = db()->prepare("SELECT target_dana FROM program WHERE id = :id LIMIT 1");
        $stmt_prog->execute([':id' => $program_id]);
        $target = (int)($stmt_prog->fetchColumn() ?: 0);

        $stmt_don = db()->prepare("
            SELECT COALESCE(SUM(jumlah), 0) AS total, COUNT(id) AS donatur
            FROM donasi
            WHERE program_id = :id AND status = 'berhasil'
        ");
        $stmt_don->execute([':id' => $program_id]);
        $hasil = $stmt_don->fetch() ?: ['total' => 0, 'donatur' => 0];

        $terkumpul = (int)$hasil['total'];
        $donatur = (int)$hasil['donatur'];
        $persen = $target > 0 ? min(100, (int)round(($terkumpul / $target) * 100)) : 0;

        return [
            'target'        => $target,
            'terkumpul'     => $terkumpul,
            'persen'        => $persen,
            'jumlah_donatur'=> $donatur
        ];
    } catch (Throwable $e) {
        return ['target' => 0, 'terkumpul' => 0, 'persen' => 0, 'jumlah_donatur' => 0];
    }
}

/**
 * Mengunggah berkas dengan validasi ukuran, MIME, dan ekstensi
 */
function unggah_berkas(array $berkas, string $kategori = 'gambar'): array
{
    if (($berkas['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return ['sukses' => false, 'nama' => null, 'pesan' => 'Terjadi kesalahan saat mengunggah berkas.'];
    }

    $aturan = ($kategori === 'gambar')
        ? [
            'max'  => BATAS_UNGGAH_GAMBAR,
            'label_max' => '2 MB',
            'mime' => ['image/jpeg', 'image/png', 'image/webp'],
            'ext'  => ['jpg', 'jpeg', 'png', 'webp'],
            'label_format' => 'JPG, PNG, atau WebP'
        ]
        : [
            'max'  => BATAS_UNGGAH_BERKAS,
            'label_max' => '5 MB',
            'mime' => ['application/pdf', 'image/jpeg', 'image/png'],
            'ext'  => ['pdf', 'jpg', 'jpeg', 'png'],
            'label_format' => 'PDF, JPG, atau PNG'
        ];

    if ($berkas['size'] > $aturan['max']) {
        return ['sukses' => false, 'nama' => null, 'pesan' => 'Ukuran berkas melebihi batas maksimal (' . $aturan['label_max'] . ').'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $tipe_mime = finfo_file($finfo, $berkas['tmp_name']);
    finfo_close($finfo);

    $ekstensi = strtolower(pathinfo($berkas['name'], PATHINFO_EXTENSION));
    if (!in_array($tipe_mime, $aturan['mime'], true) || !in_array($ekstensi, $aturan['ext'], true)) {
        return ['sukses' => false, 'nama' => null, 'pesan' => 'Format berkas tidak diizinkan. Hanya menerima berkas bertipe ' . $aturan['label_format'] . '.'];
    }

    // Untuk kategori gambar, pastikan berkas benar-benar gambar yang valid
    if ($kategori === 'gambar') {
        $info_gambar = @getimagesize($berkas['tmp_name']);
        if ($info_gambar === false || empty($info_gambar[0]) || empty($info_gambar[1])) {
            return ['sukses' => false, 'nama' => null, 'pesan' => 'Berkas bukan gambar yang valid.'];
        }
    }

    $nama_baru = bin2hex(random_bytes(16)) . '.' . $ekstensi;
    $tujuan = __DIR__ . '/../uploads/' . $nama_baru;

    if (!move_uploaded_file($berkas['tmp_name'], $tujuan)) {
        return ['sukses' => false, 'nama' => null, 'pesan' => 'Gagal menyimpan berkas ke server.'];
    }

    return ['sukses' => true, 'nama' => $nama_baru, 'pesan' => 'Berkas berhasil diunggah.'];
}

/**
 * Menghitung data paginasi sederhana
 */
function paginasi(int $total_item, int $per_halaman, int $halaman_saat_ini, string $url_format): array
{
    $total_halaman = max(1, (int)ceil($total_item / $per_halaman));
    $halaman = max(1, min($halaman_saat_ini, $total_halaman));
    $offset = ($halaman - 1) * $per_halaman;

    return [
        'total_item'    => $total_item,
        'total_halaman' => $total_halaman,
        'halaman'       => $halaman,
        'offset'        => $offset,
        'ada_sebelum'   => $halaman > 1,
        'ada_berikut'   => $halaman < $total_halaman,
        'url_sebelum'   => sprintf($url_format, $halaman - 1),
        'url_berikut'   => sprintf($url_format, $halaman + 1),
    ];
}

/**
 * Memvalidasi format nomor WhatsApp Indonesia
 * Mendukung format: 08xxx, 628xxx, +628xxx (panjang digit 10-15 angka)
 */
function validasi_nomor_wa(?string $nomor): bool
{
    if (empty($nomor)) {
        return false;
    }
    // Bersihkan karakter pemisah seperti spasi, titik, tanda hubung
    $bersih = preg_replace('/[\s\.\-]+/', '', trim($nomor));
    return (bool)preg_match('/^(?:\+62|62|0)8[1-9][0-9]{7,11}$/', $bersih);
}

/**
 * Menghasilkan tautan wa.me dengan teks pesan terenkode rapi
 */
function tautan_wa(?string $nomor, string $pesan = ''): string
{
    $nomor_bersih = preg_replace('/[^0-9]/', '', (string)$nomor);
    if (str_starts_with($nomor_bersih, '0')) {
        $nomor_bersih = '62' . substr($nomor_bersih, 1);
    }

    // Jika nomor tidak valid atau berupa placeholder contoh yang tidak ada angkanya
    $target = (strlen($nomor_bersih) >= 10) ? $nomor_bersih : '';
    $kueri = !empty($pesan) ? '?text=' . rawurlencode($pesan) : '';

    return 'https://wa.me/' . $target . $kueri;
}

/**
 * Format tanggal ke bahasa Indonesia sederhana (contoh: 25 Oktober 2026)
 */
function tanggal_id(?string $tanggal): string
{
    if (empty($tanggal)) {
        return '-';
    }
    $waktu = strtotime($tanggal);
    if (!$waktu) {
        return $tanggal;
    }
    $bulan_id = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $tgl = (int)date('j', $waktu);
    $bln = $bulan_id[(int)date('n', $waktu)] ?? date('m', $waktu);
    $thn = date('Y', $waktu);
    return $tgl . ' ' . $bln . ' ' . $thn;
}
