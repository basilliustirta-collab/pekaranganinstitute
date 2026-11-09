<?php
/**
 * Aksi Pemrosesan Produk - Pekarangan Institute
 * Menangani tambah, ubah, ubah status, dan hapus produk.
 * Memastikan proteksi CSRF, validasi integritas transaksi pesanan, dan pembersihan berkas foto.
 */

require_once __DIR__ . '/../includes/fungsi.php';
wajib_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !cek_csrf()) {
    pesan_flash('merah', 'Permintaan tidak valid atau token keamanan kedaluwarsa.');
    alihkan('admin/produk.php');
}

$aksi = trim($_POST['aksi'] ?? '');

switch ($aksi) {
    case 'tambah':
    case 'ubah':
        $adalah_edit = ($aksi === 'ubah');
        $id = (int)($_POST['id'] ?? 0);

        if ($adalah_edit && $id <= 0) {
            pesan_flash('merah', 'ID produk tidak valid.');
            alihkan('admin/produk.php');
        }

        $nama = trim($_POST['nama'] ?? '');
        $slug_input = trim($_POST['slug'] ?? '');
        $kategori = trim($_POST['kategori'] ?? '');
        $harga_input = trim($_POST['harga'] ?? '');
        $stok_input = trim($_POST['stok'] ?? '');
        $deskripsi_singkat = trim($_POST['deskripsi_singkat'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $penjual = trim($_POST['penjual'] ?? '');
        $status = trim($_POST['status'] ?? 'draf');

        $errors = [];

        if ($nama === '') {
            $errors['nama'] = 'Nama produk wajib diisi.';
        } elseif (mb_strlen($nama) > 200) {
            $errors['nama'] = 'Nama produk terlalu panjang (maksimal 200 karakter).';
        }

        $slug = buat_slug($slug_input !== '' ? $slug_input : $nama);
        // Cek keunikan slug
        try {
            $sql_slug = $adalah_edit
                ? "SELECT COUNT(*) FROM produk WHERE slug = :slug AND id != :id"
                : "SELECT COUNT(*) FROM produk WHERE slug = :slug";
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

        if ($kategori === '') {
            $errors['kategori'] = 'Kategori produk wajib diisi.';
        } elseif (mb_strlen($kategori) > 100) {
            $errors['kategori'] = 'Kategori terlalu panjang (maksimal 100 karakter).';
        }

        if ($harga_input === '' || !is_numeric($harga_input) || (int)$harga_input < 0) {
            $errors['harga'] = 'Harga harus berupa angka dan tidak boleh negatif.';
        }
        $harga = (int)$harga_input;

        if ($stok_input === '' || !is_numeric($stok_input) || (int)$stok_input < 0) {
            $errors['stok'] = 'Stok harus berupa angka dan minimal 0.';
        }
        $stok = (int)$stok_input;

        if ($deskripsi_singkat === '') {
            $errors['deskripsi_singkat'] = 'Deskripsi singkat wajib diisi.';
        } elseif (mb_strlen($deskripsi_singkat) > 160) {
            $errors['deskripsi_singkat'] = 'Deskripsi singkat tidak boleh lebih dari 160 karakter.';
        }

        if ($deskripsi === '') {
            $errors['deskripsi'] = 'Deskripsi lengkap wajib diisi.';
        }

        if (!in_array($status, ['draf', 'tampil', 'arsip'], true)) {
            $errors['status'] = 'Status produk tidak valid.';
        }

        // Ambil data produk lama jika mode edit
        $produk_lama = null;
        if ($adalah_edit) {
            $stmt_lama = db()->prepare("SELECT * FROM produk WHERE id = :id LIMIT 1");
            $stmt_lama->execute([':id' => $id]);
            $produk_lama = $stmt_lama->fetch();
        }

        // Penanganan Unggahan Foto
        $nama_foto = $produk_lama['foto'] ?? null;
        if (!empty($_FILES['foto']['name'])) {
            $hasil_unggah = unggah_berkas($_FILES['foto'], 'gambar');
            if (!$hasil_unggah['sukses']) {
                $errors['foto'] = $hasil_unggah['pesan'];
            } else {
                $nama_foto_baru = $hasil_unggah['nama'];
                // Hapus foto lama jika ada dan bukan data contoh bawaan
                if (!empty($nama_foto) && empty($produk_lama['is_contoh'])) {
                    $jalur_lama = __DIR__ . '/../uploads/' . $nama_foto;
                    if (file_exists($jalur_lama) && is_file($jalur_lama)) {
                        @unlink($jalur_lama);
                    }
                }
                $nama_foto = $nama_foto_baru;
            }
        }

        // Jika terdapat error validasi, kembalikan ke form
        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_input'] = $_POST;
            $tujuan = $adalah_edit ? 'admin/produk-form.php?id=' . $id : 'admin/produk-form.php';
            alihkan($tujuan);
        }

        // Simpan ke database
        try {
            if ($adalah_edit) {
                $stmt = db()->prepare("
                    UPDATE produk SET 
                        nama = :nama,
                        slug = :slug,
                        kategori = :kategori,
                        harga = :harga,
                        stok = :stok,
                        deskripsi_singkat = :deskripsi_singkat,
                        deskripsi = :deskripsi,
                        penjual = :penjual,
                        foto = :foto,
                        status = :status,
                        diubah_pada = NOW()
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':nama'              => $nama,
                    ':slug'              => $slug,
                    ':kategori'          => $kategori,
                    ':harga'             => $harga,
                    ':stok'              => $stok,
                    ':deskripsi_singkat' => $deskripsi_singkat,
                    ':deskripsi'         => $deskripsi,
                    ':penjual'           => $penjual,
                    ':foto'              => $nama_foto,
                    ':status'            => $status,
                    ':id'                => $id,
                ]);
                pesan_flash('hijau', 'Produk "' . $nama . '" berhasil diperbarui.');
            } else {
                $stmt = db()->prepare("
                    INSERT INTO produk (nama, slug, kategori, harga, stok, deskripsi_singkat, deskripsi, penjual, foto, status, is_contoh, dibuat_pada)
                    VALUES (:nama, :slug, :kategori, :harga, :stok, :deskripsi_singkat, :deskripsi, :penjual, :foto, :status, 0, NOW())
                ");
                $stmt->execute([
                    ':nama'              => $nama,
                    ':slug'              => $slug,
                    ':kategori'          => $kategori,
                    ':harga'             => $harga,
                    ':stok'              => $stok,
                    ':deskripsi_singkat' => $deskripsi_singkat,
                    ':deskripsi'         => $deskripsi,
                    ':penjual'           => $penjual,
                    ':foto'              => $nama_foto,
                    ':status'            => $status,
                ]);
                pesan_flash('hijau', 'Produk baru "' . $nama . '" berhasil ditambahkan.');
            }
        } catch (Throwable $e) {
            pesan_flash('merah', pesan_error_db('Gagal menyimpan data produk', $e));
        }

        alihkan('admin/produk.php');
        break;

    case 'ubah_status':
        $id = (int)($_POST['id'] ?? 0);
        $status_baru = trim($_POST['status_baru'] ?? '');

        if ($id <= 0 || !in_array($status_baru, ['tampil', 'draf', 'arsip'], true)) {
            pesan_flash('merah', 'Data status tidak valid.');
            alihkan('admin/produk.php');
        }

        try {
            $stmt = db()->prepare("UPDATE produk SET status = :status, diubah_pada = NOW() WHERE id = :id");
            $stmt->execute([':status' => $status_baru, ':id' => $id]);
            pesan_flash('hijau', 'Status produk berhasil diubah menjadi ' . $status_baru . '.');
        } catch (Throwable $e) {
            pesan_flash('merah', pesan_error_db('Gagal memperbarui status', $e));
        }
        alihkan('admin/produk.php');
        break;

    case 'hapus':
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            pesan_flash('merah', 'ID produk tidak valid.');
            alihkan('admin/produk.php');
        }

        try {
            // Periksa apakah produk pernah dipesan
            $stmt_cek = db()->prepare("SELECT COUNT(*) FROM pesanan WHERE produk_id = :id");
            $stmt_cek->execute([':id' => $id]);
            $pernah_dipesan = (int)$stmt_cek->fetchColumn();

            if ($pernah_dipesan > 0) {
                pesan_flash('kuning', 'Produk ini tidak dapat dihapus karena sudah memiliki ' . $pernah_dipesan . ' riwayat pesanan. Anda dapat memilih untuk Mengarsipkan produk ini agar tidak muncul lagi di publik.');
                alihkan('admin/produk.php');
            }

            // Ambil info foto sebelum hapus record
            $stmt_foto = db()->prepare("SELECT foto, is_contoh, nama FROM produk WHERE id = :id LIMIT 1");
            $stmt_foto->execute([':id' => $id]);
            $data_produk = $stmt_foto->fetch();

            if (!$data_produk) {
                pesan_flash('merah', 'Produk tidak ditemukan.');
                alihkan('admin/produk.php');
            }

            // Hapus file foto dari disk jika ada dan bukan data contoh
            if (!empty($data_produk['foto']) && empty($data_produk['is_contoh'])) {
                $jalur_foto = __DIR__ . '/../uploads/' . $data_produk['foto'];
                if (file_exists($jalur_foto) && is_file($jalur_foto)) {
                    @unlink($jalur_foto);
                }
            }

            // Hapus dari database
            $stmt_del = db()->prepare("DELETE FROM produk WHERE id = :id");
            $stmt_del->execute([':id' => $id]);

            pesan_flash('hijau', 'Produk "' . $data_produk['nama'] . '" berhasil dihapus.');
        } catch (Throwable $e) {
            pesan_flash('merah', pesan_error_db('Gagal menghapus produk', $e));
        }

        alihkan('admin/produk.php');
        break;

    default:
        pesan_flash('merah', 'Aksi tidak dikenali.');
        alihkan('admin/produk.php');
        break;
}
