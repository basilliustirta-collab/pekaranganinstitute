<?php
/**
 * Aksi Pemrosesan Laporan Transparansi - Pekarangan Institute
 * Menangani tambah, ubah, dan hapus berkas laporan pertanggungjawaban publik.
 * Memastikan pembersihan berkas dokumen di server saat laporan dihapus atau diganti.
 */

require_once __DIR__ . '/../includes/fungsi.php';
wajib_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !cek_csrf()) {
    pesan_flash('merah', 'Permintaan tidak valid atau token keamanan kedaluwarsa.');
    alihkan('admin/laporan.php');
}

$aksi = trim($_POST['aksi'] ?? '');

switch ($aksi) {
    case 'tambah':
    case 'ubah':
        $adalah_edit = ($aksi === 'ubah');
        $id = (int)($_POST['id'] ?? 0);

        if ($adalah_edit && $id <= 0) {
            pesan_flash('merah', 'ID laporan tidak valid.');
            alihkan('admin/laporan.php');
        }

        $judul = trim($_POST['judul'] ?? '');
        $periode = trim($_POST['periode'] ?? '');
        $ringkasan = trim($_POST['ringkasan'] ?? '');
        $status = trim($_POST['status'] ?? 'tampil');

        $errors = [];

        if ($judul === '') {
            $errors['judul'] = 'Judul laporan wajib diisi.';
        } elseif (mb_strlen($judul) > 200) {
            $errors['judul'] = 'Judul terlalu panjang (maksimal 200 karakter).';
        }

        if ($periode === '') {
            $errors['periode'] = 'Periode laporan wajib diisi.';
        } elseif (mb_strlen($periode) > 100) {
            $errors['periode'] = 'Periode terlalu panjang (maksimal 100 karakter).';
        }

        if ($ringkasan === '') {
            $errors['ringkasan'] = 'Ringkasan laporan wajib diisi.';
        }

        if (!in_array($status, ['tampil', 'draf'], true)) {
            $errors['status'] = 'Status publikasi tidak valid.';
        }

        // Ambil data laporan lama jika edit
        $laporan_lama = null;
        if ($adalah_edit) {
            $stmt_lama = db()->prepare("SELECT * FROM laporan WHERE id = :id LIMIT 1");
            $stmt_lama->execute([':id' => $id]);
            $laporan_lama = $stmt_lama->fetch();
        }

        $nama_berkas = $laporan_lama['berkas'] ?? '';

        // Penanganan Unggahan Berkas
        if (!empty($_FILES['berkas']['name'])) {
            $hasil_unggah = unggah_berkas($_FILES['berkas'], 'laporan');
            if (!$hasil_unggah['sukses']) {
                $errors['berkas'] = $hasil_unggah['pesan'];
            } else {
                $berkas_baru = $hasil_unggah['nama'];
                // Hapus berkas lama jika bukan contoh
                if (!empty($nama_berkas) && empty($laporan_lama['is_contoh'])) {
                    $jalur_lama = __DIR__ . '/../uploads/' . $nama_berkas;
                    if (file_exists($jalur_lama) && is_file($jalur_lama)) {
                        @unlink($jalur_lama);
                    }
                }
                $nama_berkas = $berkas_baru;
            }
        } elseif (!$adalah_edit) {
            $errors['berkas'] = 'Berkas dokumen laporan wajib diunggah.';
        }

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_input'] = $_POST;
            $tujuan = $adalah_edit ? 'admin/laporan-form.php?id=' . $id : 'admin/laporan-form.php';
            alihkan($tujuan);
        }

        try {
            if ($adalah_edit) {
                $stmt = db()->prepare("
                    UPDATE laporan SET 
                        judul = :judul,
                        periode = :periode,
                        ringkasan = :ringkasan,
                        berkas = :berkas,
                        status = :status
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':judul'     => $judul,
                    ':periode'   => $periode,
                    ':ringkasan' => $ringkasan,
                    ':berkas'    => $nama_berkas,
                    ':status'    => $status,
                    ':id'        => $id,
                ]);
                pesan_flash('hijau', 'Laporan "' . $judul . '" berhasil diperbarui.');
            } else {
                $stmt = db()->prepare("
                    INSERT INTO laporan (judul, periode, ringkasan, berkas, status, is_contoh, dibuat_pada)
                    VALUES (:judul, :periode, :ringkasan, :berkas, :status, 0, NOW())
                ");
                $stmt->execute([
                    ':judul'     => $judul,
                    ':periode'   => $periode,
                    ':ringkasan' => $ringkasan,
                    ':berkas'    => $nama_berkas,
                    ':status'    => $status,
                ]);
                pesan_flash('hijau', 'Laporan baru "' . $judul . '" berhasil diunggah.');
            }
        } catch (Throwable $e) {
            pesan_flash('merah', pesan_error_db('Gagal menyimpan laporan', $e));
        }

        alihkan('admin/laporan.php');
        break;

    case 'hapus':
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            pesan_flash('merah', 'ID laporan tidak valid.');
            alihkan('admin/laporan.php');
        }

        try {
            $stmt_cek = db()->prepare("SELECT judul, berkas, is_contoh FROM laporan WHERE id = :id LIMIT 1");
            $stmt_cek->execute([':id' => $id]);
            $data_lap = $stmt_cek->fetch();

            if (!$data_lap) {
                pesan_flash('merah', 'Laporan tidak ditemukan.');
                alihkan('admin/laporan.php');
            }

            // Hapus berkas fisik dari server jika ada dan bukan data contoh
            if (!empty($data_lap['berkas']) && empty($data_lap['is_contoh'])) {
                $jalur_berkas = __DIR__ . '/../uploads/' . $data_lap['berkas'];
                if (file_exists($jalur_berkas) && is_file($jalur_berkas)) {
                    @unlink($jalur_berkas);
                }
            }

            $stmt_del = db()->prepare("DELETE FROM laporan WHERE id = :id");
            $stmt_del->execute([':id' => $id]);

            pesan_flash('hijau', 'Laporan "' . $data_lap['judul'] . '" berhasil dihapus beserta berkas fisiknya.');
        } catch (Throwable $e) {
            pesan_flash('merah', pesan_error_db('Gagal menghapus laporan', $e));
        }

        alihkan('admin/laporan.php');
        break;

    default:
        pesan_flash('merah', 'Aksi tidak dikenali.');
        alihkan('admin/laporan.php');
        break;
}
