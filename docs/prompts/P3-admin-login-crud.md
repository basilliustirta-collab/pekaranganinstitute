# PROMPT P3 - Login Admin dan CRUD

Baca `AGENTS.md`, `FEEDBACK.md`, `DECISIONS.md`. P1-P2 sudah selesai. Kerjakan **hanya** tahap ini. Pakai ulang helper dan CSS yang ada; tambahkan `assets/css/admin.css` dan `assets/js/admin.js` untuk admin.
Jika saya melampirkan gambar referensi UI, ikuti **pola tata letaknya saja** (bukan warna, teks, atau nama). Warna tetap memakai variabel di `style.css`.

## Desain admin (sederhana)
- Tampilan terang: sidebar kiri putih/biru-muda dengan menu, konten di kanan di atas latar `--latar`. Di mobile sidebar menjadi menu geser (tombol hamburger).
- Menu: Dashboard, Produk, Program, Donasi, Pesanan, Laporan, Pengaturan, Lihat Situs, Keluar. Tanpa menu lain.
- Tabel rapi (di mobile berubah jadi kartu bertumpuk), tombol aksi jelas berlabel teks ("Ubah", "Hapus"), badge status berwarna dengan teks, pesan sukses/gagal (flash) di atas halaman.
- Bahasa sehari-hari, tidak teknis.

## Tugas
### Login
- `admin/setup.php`: **hanya bisa dibuka jika tabel `admin` kosong**; membuat akun admin pertama (nama, email, password min 8 karakter + konfirmasi), lalu mengarahkan ke login. Jika sudah ada admin, tampilkan 404/"Tidak tersedia". Tampilkan pengingat untuk menghapus file ini setelah dipakai.
- `admin/login.php`: kartu putih di tengah dengan latar biru lembut; logo, judul "Masuk Admin", input Email dan Password (tombol "Tampilkan/Sembunyikan" lewat JS), tombol "Masuk", tautan "Kembali ke beranda". **Tanpa** daftar akun, login sosial, atau "lupa password" otomatis (cukup teks: "Lupa password? Hubungi pengelola sistem.").
  - Pesan gagal selalu umum: "Email atau password salah." Batasi 5 percobaan gagal, lalu tunggu 60 detik (pesan jelas).
  - Sukses: `session_regenerate_id(true)`, simpan id admin, alihkan ke dashboard. Jika sudah login, langsung ke dashboard.
  - Label terhubung ke input, `autocomplete` benar, fokus terlihat, tombol menampilkan status saat diproses.
- `admin/logout.php`: hapus sesi (lewat POST + CSRF, tautan "Keluar" berupa form kecil), alihkan ke login.
- Semua file admin (termasuk `*-aksi.php`) memanggil `wajib_login()` di baris pertama. Sesi berakhir setelah 2 jam tidak aktif dengan pesan "Sesi berakhir, silakan masuk lagi."

### Dashboard `admin/index.php`
Sapaan; 4 kartu ringkasan terpisah: **Penjualan** (pesanan dibayar ke atas), **Donasi** (berhasil), **Pesanan menunggu**, **Donasi menunggu**; 5 pesanan terbaru; 5 donasi terbaru; tombol cepat: Tambah Produk, Buat Program, Lihat Pesanan, Lihat Donasi. Komersial dan donasi tidak dijumlahkan. Angka dari data contoh diberi `lencana-contoh`.

### CRUD Produk (`produk.php`, `produk-form.php`, `produk-aksi.php`)
- **Daftar:** pencarian nama, filter status (Semua/Tampil/Draf/Arsip), paginasi 10; kolom foto kecil, nama, kategori, harga, stok, status, aksi (Ubah, menu Tampilkan/Jadikan Draf, Arsipkan, Hapus). Kosong: "Belum ada produk. Tambah produk pertama Anda."
- **Tambah/Ubah (satu form):** nama (wajib), slug otomatis (unik, bisa diubah), kategori (isian teks dengan saran dari kategori yang sudah ada), harga, stok, deskripsi singkat (maks 160), deskripsi lengkap, penjual (opsional), foto (JPG/PNG/WebP maks 2 MB, pratinjau JS), status (default Draf). Tombol Simpan dan Batal. Validasi server, pesan error di bawah field, isian tetap terisi saat error.
- **Hapus:** konfirmasi JS + POST + CSRF. Produk yang **pernah dipesan** tidak bisa dihapus; sistem menawarkan **Arsipkan**. Hapus produk juga menghapus file fotonya. Ganti foto menghapus foto lama.
- Penanda jelas "Tampil di publik" atau "Draf (belum tampil)".

### CRUD Program (`program.php`, `program-form.php`, `program-aksi.php`)
- Daftar dengan filter status (Draf/Aktif/Selesai), kolom: judul, target, terkumpul (hitung dari donasi `berhasil`), progres, tenggat, status, aksi.
- Form: judul, slug, ringkasan, cerita, **target dana**, **tenggat**, lokasi, penerima manfaat (teks umum), foto sampul, status. Terkumpul/persentase **hanya ditampilkan**, tidak bisa diisi.
- Peringatan tetap di form: "Jangan menulis nama atau mengunggah foto penerima manfaat (terutama anak-anak) tanpa persetujuan tertulis."
- Hapus: tidak boleh bila sudah punya donasi (tawarkan ubah status menjadi Selesai). Program Selesai/Draf tidak menerima donasi baru.

### Kelola Donasi (`donasi.php`, `donasi-aksi.php`)
- Daftar dengan filter status (Menunggu/Berhasil/Gagal), filter program, pencarian kode/nama, paginasi. Kolom: kode, donatur (anonim tampil "Anonim", nama asli di halaman detail admin), program, nominal, tanggal, status.
- Detail donasi (halaman atau panel): semua data, pesan, tombol WhatsApp.
- Aksi: **Tandai Berhasil** dan **Tandai Gagal** (konfirmasi). Mengubah dari `berhasil` kembali ke status lain memerlukan konfirmasi tambahan. **Tidak ada tombol hapus.** Progres program otomatis mengikuti.

### Kelola Pesanan (`pesanan.php`, `pesanan-aksi.php`)
- Daftar dengan filter status, pencarian kode/nama, paginasi. Detail: produk, jumlah, total, kontak (tombol WhatsApp), alamat, catatan.
- Ubah status hanya ke transisi valid: menunggu -> dibayar/batal; dibayar -> diproses/batal; diproses -> dikirim; dikirim -> selesai.
- **Stok:** menjadi `dibayar` mengurangi stok tepat sekali (tolak bila stok tidak cukup, jelaskan); batal setelah `dibayar` mengembalikan stok. Pakai transaksi database. **Tidak ada tombol hapus.**

### CRUD Laporan (`laporan.php`, `laporan-form.php`, `laporan-aksi.php`)
Judul, periode, ringkasan, berkas (PDF/JPG/PNG maks 5 MB), status Draf/Tampil. Hapus (konfirmasi) menghapus berkasnya.

### Pengaturan (`pengaturan.php`)
Satu form berkelompok: Identitas (nama situs, tagline), Beranda (judul dan teks hero), Tentang (teks), Kontak (WhatsApp, email, alamat), Pembayaran (info rekening/QRIS teks), Donasi (nominal minimum), Alokasi dana (operasional dan sosial: total harus 100) dan Catatan transparansi. Peringatan di dekat alokasi dan statistik: "Isi hanya angka yang sudah dikonfirmasi mitra. Bagian ini tampil di halaman publik." Validasi server. Perubahan langsung terlihat di halaman publik.

### Keamanan (wajib)
Sesuai `AGENTS.md` bagian 10: prepared statement, `e()`, CSRF di semua POST, `wajib_login()` di semua file admin, validasi unggahan (ukuran, ekstensi, MIME), nama file acak, redirect internal saja.

## Definition of Done
- [ ] `php -l` lolos untuk semua file (jika PHP tersedia); tidak ada warning PHP
- [ ] `setup.php` hanya jalan saat belum ada admin; setelah itu tidak tersedia
- [ ] Membuka `admin/*` tanpa login (termasuk `*-aksi.php` langsung lewat URL) selalu dialihkan ke login
- [ ] Login: berhasil, gagal (pesan umum), dan terkunci 60 detik setelah 5 kali salah
- [ ] Uji ujung ke ujung: tambah produk Tampil -> muncul di `produk.php`; jadikan Draf -> hilang dan detailnya 404; ubah harga -> berubah di publik; hapus (yang tidak pernah dipesan) -> hilang. Ulangi untuk program dan laporan.
- [ ] Donasi: buat dari publik (menunggu) -> tandai Berhasil -> progres publik naik; tandai Gagal -> tidak naik
- [ ] Pesanan: dibayar mengurangi stok sekali; batal setelah dibayar mengembalikan stok; transisi tidak valid ditolak
- [ ] Produk yang pernah dipesan dan program yang punya donasi tidak bisa dihapus
- [ ] Unggah file bukan gambar atau melebihi batas ditolak dengan pesan jelas
- [ ] Rapi di 375px (tabel menjadi kartu) dan 768px

## Akhiri dengan
Ringkasan + langkah yang harus saya lakukan (buka `admin/setup.php`) + hal yang perlu saya cek manual. **BERHENTI dan tunggu review.**
