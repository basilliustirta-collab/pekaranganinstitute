# PROMPT P4 - Audit, Keamanan, README Final

Baca `AGENTS.md`, `FEEDBACK.md`, `DECISIONS.md`. P1-P3 sudah selesai. Tahap ini **tidak menambah fitur**: hanya memeriksa, memperbaiki, dan merapikan. Perbaiki masalah yang ditemukan, jangan hanya melaporkannya.

## Tugas
### 1. Pemeriksaan kode
- Jalankan `php -l` pada semua file PHP (jika PHP tersedia; jika tidak, katakan dan lakukan pemeriksaan membaca kode).
- Cari dan perbaiki: query tanpa prepared statement, output tanpa `e()`, form POST tanpa token CSRF, file admin tanpa `wajib_login()` di baris pertama, variabel tidak terdefinisi, kode tak terpakai, komentar yang menyesatkan, fungsi yang terlalu panjang.
- Pastikan tidak ada library atau file di luar batasan teknologi (`AGENTS.md` bagian 3) dan tidak ada folder di luar struktur bagian 5.

### 2. Daftar uji keamanan (jalankan dan laporkan lulus/gagal)
1. Buka setiap URL di `admin/` tanpa login, termasuk `*-aksi.php` lewat GET dan POST -> harus ditolak/dialihkan.
2. POST ke aksi admin tanpa token CSRF atau dengan token salah -> ditolak.
3. Coba injeksi sederhana (`' OR 1=1 --`, `<script>alert(1)</script>`) di semua field form publik, pencarian, dan admin -> tidak ada error SQL, skrip tidak berjalan (teks tampil apa adanya).
4. Unggah file `.php` yang diganti nama `.jpg`, file besar, dan file bukan gambar -> ditolak.
5. Akses langsung `includes/config.php`, `database/schema.sql`, `uploads/*.php` lewat URL -> ditolak/tidak dieksekusi.
6. Login: 5 kali salah -> terkunci; `?next=` atau redirect ke situs luar -> diabaikan.
7. Ubah `harga` atau `status` lewat inspect element pada form publik -> diabaikan server.
8. Dengan `APP_ENV = 'produksi'`, error PHP/SQL tidak menampilkan detail ke pengguna.
9. Cookie sesi memiliki `HttpOnly` dan `SameSite`.

### 3. Uji menyeluruh (ujung ke ujung)
Pesan produk -> admin menandai dibayar -> stok berkurang -> transparansi mencerminkan penjualan. Donasi -> admin menandai berhasil -> progres program dan transparansi naik. Jadikan produk Draf -> hilang dari publik. Cek halaman 404 ramah untuk slug/kode salah. Cek semua tautan menu dan footer, dan semua halaman di 375px/768px/1280px, navigasi keyboard, dan kontras warna.

### 4. Rapikan dan dokumentasi
- `README.md` untuk pemula: apa itu proyek ini, kebutuhan (XAMPP), cara instal langkah demi langkah (letakkan di `htdocs`, start Apache dan MySQL, import `schema.sql` lalu `seed.sql`, salin `config.sample.php` menjadi `config.php`, buka `admin/setup.php`), struktur folder dengan satu kalimat per file, cara mengganti logo/teks/warna, dan cara mengganti data contoh dengan data asli.
- Bagian "Sebelum dipakai sungguhan": hapus `admin/setup.php`, `seed.sql` jangan diimport di produksi, `APP_ENV = 'produksi'`, HTTPS, backup database, serta daftar konfirmasi mitra (izin donasi, kebijakan privasi, consent foto).
- Bagian "Yang masih contoh/manual": pembayaran manual, data contoh, tanpa notifikasi.

## Definition of Done
- [ ] Semua butir uji keamanan di atas dijalankan dan hasilnya dilaporkan; yang gagal sudah diperbaiki
- [ ] `php -l` bersih (jika tersedia); tidak ada warning PHP
- [ ] README bisa diikuti pemula dari nol sampai halaman berjalan
- [ ] Tidak ada fitur baru yang ditambahkan

## Akhiri dengan
Laporan singkat: hasil uji, perbaikan yang dilakukan, yang tidak bisa diuji otomatis (dan harus saya cek manual), dan keputusan terbuka untuk mitra. **BERHENTI.**
