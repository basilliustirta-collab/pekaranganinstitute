# Pekarangan Institute - Prototype Website

Website purwarupa (prototype) resmi **Pekarangan Institute**, inisiatif kewirausahaan sosial berbasis komunitas dengan konsep **Berbelanja, Berbagi, Berdampak**. Website ini memadukan profil inisiatif, katalog produk hasil pekarangan, penggalangan donasi program sosial, transparansi pertanggungjawaban dana, serta panel administrasi sederhana.

Purwarupa ini dirancang khusus untuk didiskusikan dengan mitra penggerak dan komunitas. Dibangun dengan memprioritaskan prinsip **kesederhanaan**, kemudahan pengelolaan bagi admin, dan keterbacaan kode tanpa ketergantungan framework yang rumit.

---

## 1. Kebutuhan Sistem

- **PHP 8.0 atau lebih baru** (ekstensi `pdo_mysql`, `fileinfo`, `session`, `mbstring` aktif).
- **MySQL 5.7+ / MariaDB 10.3+**.
- **Web Server Apache** (misalnya via paket **XAMPP**, **Laragon**, atau **MAMP**) dengan modul `mod_rewrite` dan dukungan file `.htaccess`.
- Peramban web modern (Google Chrome, Mozilla Firefox, Microsoft Edge, Safari).
- Koneksi internet ringan untuk memuat huruf **Poppins** dari Google Fonts.

---

## 2. Panduan Instalasi Langkah Demi Langkah (Pemula)

Ikuti langkah-langkah mudah berikut dari awal sampai website dapat diakses di komputer lokal Anda:

### Langkah 1: Tempatkan Folder Proyek
1. Jika menggunakan **XAMPP**: Pindahkan atau ekstrak folder proyek ini ke dalam folder `C:\xampp\htdocs\pekarangan-v2`.
2. Jika menggunakan **Laragon**: Tempatkan folder proyek di `C:\laragon\www\pekarangan-v2`.

### Langkah 2: Jalankan Layanan Server Lokal
1. Buka aplikasi **XAMPP Control Panel** (atau Laragon).
2. Klik tombol **Start** pada modul **Apache** dan modul **MySQL** hingga indikator berwarna hijau.

### Langkah 3: Impor Basis Data (Database)
1. Buka peramban (browser) dan akses alamat: [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
2. Buat database baru bernama `pekarangan` dengan collation `utf8mb4_unicode_ci` (opsional jika menggunakan tombol impor langsung).
3. Klik tab **Import** (Impor):
   - Pilih berkas `database/schema.sql`, lalu klik tombol **Go** / **Kirim** untuk membuat struktur tabel.
   - Setelah selesai, klik tab **Import** kembali, pilih berkas `database/seed.sql`, lalu klik **Go** / **Kirim** untuk mengisi data contoh awal.

### Langkah 4: Buat Berkas Konfigurasi (`config.php`)
1. Buka folder `includes/` di dalam proyek.
2. Salin berkas `config.sample.php`, lalu ubah namanya menjadi `config.php`.
3. Buka berkas `config.php` dengan teks editor (Notepad, VS Code, dll.), lalu sesuaikan jika pengaturan MySQL Anda berbeda:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAMA', 'pekarangan');
   define('DB_USER', 'root');
   define('DB_PASS', ''); // default XAMPP/Laragon biasanya kosong

   // Sesuaikan nama folder web Anda
   define('BASE_URL', '/pekarangan-v2');
   define('APP_ENV', 'lokal');
   ```

### Langkah 5: Buat Akun Administrator Pertama
1. Buka peramban dan akses alamat:
   [http://localhost/pekarangan-v2/admin/setup.php](http://localhost/pekarangan-v2/admin/setup.php)
2. Isi formulir **Setup Admin Pertama**: masukkan Nama Lengkap, Alamat Email, dan Kata Sandi (minimal 8 karakter).
3. Klik tombol **Buat Akun Administrator**.
4. Sistem akan otomatis mengarahkan Anda ke halaman login admin. Halaman setup akan otomatis terkunci demi keamanan setelah akun admin pertama dibuat.

### Langkah 6: Masuk ke Panel Admin
1. Akses halaman login admin: [http://localhost/pekarangan-v2/admin/login.php](http://localhost/pekarangan-v2/admin/login.php).
2. Masukkan email dan kata sandi yang telah Anda buat pada Langkah 5.
3. Anda kini berada di Dashboard Admin dan siap mengelola data website.

---

## 3. Struktur Berkas & Folder

Setiap berkas memiliki tugas yang jelas dan mandiri:

```text
pekarangan/
├── .htaccess                   Proteksi direktori root, pemblokiran dotfiles, dan pencegahan directory browsing.
├── index.php                   Halaman beranda publik (hero profil, langkah kerja, produk & program terbaru, ajakan).
├── tentang.php                 Profil inisiatif, tiga pilar nilai, dan simulasi alokasi dana operasional/sosial.
├── produk.php                  Katalog produk publik dilengkapi pencarian nama, filter kategori, urutan harga, dan paginasi.
├── produk-detail.php           Halaman rincian 1 produk dan formulir pemesanan langsung tanpa keranjang.
├── pesanan-sukses.php          Halaman sukses pemesanan berisi rincian pesanan, rekening transfer, dan tautan WhatsApp.
├── program.php                 Daftar program pemberdayaan sosial aktif dan selesai beserta progres dana real-time.
├── program-detail.php          Cerita lengkap program, capaian dana terkumpul, dan daftar donatur terverifikasi.
├── donasi.php                  Formulir donasi untuk program sosial aktif dengan opsi nominal cepat dan anonimitas.
├── donasi-sukses.php           Instruksi transfer rekening resmi dan tombol konfirmasi donasi via WhatsApp.
├── transparansi.php            Akuntabilitas dana terpisah (penjualan vs donasi), diagram alokasi, dan laporan berkala.
├── kontak.php                  Halaman kontak resmi sekretariat, nomor WhatsApp, alamat kantor, dan surel.
│
├── admin/
│   ├── setup.php               Formulir inisialisasi akun admin pertama (otomatis nonaktif setelah ada admin).
│   ├── login.php               Halaman masuk admin dengan proteksi brute force (5x gagal kunci 60 detik) & anti-redirect.
│   ├── logout.php              Proses keluar admin yang aman melalui metode POST dan validasi token CSRF.
│   ├── index.php               Dashboard admin menampilkan ringkasan penjualan, donasi, antrean transaksi, dan aksi cepat.
│   ├── produk.php              Manajemen tabel produk (pencarian, filter status, tombol ubah status dan hapus).
│   ├── produk-form.php         Formulir tambah dan edit produk beserta unggah foto dan validasi input.
│   ├── produk-aksi.php         Pemrosesan backend simpan, perbarui status, dan hapus produk (terproteksi relasi pesanan).
│   ├── program.php             Manajemen tabel program sosial kemasyarakatan.
│   ├── program-form.php        Formulir tambah dan edit program sosial beserta target dana dan foto sampul.
│   ├── program-aksi.php        Pemrosesan backend program sosial (terproteksi relasi catatan donasi).
│   ├── donasi.php              Daftar seluruh transaksi donasi masyarakat dengan filter status dan informasi program.
│   ├── donasi-aksi.php         Pemrosesan perubahan status donasi (menunggu, berhasil, gagal) tanpa opsi hapus data.
│   ├── pesanan.php             Daftar pesanan produk dari pembeli dilengkapi filter status dan riwayat item.
│   ├── pesanan-aksi.php        State machine status pesanan dan pemotongan/pengembalian stok otomatis via transaksi PDO.
│   ├── laporan.php             Daftar publikasi dokumen laporan transparansi berkala.
│   ├── laporan-form.php        Formulir unggah berkas PDF/laporan pertanggungjawaban kegiatan.
│   ├── laporan-aksi.php        Pemrosesan simpan dan hapus berkas laporan fisik dari server.
│   └── pengaturan.php          Kelola identitas situs, rekening bank, alokasi dana, kontak, dan catatan transparansi.
│
├── includes/
│   ├── .htaccess               Blokir total seluruh akses peramban langsung ke folder konfigurasi dan logika internal.
│   ├── config.sample.php       Templat konfigurasi basis data dan konstanta lingkungan aplikasi.
│   ├── config.php              Konfigurasi aktif (rahasia, tidak di-commit ke repositori git).
│   ├── db.php                  Koneksi tunggal (singleton) PDO MySQL dengan opsi keamanan dan penanganan error.
│   ├── auth.php                Manajemen sesi aman (cookie HttpOnly, SameSite=Lax, masa aktif 2 jam) dan fungsi wajib_login().
│   ├── fungsi.php              Pustaka fungsi helper (e(), rupiah(), url(), alihkan(), token_csrf(), cek_csrf(), unggah_berkas(), dll).
│   ├── header.php              Kepala HTML publik semantik, Google Fonts Poppins, metadata Open Graph, dan navigasi utama.
│   ├── footer.php              Kaki halaman publik semantik, navigasi sekunder, kontak, dan penanda data contoh.
│   ├── admin-header.php        Kepala panel admin, pengecekan otentikasi wajib, sidebar menu responsif, dan pesan kilas.
│   └── admin-footer.php        Kaki halaman panel admin dan penutup layout konten.
│
├── assets/
│   ├── css/
│   │   ├── style.css           Lembar gaya utama seluruh halaman publik (variabel warna, tipografi, kartu, form, aksesibilitas).
│   │   └── admin.css           Lembar gaya khusus panel admin (sidebar, topbar, kartu ringkasan, tabel data, badge status).
│   ├── js/
│   │   ├── app.js              Skrip interaksi publik (toggle menu navigasi mobile, tombol nominal cepat donasi).
│   │   └── admin.js            Skrip panel admin (toggle sidebar mobile, toggle lihat kata sandi, konfirmasi hapus data).
│   └── img/
│       └── logo.png            Berkas logo resmi instansi (bila tidak ada, sistem otomatis menggunakan simbol teks aman).
│
├── database/
│   ├── .htaccess               Blokir total seluruh akses langsung ke berkas SQL dari browser.
│   ├── schema.sql              Skema tabel database MySQL dengan relasi kunci asing (Foreign Keys) dan indeks performa.
│   └── seed.sql                Data awalan contoh yang seluruhnya bertanda is_contoh = 1.
│
└── uploads/
    ├── .htaccess               Pencegah eksekusi script berbahaya (PHP engine off) dan penonaktifan directory browsing.
    └── .gitkeep                Penjaga keberadaan direktori penyimpanan berkas di sistem git.
```

---

## 4. Cara Kustomisasi Website

### A. Mengganti Logo
1. Letakkan gambar logo organisasi Anda di dalam folder: `assets/img/logo.png`.
2. Format yang disarankan adalah PNG dengan latar transparan (tinggi proporsional 40-50 piksel).
3. Jika berkas `logo.png` belum ada, sistem secara otomatis menampilkan simbol ramah lingkungan `&#9851;` diikuti Nama Situs berupa teks.

### B. Mengganti Nama, Kontak, Rekening, dan Teks Beranda
Seluruh konten utama dapat diganti langsung tanpa menyentuh kode:
1. Masuk ke **Panel Admin** -> buka menu **Pengaturan**.
2. Anda dapat mengubah:
   - **Identitas**: Nama Situs dan Tagline.
   - **Teks Beranda & Profil**: Judul Hero, Teks Hero, dan Deskripsi Halaman Tentang.
   - **Kontak Resmi**: Nomor WhatsApp pengelola, Alamat Email, dan Alamat Kantor Sekretariat.
   - **Rekening Pembayaran**: Rincian nama bank, nomor rekening, dan nama pemilik rekening untuk instruksi transfer pemesanan produk dan donasi.
   - **Transparansi**: Batas donasi minimum serta persentase Alokasi Dana Sosial dan Operasional (total harus 100%).
3. Klik tombol **Simpan Semua Pengaturan**. Perubahan akan langsung tampil di halaman publik.

### C. Mengganti Warna Tema
Warna website diatur terpusat melalui variabel CSS di berkas `assets/css/style.css` pada baris paling atas:
```css
:root {
  --biru: #0049AB;          /* Warna primer (identitas utama) */
  --biru-tua: #003580;      /* Variasi biru pekat untuk teks judul */
  --biru-muda: #EAF1FB;     /* Latar belakang elemen aksen biru */
  --oranye: #C2570C;        /* Warna khusus donasi, tombol aksi, dan progres */
  --oranye-muda: #FEF1E2;   /* Latar lencana dan kartu donasi */
  --teks: #1B2430;          /* Warna teks utama (memenuhi WCAG AA) */
  --latar: #F6F8FB;         /* Warna latar halaman */
  --putih: #FFFFFF;         /* Warna kartu dan kontainer */
}
```

### D. Mengganti Data Contoh Menjadi Data Asli
1. Masuk ke **Panel Admin**.
2. **Katalog Produk**: Buka menu **Produk** -> klik **+ Tambah Produk** untuk memasukkan produk riil binaan Anda. Untuk produk contoh bawaan, Anda dapat mengubah statusnya menjadi **Arsip** atau menghapusnya jika belum memiliki riwayat pesanan.
3. **Program Sosial**: Buka menu **Program** -> klik **+ Tambah Program** untuk menambahkan inisiatif nyata penggalangan program.
4. **Laporan Transparansi**: Buka menu **Laporan** -> unggah laporan pertanggungjawaban kegiatan resmi dalam format PDF.

---

## 5. Yang Masih Berupa Contoh & Manual (Status Prototype)

Karena website ini berstatus **purwarupa awal (prototype)** untuk didiskusikan bersama mitra:
1. **Verifikasi Pembayaran Manual**: Sistem tidak menggunakan payment gateway otomatis pihak ketiga. Pembeli dan donatur mentransfer dana secara manual ke rekening yayasan/komunitas, lalu mengonfirmasi bukti transfer melalui tautan pesan WhatsApp langsung ke admin.
2. **Pengurangan Stok**: Stok produk baru dipotong otomatis saat admin memverifikasi pembayaran dan mengubah status pesanan menjadi `dibayar` di panel admin. Jika pesanan dibatalkan setelah dibayar, stok otomatis dikembalikan ke inventaris produk.
3. **Penghitungan Donasi Transparansi**: Progres dana pada halaman program dan laporan transparansi hanya menghitung transaksi donasi yang telah diverifikasi berstatus `berhasil` oleh admin.
4. **Tanpa Notifikasi Otomatis**: Tidak ada pengiriman email/SMS otomatis (menghindari ketergantungan SMTP luar pada tahap purwarupa).
5. **Data Contoh Awal**: Semua item produk, program, dan transaksi bawaan ditandai kolom `is_contoh = 1` di database dan memiliki label visual **DATA CONTOH** di antarmuka publik agar tidak disalahpahami sebagai klaim fakta publik.

---

## 6. Checklist Wajib Sebelum Dipakai Sungguhan (Pra-Produksi)

Sebelum website ini dipublikasikan secara komersial ke publik di server produksi:

- [ ] **Hapus Berkas Setup**: Hapus berkas `admin/setup.php` dari server produksi agar tidak dapat diakses kembali oleh siapa pun.
- [ ] **Jangan Mengimpor `seed.sql`**: Pada server produksi, hanya impor berkas `database/schema.sql` (tabel kosong). Jangan mengimpor `database/seed.sql` agar data contoh tidak bercampur dengan transaksi riil.
- [ ] **Ubah Lingkungan ke Produksi**: Pada berkas `includes/config.php`, pastikan nilai `APP_ENV` telah diubah menjadi `'produksi'`:
  ```php
  define('APP_ENV', 'produksi');
  ```
  Hal ini wajib dilakukan agar rincian pesan error PHP dan struktur basis data tidak bocor ke publik.
- [ ] **Gunakan Sambungan Aman HTTPS**: Wajib memasang sertifikat SSL (HTTPS) agar data pribadi pemesan, formulir donasi, dan sesi login admin terenkripsi selama transmisi data di internet.
- [ ] **Pencadangan (Backup) Terjadwal**: Siapkan jadwal backup rutin basis data MySQL dan folder `uploads/`.
- [ ] **Konfirmasi Legalitas & Kebijakan Mitra**:
  - Konfirmasi izin resmi pengumpulan sumbangan/donasi publik dari instansi berwenang (Kementerian Sosial / Dinas Sosial).
  - Susun dan cantumkan dokumen Kebijakan Privasi (*Privacy Policy*) serta Syarat dan Ketentuan layanan terkait pengumpulan data pribadi (nama, nomor telepon WhatsApp, dan alamat pengiriman pemesan/donatur).
  - Pastikan mematuhi etika perlindungan privasi penerima manfaat sosial: tidak menampilkan nama perorangan atau foto anak penerima manfaat tanpa persetujuan tertulis (*informed consent*).
