# PROMPT P2 - Produk, Pesan, Program, Donasi, Transparansi

Baca `AGENTS.md`, `FEEDBACK.md`, `DECISIONS.md`. P1 sudah selesai. Kerjakan **hanya** tahap ini dan jangan mengubah struktur yang sudah ada tanpa alasan. Pakai ulang helper dan komponen CSS dari P1.

## Tugas
### Produk
- `produk.php`: grid kartu produk berstatus `tampil`. Pencarian nama, filter kategori (dari data produk), urutan (terbaru, harga naik/turun), paginasi 9 per halaman. Stok 0 tetap tampil dengan label "Stok habis". Kosong: "Belum ada produk."
- `produk-detail.php?slug=`: foto (atau `foto-kosong`), nama, kategori, harga, stok, deskripsi, penjual/UMKM bila ada, dan **form "Pesan Sekarang"**: nama, WhatsApp, alamat, jumlah (1 sampai stok), catatan. Produk tidak ditemukan/bukan `tampil`: halaman 404 ramah.
- Saat form dikirim (POST ke halaman yang sama atau file khusus):
  - Validasi server (wajib, panjang, nomor WhatsApp Indonesia, jumlah <= stok), cek CSRF dan honeypot, harga dari database.
  - Simpan ke `pesanan` (status `menunggu`, salinan nama produk dan harga, kode `ORD-YYYYMMDD-XXXX`). **Stok belum dikurangi.**
  - Redirect ke `pesanan-sukses.php?kode=...`.
- `pesanan-sukses.php`: tampilkan kode, ringkasan (nama produk, jumlah, total), `info_pembayaran` dari pengaturan, banner "Pembayaran diverifikasi manual oleh admin", dan tombol "Konfirmasi via WhatsApp" (`wa.me` dengan teks berisi kode pesanan). Kode tidak ditemukan: 404 ramah. Jangan menampilkan alamat/WhatsApp lengkap.

### Program dan donasi
- `program.php`: kartu program berstatus `aktif` dan `selesai` (selesai diberi lencana), berisi judul, ringkasan, target, terkumpul, persentase, tenggat, lokasi, progress bar. Persentase dibatasi tampilan maks 100% (angka asli tetap ditampilkan).
- `program-detail.php?slug=`: judul, cerita, target, terkumpul, persentase, tenggat, lokasi, penerima manfaat (teks umum), tombol "Donasi Sekarang" (oranye; berubah jadi keterangan bila program bukan `aktif`). Bahasa faktual, tidak berlebihan secara emosional. Tidak ada foto atau nama penerima manfaat.
- `donasi.php` (menerima `?program=slug`): satu halaman sederhana berisi:
  1. pilih program (hanya `aktif`)
  2. nominal: tombol cepat (25.000, 50.000, 100.000, 250.000) + isian bebas; minimum dari `pengaturan.donasi_minimum`
  3. nama dan WhatsApp
  4. centang "Sembunyikan nama saya (anonim)"
  5. pesan (opsional)
  6. tombol "Lanjut Donasi"
  - JS kecil: tombol nominal mengisi kolom nominal. Tanpa JS tetap bisa mengisi manual.
  - Validasi server, CSRF, honeypot. Simpan ke `donasi` (status `menunggu`, kode `DON-YYYYMMDD-XXXX`), redirect ke `donasi-sukses.php?kode=...`.
- `donasi-sukses.php`: kode referensi, program, nominal, `info_pembayaran`, banner "Donasi dihitung setelah dikonfirmasi admin", tombol "Konfirmasi via WhatsApp".
- Daftar donatur terbaru (opsional, di `program-detail.php`): hanya donasi `berhasil`; yang anonim ditampilkan "Anonim".

### Transparansi `transparansi.php`
- Dua ringkasan **terpisah dan berlabel jelas**: "Penjualan produk" (jumlah total dari pesanan berstatus dibayar/diproses/dikirim/selesai) dan "Donasi terkumpul" (donasi `berhasil`). Jangan dijumlahkan.
- Alokasi dana dari `pengaturan` ditampilkan sebagai batang horizontal CSS sederhana (tanpa library grafik).
- Donasi per program (tabel sederhana atau batang CSS).
- Daftar laporan berstatus `tampil` (judul, periode, ringkasan, tautan unduh). Kosong: teks sopan.
- Catatan `catatan_transparansi` + `lencana-contoh` selama angka berasal dari data contoh.

### Umum
- Semua halaman memakai `header.php`/`footer.php` dari P1, `<title>` dan deskripsi unik.
- Semua query prepared statement; semua output `e()`.
- Pesan sukses/gagal memakai flash message yang rapi.

## Definition of Done
- [ ] `php -l` lolos untuk semua file (jika PHP tersedia); tidak ada warning PHP di `APP_ENV = 'lokal'`
- [ ] Alur pesan: produk -> detail -> kirim form -> halaman sukses dengan kode `ORD-...`; baris baru muncul di tabel `pesanan` berstatus `menunggu`; stok belum berubah
- [ ] Alur donasi: program -> donasi -> halaman sukses `DON-...`; baris di `donasi` berstatus `menunggu`; progres program **tidak** berubah sampai status `berhasil`
- [ ] Form menolak: nama kosong, nominal di bawah minimum, jumlah melebihi stok, WhatsApp salah (diuji di server, bukan hanya browser)
- [ ] Donasi anonim tidak menampilkan nama di halaman publik
- [ ] Transparansi memisahkan penjualan dan donasi; semua angka contoh berlabel
- [ ] Rapi di 375px dan 768px; semua tombol utama nyaman disentuh

## Akhiri dengan
Ringkasan + hal yang perlu saya cek manual + pertanyaan untuk mitra (nominal minimum, info rekening, kategori produk aktif). **BERHENTI dan tunggu review.**
