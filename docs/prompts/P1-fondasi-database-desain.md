# PROMPT P1 - Fondasi, Database, Desain, Beranda/Tentang/Kontak

Baca `AGENTS.md`, `FEEDBACK.md`, `DECISIONS.md` terlebih dahulu dan patuhi seluruhnya (terutama batasan teknologi: PHP polos, MySQL/PDO, JS biasa, tanpa framework/npm).
Peran: Senior Web Developer + UI Designer. Prioritas: sederhana dan mudah dipahami pemula.

## Tugas
1. **Lihat isi folder** dan tulis rencana singkat (maks 10 baris).
2. **Buat struktur folder** persis seperti `AGENTS.md` bagian 5 (file yang belum dikerjakan di tahap ini boleh belum dibuat). Buat juga:
   - `.gitignore` (isi: `includes/config.php`, isi `uploads/` kecuali `.gitkeep`)
   - `includes/.htaccess` dan `database/.htaccess` berisi `Require all denied`
   - `uploads/.htaccess` yang mematikan eksekusi PHP (mis. `php_flag engine off` dan `RemoveHandler .php`, atau aturan setara), plus `uploads/.gitkeep`
3. **Database** `database/schema.sql` (MySQL/MariaDB, `utf8mb4`, `CREATE DATABASE IF NOT EXISTS pekarangan`, `USE pekarangan`). Tabel:
   - `admin`: id, nama, email (unik), password_hash, dibuat_pada
   - `produk`: id, nama, slug (unik), kategori (teks), harga (INT >= 0), stok (INT >= 0), deskripsi_singkat, deskripsi, penjual, foto, status ENUM('draf','tampil','arsip'), is_contoh TINYINT(1), dibuat_pada, diubah_pada
   - `program`: id, judul, slug (unik), ringkasan, cerita, target_dana (INT > 0), tenggat (DATE), lokasi, penerima_manfaat (teks umum), foto_sampul, status ENUM('draf','aktif','selesai'), is_contoh, dibuat_pada, diubah_pada
   - `pesanan`: id, kode (unik), produk_id (FK ke produk, ON DELETE RESTRICT), nama_produk (salinan nama saat dipesan), harga_satuan, jumlah, total, nama_pemesan, whatsapp, alamat, catatan, status ENUM('menunggu','dibayar','diproses','dikirim','selesai','batal'), is_contoh, dibuat_pada, diubah_pada
   - `donasi`: id, kode (unik), program_id (FK ke program, ON DELETE RESTRICT), nama_donatur, whatsapp, jumlah (INT > 0), anonim TINYINT(1), pesan, status ENUM('menunggu','berhasil','gagal'), is_contoh, dibuat_pada, diubah_pada
   - `laporan`: id, judul, periode, ringkasan, berkas, status ENUM('draf','tampil'), is_contoh, dibuat_pada
   - `pengaturan`: kunci (PRIMARY KEY), nilai (TEXT)
   Tambahkan indeks seperlunya (slug, status, program_id, produk_id). Gunakan CHECK/constraint bila didukung, dan tetap validasi di PHP.
4. **Data contoh** `database/seed.sql`: 6 produk contoh (nama `[Nama Produk] 1..6`, beberapa kategori berbeda, harga bulat sederhana, sebagian status 'tampil', 1 'draf'), 2 program contoh (judul `[Judul Program] 1..2`, target contoh), beberapa donasi `berhasil` dan `menunggu` serta 2 pesanan contoh, 1 laporan contoh. **Semua `is_contoh = 1`.** Isi `pengaturan` dengan kunci berikut (nilai placeholder jelas): `nama_situs`, `tagline`, `hero_judul`, `hero_teks`, `tentang_teks`, `whatsapp`, `email`, `alamat`, `info_pembayaran` (mis. "[Nama Bank] [Nomor Rekening] a.n. [Nama]"), `donasi_minimum` (10000), `alokasi_operasional` (angka placeholder), `alokasi_sosial` (angka placeholder; jumlahnya 100), `catatan_transparansi`.
5. **includes/**:
   - `config.sample.php`: DB_HOST, DB_NAMA, DB_USER, DB_PASS, `BASE_URL` (mis. `/pekarangan`), `APP_ENV` ('lokal' atau 'produksi'), batas unggah (gambar 2 MB, berkas laporan 5 MB). `config.php` dibuat pengguna dari sample dan tidak di-commit.
   - `db.php`: fungsi `db()` mengembalikan satu koneksi PDO (mode exception, fetch assoc, emulate prepares off, charset utf8mb4). Jika gagal terhubung, tampilkan halaman ramah ("Database belum siap, cek config.php") tanpa membocorkan detail saat `APP_ENV = 'produksi'`.
   - `fungsi.php`: helper pada `AGENTS.md` bagian 6 (`e`, `rupiah`, `url`, `alihkan`, `pesan_flash`, `token_csrf`, `cek_csrf`, `buat_slug` (unik), `buat_kode`, `ambil_pengaturan`, `unggah_berkas`, `paginasi`) dan `progres_program($program_id)` yang menghitung dari donasi berstatus `berhasil`.
   - `auth.php`: fungsi sesi (`mulai_sesi_aman()`, `admin_sedang_login()`, `wajib_login()`), belum perlu halaman login (di P3).
   - `header.php` dan `footer.php` untuk halaman publik (lihat desain).
6. **Desain** `assets/css/style.css` dan `assets/js/app.js` sesuai `AGENTS.md` bagian 7. CSS terstruktur: variabel warna, reset ringan, tipografi, kontainer, tombol, kartu, lencana (status dan `lencana-contoh`), form, progress bar, grid, navbar, footer, hero, langkah-langkah, alert/flash, `foto-kosong`. `app.js` hanya untuk menu hamburger (aksesibel: `aria-expanded`, bisa ditutup dengan Esc).
7. **Header publik:** logo (`assets/img/logo.png`, atau teks "PEKARANGAN INSTITUTE" bila file belum ada), menu: Beranda, Produk, Program, Transparansi, Tentang, Kontak, dan tombol "Donasi" (oranye). Hamburger di mobile. Tautan "lewati ke konten". **Footer:** tagline, kontak dari pengaturan, tautan menu, catatan kecil "Data contoh" bila ada data contoh.
8. **Halaman:**
   - `index.php` (Beranda), berurutan dan ringkas:
     1. **Hero:** label kecil "BERBELANJA - BERBAGI - BERDAMPAK", judul `hero_judul` ("Berbelanja, Berbagi, Berdampak."), paragraf `hero_teks`, tombol "Belanja Sekarang" (-> produk.php) dan "Lihat Program" (-> program.php), kotak `foto-kosong` di samping/bawah.
     2. **Tiga langkah:** Belanja -> Berbagi -> Berdampak, masing-masing dengan ikon sederhana dan satu kalimat.
     3. **Produk terbaru:** 3 kartu produk berstatus tampil (foto, kategori, nama, harga, tombol "Lihat").
     4. **Program berjalan:** 2 kartu program aktif dengan progress bar oranye dan tombol "Donasi".
     5. **Ajakan akhir:** satu blok dengan dua tombol (Belanja/Donasi) dan tautan ke Transparansi.
     Bagian yang datanya kosong menampilkan teks sopan (mis. "Belum ada produk."), bukan kosong.
   - `tentang.php`: cerita singkat dari `tentang_teks`, tiga nilai (Berbelanja/Berbagi/Berdampak), dan bagian "Bagaimana dana dikelola" yang membaca alokasi dari `pengaturan` (dengan `lencana-contoh` bila masih placeholder).
   - `kontak.php`: WhatsApp (tautan `wa.me`), email, alamat dari `pengaturan`. Tanpa form kirim pesan.
   - Setiap halaman punya `<title>`, `<meta name="description">`, Open Graph dasar, dan `lang="id"`.

## Definition of Done
- [ ] Struktur folder sesuai `AGENTS.md`; `config.php` tidak ada di repo
- [ ] `schema.sql` dan `seed.sql` bisa diimport berurutan di phpMyAdmin tanpa error (jika MySQL tersedia; jika tidak, katakan belum diuji)
- [ ] `php -l` lolos untuk semua file PHP (jika PHP tersedia)
- [ ] Beranda, Tentang, Kontak tampil tanpa error/warning PHP dengan `APP_ENV = 'lokal'`
- [ ] Rapi di 375px, 768px, 1280px; menu hamburger bisa dipakai dengan keyboard
- [ ] Semua data contoh berlabel "DATA CONTOH"; tidak ada klaim palsu
- [ ] Semua query memakai prepared statement; semua output memakai `e()`

## Akhiri dengan
Ringkasan: apa yang dibuat, langkah yang harus saya lakukan (import SQL, salin config, start XAMPP), hal yang perlu saya cek manual. **BERHENTI dan tunggu review.**
