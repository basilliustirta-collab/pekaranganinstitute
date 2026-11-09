<?php
/**
 * Aksi Pemrosesan Program Sosial - Pekarangan Institute
 * Menangani tambah, ubah, ubah status, dan hapus program sosial.
 * Melindungi integritas data donasi (tidak boleh hapus program berdonasi) dan berkas sampul.
 */

require_once __DIR__ . '/../includes/fungsi.php';
wajib_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !cek_csrf()) {
    pesan_flash('merah', 'Permintaan tidak valid atau token keamanan kedaluwarsa.');
    alihkan('admin/program.php');
}

$aksi = trim($_POST['aksi'] ?? '');

switch ($aksi) {
    case 'tambah':
    case 'ubah':
        $adalah_edit = ($aksi === 'ubah');
        $id = (int)($_POST['id'] ?? 0);

        if ($adalah_edit && $id <= 0) {
            pesan_flash('merah', 'ID program tidak valid.');
            alihkan('admin/program.php');
        }

        $judul = trim($_POST['judul'] ?? '');
        $slug_input = trim($_POST['slug'] ?? '');
        $ringkasan = trim($_POST['ringkasan'] ?? '');
        $cerita = trim($_POST['cerita'] ?? '');
        $target_input = trim($_POST['target_dana'] ?? '');
        $tenggat_input = trim($_POST['tenggat'] ?? '');
        $lokasi = trim($_POST['lokasi'] ?? '');
        $penerima_manfaat = trim($_POST['penerima_manfaat'] ?? '');
        $status = trim($_POST['status'] ?? 'aktif');

        $errors = [];

        if ($judul === '') {
            $errors['judul'] = 'Judul program wajib diisi.';
        } elseif (mb_strlen($judul) > 200) {
            $errors['judul'] = 'Judul program terlalu panjang (maksimal 200 karakter).';
        }

        $slug = buat_slug($slug_input !== '' ? $slug_input : $judul);
        try {
            $sql_slug = $adalah_edit
                ? "SELECT COUNT(*) FROM program WHERE slug = :slug AND id != :id"
                : "SELECT COUNT(*) FROM program WHERE slug = :slug";
            $stmt_slug = db()->prepare($sql_slug);
            $stmt_slug->bindValue(':slug', $slug);
            if ($adalah_edit) {
                $stmt_slug->bindValue(':id', $id, PDO::PARAM_INT);
            }
            $stmt_slug->execute();
            if ((int)$stmt_slug->fetchColumn() > 0) {
                $slug = $slug . '-' . time();
            }
        } catch (Throwable $e) {}

        if ($target_input === '' || !is_numeric($target_input) || (int)$target_input <= 0) {
            $errors['target_dana'] = 'Target dana harus berupa angka lebih besar dari 0.';
        }
        $target_dana = (int)$target_input;

        $tenggat = null;
        if ($tenggat_input !== '') {
            $waktu_tenggat = strtotime($tenggat_input);
            if (!$waktu_tenggat) {
                $errors['tenggat'] = 'Format tanggal tenggat tidak valid.';
            } else {
                $tenggat = date('Y-m-d', $waktu_tenggat);
            }
        }

        if ($lokasi === '') {
            $errors['lokasi'] = 'Lokasi program wajib diisi.';
        }

        if ($penerima_manfaat === '') {
            $errors['penerima_manfaat'] = 'Sasaran penerima manfaat wajib diisi.';
        }

        if ($ringkasan === '') {
            $errors['ringkasan'] = 'Ringkasan program wajib diisi.';
        } elseif (mb_strlen($ringkasan) > 255) {
            $errors['ringkasan'] = 'Ringkasan tidak boleh lebih dari 255 karakter.';
        }

        if ($cerita === '') {
            $errors['cerita'] = 'Cerita program wajib diisi.';
        }

        if (!in_array($status, ['aktif', 'draf', 'selesai'], true)) {
            $errors['status'] = 'Status program tidak valid.';
        }

        // Ambil data program lama jika edit
        $program_lama = null;
        if ($adalah_edit) {
            $stmt_lama = db()->prepare("SELECT * FROM program WHERE id = :id LIMIT 1");
            $stmt_lama->execute([':id' => $id]);
            $program_lama = $stmt_lama->fetch();
        }

        // Penanganan Unggahan Sampul
        $foto_sampul = $program_lama['foto_sampul'] ?? null;
        if (!empty($_FILES['foto_sampul']['name'])) {
            $hasil_unggah = unggah_berkas($_FILES['foto_sampul'], 'gambar');
            if (!$hasil_unggah['sukses']) {
                $errors['foto_sampul'] = $hasil_unggah['pesan'];
            } else {
                $foto_baru = $hasil_unggah['nama'];
                if (!empty($foto_sampul) && empty($program_lama['is_contoh'])) {
                    $jalur_lama = __DIR__ . '/../uploads/' . $foto_sampul;
                    if (file_exists($jalur_lama) && is_file($jalur_lama)) {
                        @unlink($jalur_lama);
                    }
                }
                $foto_sampul = $foto_baru;
            }
        }

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_input'] = $_POST;
            $tujuan = $adalah_edit ? 'admin/program-form.php?id=' . $id : 'admin/program-form.php';
            alihkan($tujuan);
        }

        try {
            if ($adalah_edit) {
                $stmt = db()->prepare("
                    UPDATE program SET 
                        judul = :judul,
                        slug = :slug,
                        ringkasan = :ringkasan,
                        cerita = :cerita,
                        target_dana = :target_dana,
                        tenggat = :tenggat,
                        lokasi = :lokasi,
                        penerima_manfaat = :penerima_manfaat,
                        foto_sampul = :foto_sampul,
                        status = :status,
                        diubah_pada = NOW()
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':judul'            => $judul,
                    ':slug'             => $slug,
                    ':ringkasan'        => $ringkasan,
                    ':cerita'           => $cerita,
                    ':target_dana'      => $target_dana,
                    ':tenggat'          => $tenggat,
                    ':lokasi'           => $lokasi,
                    ':penerima_manfaat' => $penerima_manfaat,
                    ':foto_sampul'      => $foto_sampul,
                    ':status'           => $status,
                    ':id'               => $id,
                ]);
                pesan_flash('hijau', 'Program "' . $judul . '" berhasil diperbarui.');
            } else {
                $stmt = db()->prepare("
                    INSERT INTO program (judul, slug, ringkasan, cerita, target_dana, tenggat, lokasi, penerima_manfaat, foto_sampul, status, is_contoh, dibuat_pada)
                    VALUES (:judul, :slug, :ringkasan, :cerita, :target_dana, :tenggat, :lokasi, :penerima_manfaat, :foto_sampul, :status, 0, NOW())
                ");
                $stmt->execute([
                    ':judul'            => $judul,
                    ':slug'             => $slug,
                    ':ringkasan'        => $ringkasan,
                    ':cerita'           => $cerita,
                    ':target_dana'      => $target_dana,
                    ':tenggat'          => $tenggat,
                    ':lokasi'           => $lokasi,
                    ':penerima_manfaat' => $penerima_manfaat,
                    ':foto_sampul'      => $foto_sampul,
                    ':status'           => $status,
                ]);
                pesan_flash('hijau', 'Program baru "' . $judul . '" berhasil dibuat.');
            }
        } catch (Throwable $e) {
            pesan_flash('merah', pesan_error_db('Gagal menyimpan program', $e));
        }

        alihkan('admin/program.php');
        break;

    case 'ubah_status':
        $id = (int)($_POST['id'] ?? 0);
        $status_baru = trim($_POST['status_baru'] ?? '');

        if ($id <= 0 || !in_array($status_baru, ['aktif', 'draf', 'selesai'], true)) {
            pesan_flash('merah', 'Data status tidak valid.');
            alihkan('admin/program.php');
        }

        try {
            $stmt = db()->prepare("UPDATE program SET status = :status, diubah_pada = NOW() WHERE id = :id");
            $stmt->execute([':status' => $status_baru, ':id' => $id]);
            pesan_flash('hijau', 'Status program berhasil diubah menjadi ' . $status_baru . '.');
        } catch (Throwable $e) {
            pesan_flash('merah', pesan_error_db('Gagal memperbarui status', $e));
        }
        alihkan('admin/program.php');
        break;

    case 'hapus':
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            pesan_flash('merah', 'ID program tidak valid.');
            alihkan('admin/program.php');
        }

        try {
            // Periksa apakah program memiliki riwayat donasi
            $stmt_cek = db()->prepare("SELECT COUNT(*) FROM donasi WHERE program_id = :id");
            $stmt_cek->execute([':id' => $id]);
            $ada_donasi = (int)$stmt_cek->fetchColumn();

            if ($ada_donasi > 0) {
                pesan_flash('kuning', 'Program ini tidak dapat dihapus karena sudah memiliki ' . $ada_donasi . ' catatan donasi. Silakan ubah status program menjadi Selesai.');
                alihkan('admin/program.php');
            }

            // Ambil data foto sampul sebelum dihapus
            $stmt_sampul = db()->prepare("SELECT foto_sampul, is_contoh, judul FROM program WHERE id = :id LIMIT 1");
            $stmt_sampul->execute([':id' => $id]);
            $data_prog = $stmt_sampul->fetch();

            if (!$data_prog) {
                pesan_flash('merah', 'Program tidak ditemukan.');
                alihkan('admin/program.php');
            }

            if (!empty($data_prog['foto_sampul']) && empty($data_prog['is_contoh'])) {
                $jalur_sampul = __DIR__ . '/../uploads/' . $data_prog['foto_sampul'];
                if (file_exists($jalur_sampul) && is_file($jalur_sampul)) {
                    @unlink($jalur_sampul);
                }
            }

            $stmt_del = db()->prepare("DELETE FROM program WHERE id = :id");
            $stmt_del->execute([':id' => $id]);

            pesan_flash('hijau', 'Program "' . $data_prog['judul'] . '" berhasil dihapus.');
        } catch (Throwable $e) {
            pesan_flash('merah', pesan_error_db('Gagal menghapus program', $e));
        }

        alihkan('admin/program.php');
        break;

    default:
        pesan_flash('merah', 'Aksi tidak dikenali.');
        alihkan('admin/program.php');
        break;
}
