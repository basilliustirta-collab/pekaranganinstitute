# AGENTS.md - Pekarangan Institute (Versi Sederhana: HTML, PHP, JS + MySQL)

Baca file ini di awal setiap sesi. Aturan di sini mengalahkan instruksi lain kecuali pengguna mengubahnya secara eksplisit (catat di DECISIONS.md).

## 1. Konteks
Pekarangan Institute adalah inisiatif kewirausahaan sosial. Konsep: **Berbelanja, Berbagi, Berdampak.**
Website ini: profil singkat, katalog produk, donasi untuk program sosial, transparansi dana, dan admin sederhana.
Ini **prototype untuk didiskusikan dengan mitra** dan akan berubah. Prioritas utama: **SEDERHANA** (untuk pengguna, admin, dan kode). Jika ragu antara fitur lengkap dan sederhana, pilih sederhana.

## 2. Aturan emas: jangan mengarang data
- Dilarang membuat statistik, total donasi/penjualan, jumlah penerima manfaat, lokasi, pencapaian, testimoni, atau nama orang sebagai fakta.
- Data contoh wajib bertanda: kolom `is_contoh = 1` di database dan label **"DATA CONTOH"** (`<span class="lencana-contoh">`) di tampilan. Teks placeholder: `[Nama Produk]`, `Rp X.XXX.XXX`, `[Foto]`, nomor `08xx-xxxx-xxxx`.
- Dilarang memakai foto/nama penerima manfaat (terutama anak). Tidak ada field untuk itu.
- Jangan membuat fitur yang tampak jalan tetapi tanpa logika. Pembayaran: transfer manual, tampilkan jelas "pembayaran dikonfirmasi admin".

## 3. Teknologi (BATASAN KERAS)
- **PHP 8+ polos** (gaya prosedural), **MySQL/MariaDB** lewat **PDO**, **HTML5**, **CSS satu file**, **JavaScript biasa** (vanilla).
- DILARANG: framework PHP (Laravel dll.), Composer, Node.js/npm, bundler, Tailwind, Bootstrap, React/Vue, jQuery, ORM, library pihak ketiga. Satu-satunya sumber luar: Google Fonts (link biasa).
- Tanpa routing khusus dan tanpa pretty URL: pakai file langsung dan query string (`produk-detail.php?slug=kopi-contoh`).
- Tanpa OOP kustom, namespace, autoloader. Fungsi biasa saja.

## 4. Bahasa
UI, nama file, tabel, kolom, variabel, fungsi, dan komentar: **Bahasa Indonesia** (supaya mudah dibaca, dijelaskan, dan dibuat flowchart-nya). Komentar singkat, menjelaskan "kenapa", bukan mengulang kode.

## 5. Struktur folder (TETAP, jangan menambah folder tanpa alasan)
```
pekarangan/
├── index.php              Beranda
├── tentang.php
├── produk.php             Daftar produk
├── produk-detail.php      Detail + form pesan
├── pesanan-sukses.php
├── program.php            Daftar program
├── program-detail.php
├── donasi.php             Form donasi
├── donasi-sukses.php
├── transparansi.php
├── kontak.php
├── admin/
│   ├── setup.php          Buat admin pertama (nonaktif otomatis setelah ada admin)
│   ├── login.php  logout.php  index.php (dashboard)
│   ├── produk.php  produk-form.php  produk-aksi.php
│   ├── program.php program-form.php program-aksi.php
│   ├── donasi.php  donasi-aksi.php
│   ├── pesanan.php pesanan-aksi.php
│   ├── laporan.php laporan-form.php laporan-aksi.php
│   └── pengaturan.php
├── includes/              config.sample.php, config.php (tidak di-commit), db.php,
│                          fungsi.php, auth.php, header.php, footer.php,
│                          admin-header.php, admin-footer.php
├── assets/ css/style.css, css/admin.css, js/app.js, js/admin.js, img/
├── uploads/               (foto & berkas unggahan)
├── database/              schema.sql, seed.sql
└── README.md
```

## 6. Aturan kode sederhana
- Satu halaman = satu file: logika di bagian atas, HTML di bawah. Tidak ada template engine.
- Pengulangan (header, footer, fungsi umum) ada di `includes/`. Tidak ada abstraksi lain.
- Fungsi pendek (maks sekitar 30 baris), nama jelas. Contoh helper di `fungsi.php`: `e()`, `rupiah()`, `url()`, `alihkan()`, `pesan_flash()`, `token_csrf()`, `cek_csrf()`, `buat_slug()`, `buat_kode()`, `ambil_pengaturan()`, `unggah_berkas()`, `paginasi()`.
- Aksi tulis (tambah/ubah/hapus) memakai POST ke file `*-aksi.php`, lalu redirect (pola Post-Redirect-Get) dengan pesan flash.
- JS hanya untuk interaksi kecil (menu mobile, tampil/sembunyi password, pratinjau gambar, konfirmasi hapus, tombol nominal donasi). Semua halaman tetap berfungsi tanpa JS bila memungkinkan.

## 7. Desain UI (baru, ramah dan bersih)
- Mobile-first, satu kolom di HP, grid 2-3 kolom di layar besar. Lebar konten maks 1080px.
- Warna (CSS variable di `style.css`): `--biru:#0049AB` (logo), `--biru-tua:#003580`, `--biru-muda:#EAF1FB`, `--oranye:#C2570C` (khusus donasi dan progress), `--oranye-muda:#FEF1E2`, `--teks:#1B2430`, `--teks-redup:#5B6675`, `--latar:#F6F8FB`, `--putih:#FFFFFF`, `--garis:#E2E8F0`, status `--hijau:#1E9E5A`, `--kuning:#E0A100`, `--merah:#D64545`. Pastikan kontras teks memenuhi WCAG AA.
- Huruf: **Poppins** (400/500/600/700) via Google Fonts, satu keluarga saja.
- Bentuk: sudut membulat 14px, bayangan lembut, tombol tinggi min 48px, jarak lega, ikon berupa SVG inline sederhana atau emoji (tanpa library ikon).
- Tombol: utama = biru solid, sekunder = garis biru, donasi = oranye solid.
- Foto: bila belum ada, tampilkan kotak abu-biru bertuliskan "[Foto]" (class `.foto-kosong`) dengan rasio tetap.
- Animasi hanya: transisi hover/fokus dan animasi lebar progress bar. Hormati `prefers-reduced-motion`.
- Aksesibilitas: HTML semantik, urutan heading benar, label pada form, fokus terlihat, `alt` pada gambar, pesan error jelas.

## 8. Cakupan fitur (batas)
**Termasuk:** Beranda, Tentang, Produk (daftar, detail, pesan 1 produk), Program (daftar, detail), Donasi, Transparansi, Kontak, serta Admin (login, dashboard, CRUD produk/program/laporan, kelola donasi dan pesanan, pengaturan).
**DILARANG dibangun:** keranjang belanja, checkout banyak item, payment gateway, ongkir otomatis, registrasi publik, komentar/ulasan, multi-bahasa, galeri dokumentasi, halaman dampak/Desa Pekarangan, CMS atau page builder, notifikasi email.

## 9. Aturan bisnis
- Pesanan (`pesanan`) dan donasi (`donasi`) berada di **tabel terpisah** dan dilaporkan **terpisah**; tidak pernah dijumlahkan menjadi satu angka.
- Harga selalu diambil dari database, bukan dari form.
- Progres program = jumlah donasi berstatus `berhasil` saja (dihitung saat ditampilkan, tidak disimpan).
- Stok berkurang tepat sekali saat pesanan menjadi `dibayar`; kembali saat dibatalkan setelah `dibayar`. Gunakan transaksi database.
- Kode: pesanan `ORD-YYYYMMDD-XXXX`, donasi `DON-YYYYMMDD-XXXX` (XXXX = 4 karakter acak huruf besar/angka, unik).
- Status pesanan: menunggu, dibayar, diproses, dikirim, selesai, batal. Donasi: menunggu, berhasil, gagal. Hanya transisi yang masuk akal.
- Produk yang pernah dipesan dan program yang punya donasi tidak boleh dihapus (arsipkan/tutup). Pesanan dan donasi tidak dihapus.
- Alokasi dana (mis. operasional vs sosial) disimpan di `pengaturan`, bukan angka tetap di kode.

## 10. Keamanan (wajib)
- Semua query memakai **prepared statement PDO**. Dilarang menyusun SQL dari input dengan penggabungan string.
- Semua output ke HTML melewati `e()` (htmlspecialchars, UTF-8).
- Password: `password_hash()` / `password_verify()`. Tidak ada password bawaan di repo.
- Semua form POST memakai **token CSRF**. Semua halaman admin memanggil `wajib_login()` di baris paling atas, termasuk file `*-aksi.php`.
- Sesi: `session_regenerate_id(true)` saat login, cookie `HttpOnly` dan `SameSite=Lax`, kedaluwarsa wajar (mis. 2 jam tidak aktif).
- Login: pesan error umum "Email atau password salah."; batasi percobaan (5 gagal -> tunggu 60 detik).
- Unggah berkas: validasi ukuran, ekstensi, dan tipe MIME (`finfo`), nama file acak, simpan di `uploads/`; folder itu tidak boleh mengeksekusi PHP (`.htaccess`).
- Form publik (pesan, donasi): validasi server, batas panjang, field jebakan (honeypot) sederhana.
- `includes/config.php` tidak di-commit (`.gitignore`). `includes/` dan `database/` tidak boleh diakses langsung dari browser (`.htaccess` Require all denied).
- `display_errors` mati kecuali `APP_ENV = 'lokal'`.
- Validasi dan batasi `?next=`/redirect agar hanya ke halaman internal.

## 11. Cara bekerja
- **Satu prompt per sesi.** Jangan mengerjakan tahap berikutnya.
- Sebelum coding: lihat isi folder, tulis rencana singkat (maks 10 baris).
- Di akhir: jalankan `php -l` pada semua file PHP jika PHP tersedia; jika tidak tersedia atau MySQL tidak bisa dijalankan, **katakan terus terang** bahwa pengujian itu belum dilakukan dan sebutkan apa yang perlu saya cek manual. Jangan mengklaim sudah diuji bila belum.
- Beri ringkasan: apa yang dibuat, apa yang perlu saya lakukan (import SQL, isi config), hal yang masih contoh, dan pertanyaan untuk mitra. Lalu **BERHENTI dan tunggu review**.
- Masukan mitra dicatat dulu di `FEEDBACK.md`, dikerjakan per batch. Keputusan yang disepakati ada di `DECISIONS.md` dan tidak boleh diubah tanpa perintah eksplisit.

## 12. Catatan sebelum go-live (bukan untuk prototype)
Konfirmasi ke mitra: izin pengumpulan donasi, kebijakan privasi dan syarat layanan (data pribadi pemesan/donatur), consent foto penerima manfaat, hosting (PHP + MySQL), HTTPS.
