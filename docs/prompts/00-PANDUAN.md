# Paket Prompt Pekarangan Institute - VERSI SEDERHANA (HTML, PHP, JS + MySQL)

Project BARU, terpisah dari versi Next.js. Tanpa Node.js, tanpa npm, tanpa framework.

## Yang dibutuhkan (sekali saja)
1. **XAMPP** (Apache + MySQL + PHP + phpMyAdmin) dari apachefriends.org. Alternatif: Laragon.
2. **Antigravity** (sudah ada).
3. Browser (Chrome/Edge).

## Langkah persiapan
1. Install XAMPP, lalu buka **XAMPP Control Panel**.
2. Buat folder project di dalam htdocs: `C:\xampp\htdocs\pekarangan` (Mac/Linux: folder `htdocs`/`www` di XAMPP).
3. Salin file paket ini ke folder itu:
   ```
   pekarangan/
   ├── AGENTS.md
   ├── FEEDBACK.md
   ├── DECISIONS.md
   ├── docs/prompts/   (P1, P2, P3, P4 + panduan ini)
   └── assets/img/logo.png   (logo Pekarangan Institute; buat foldernya)
   ```
4. Buka folder `pekarangan` di Antigravity (**File > Open Folder**, pilih **Trust**).
5. Cek agent membaca aturan: tanya "Sebutkan batasan teknologi dan 3 aturan emas proyek ini." Jawaban benar: hanya PHP polos + MySQL + JS biasa; dilarang framework/npm; dilarang mengarang data.
6. Supaya agent bisa mengetes sintaks PHP, tambahkan PHP XAMPP ke PATH (Windows: `C:\xampp\php`), lalu cek di terminal: `php -v`. Jika tidak mau, tidak apa-apa: agent harus jujur bahwa ia tidak bisa menjalankan tes, dan kamu mengetes manual di browser.
7. `git init`, `git add .`, `git commit -m "chore: awal"`.

## Urutan prompt (satu prompt = satu percakapan baru)
| Prompt | Isi |
|---|---|
| P1 | Struktur folder, database, desain, Beranda/Tentang/Kontak |
| P2 | Produk, pesan produk, program, donasi, transparansi |
| P3 | Login admin + CRUD (produk, program, laporan) + kelola donasi/pesanan + pengaturan |
| P4 | Audit keamanan, uji menyeluruh, README final |

Pesan untuk agent (ganti nama file):
```
Baca AGENTS.md, FEEDBACK.md, DECISIONS.md, lalu kerjakan persis sesuai
docs/prompts/P1-fondasi-database-desain.md.
Berhenti di akhir dan tunggu review saya.
```

## Setelah P1 selesai: menjalankan
1. XAMPP Control Panel: **Start** Apache dan MySQL.
2. Buka `http://localhost/phpmyadmin`, tab **Import**, pilih `database/schema.sql`, lalu import. Ulangi untuk `database/seed.sql`.
3. Salin `includes/config.sample.php` menjadi `includes/config.php`, isi sesuai XAMPP (user `root`, password kosong, database `pekarangan`).
4. Buka `http://localhost/pekarangan/`.
5. Setelah P3: buka `http://localhost/pekarangan/admin/setup.php` untuk membuat akun admin pertama.

## Aturan main
- Cek tiap tahap di browser, lalu `git commit`, baru lanjut.
- Jangan lanjut ke prompt berikutnya sebelum yang sekarang diuji.
- Masukan dari mitra: catat di `FEEDBACK.md`, lalu minta agent mengerjakan per batch.
