-- Data Contoh (Seed) Pekarangan Institute
-- Semua data contoh ditandai dengan is_contoh = 1 sesuai Aturan Emas AGENTS.md
USE pekarangan;

-- 1. Data Contoh Produk
INSERT INTO produk (id, nama, slug, kategori, harga, stok, deskripsi_singkat, deskripsi, penjual, foto, status, is_contoh, dibuat_pada) VALUES
(1, '[Nama Produk] 1', 'nama-produk-1', 'Bahan Pangan', 25000, 20, '[Deskripsi Singkat Produk] 1', '[Deskripsi Lengkap Produk] Produk contoh bahan pangan lokal berkualitas hasil panen kelompok mitra.', '[Kelompok Tani Contoh] A', NULL, 'tampil', 1, NOW()),
(2, '[Nama Produk] 2', 'nama-produk-2', 'Minuman Herbal', 35000, 15, '[Deskripsi Singkat Produk] 2', '[Deskripsi Lengkap Produk] Produk contoh minuman herbal rimpang alami tanpa bahan pengawet.', '[Kelompok Usaha Contoh] B', NULL, 'tampil', 1, NOW()),
(3, '[Nama Produk] 3', 'nama-produk-3', 'Hasil Kebun', 15000, 30, '[Deskripsi Singkat Produk] 3', '[Deskripsi Lengkap Produk] Produk contoh sayuran organik segar dipetik langsung dari kebun pekarangan.', '[Kebun Komunitas Contoh] C', NULL, 'tampil', 1, NOW()),
(4, '[Nama Produk] 4', 'nama-produk-4', 'Kerajinan Anyaman', 50000, 10, '[Deskripsi Singkat Produk] 4', '[Deskripsi Lengkap Produk] Produk contoh kerajinan anyaman serat alami karya pengrajin binaan.', '[Sentra Pengrajin Contoh] D', NULL, 'tampil', 1, NOW()),
(5, '[Nama Produk] 5', 'nama-produk-5', 'Bibit & Benih', 10000, 25, '[Deskripsi Singkat Produk] 5', '[Deskripsi Lengkap Produk] Produk contoh bibit sayur dan tanaman pekarangan siap tanam.', '[Kebun Pembibitan Contoh] E', NULL, 'tampil', 1, NOW()),
(6, '[Nama Produk] 6', 'nama-produk-6', 'Olahan Madu', 60000, 0, '[Deskripsi Singkat Produk] 6 (Draf)', '[Deskripsi Lengkap Produk] Produk contoh madu murni yang sedang dipersiapkan dan berstatus draf.', '[Peternak Madu Contoh] F', NULL, 'draf', 1, NOW())
ON DUPLICATE KEY UPDATE nama = VALUES(nama);

-- 2. Data Contoh Program
INSERT INTO program (id, judul, slug, ringkasan, cerita, target_dana, tenggat, lokasi, penerima_manfaat, foto_sampul, status, is_contoh, dibuat_pada) VALUES
(1, '[Judul Program] 1', 'judul-program-1', '[Ringkasan Program] 1 - Program pemberdayaan pekarangan produktif untuk ketahanan pangan keluarga.', '[Cerita Program] 1 - Program ini bertujuan mendampingi warga dalam mengoptimalkan lahan pekarangan rumah tangga menjadi sumber pangan mandiri bergizi seimbang dan bernilai ekonomi.', 10000000, DATE_ADD(CURDATE(), INTERVAL 60 DAY), '[Kabupaten/Kota Contoh] 1', '[Kelompok Warga Komunitas Contoh] 1', NULL, 'aktif', 1, NOW()),
(2, '[Judul Program] 2', 'judul-program-2', '[Ringkasan Program] 2 - Inisiatif penyediaan bibit dan pelatihan budidaya herbal pekarangan.', '[Cerita Program] 2 - Mendukung kemandirian kesehatan keluarga melalui penanaman tanaman obat keluarga (TOGA) serta pelatihan pengolahan pascapanen sederhana.', 25000000, DATE_ADD(CURDATE(), INTERVAL 120 DAY), '[Kabupaten/Kota Contoh] 2', '[Kelompok Penggerak Komunitas Contoh] 2', NULL, 'aktif', 1, NOW())
ON DUPLICATE KEY UPDATE judul = VALUES(judul);

-- 3. Data Contoh Donasi
INSERT INTO donasi (id, kode, program_id, nama_donatur, whatsapp, jumlah, anonim, pesan, status, is_contoh, dibuat_pada) VALUES
(1, 'DON-20260110-A1B2', 1, '[Nama Donatur] 1', '08xx-xxxx-xxxx', 500000, 0, 'Semoga program ini berkah dan bermanfaat bagi warga.', 'berhasil', 1, NOW()),
(2, 'DON-20260115-C3D4', 1, '[Nama Donatur] 2', '08xx-xxxx-xxxx', 1500000, 0, 'Semangat untuk para relawan dan komunitas!', 'berhasil', 1, NOW()),
(3, 'DON-20260120-E5F6', 1, '[Nama Donatur] 3', '08xx-xxxx-xxxx', 250000, 1, 'Donasi anonim dari hamba Allah.', 'menunggu', 1, NOW()),
(4, 'DON-20260122-G7H8', 2, '[Nama Donatur] 4', '08xx-xxxx-xxxx', 2000000, 0, 'Dukungan untuk pelestarian tanaman obat pekarangan.', 'berhasil', 1, NOW()),
(5, 'DON-20260125-J9K0', 2, '[Nama Donatur] 5', '08xx-xxxx-xxxx', 1000000, 0, 'Semoga sukses dan tercapai targetnya.', 'berhasil', 1, NOW()),
(6, 'DON-20260128-L1M2', 2, '[Nama Donatur] 6', '08xx-xxxx-xxxx', 500000, 0, 'Menunggu konfirmasi transfer bank.', 'menunggu', 1, NOW())
ON DUPLICATE KEY UPDATE kode = VALUES(kode);

-- 4. Data Contoh Pesanan
INSERT INTO pesanan (id, kode, produk_id, nama_produk, harga_satuan, jumlah, total, nama_pemesan, whatsapp, alamat, catatan, status, is_contoh, dibuat_pada) VALUES
(1, 'ORD-20260112-X1Y2', 1, '[Nama Produk] 1', 25000, 2, 50000, '[Nama Pemesan] 1', '08xx-xxxx-xxxx', '[Alamat Pengiriman Contoh] Jl. Merpati No. 5, Kota Contoh', 'Tolong dikemas rapi.', 'dibayar', 1, NOW()),
(2, 'ORD-20260118-Z3W4', 2, '[Nama Produk] 2', 35000, 1, 35000, '[Nama Pemesan] 2', '08xx-xxxx-xxxx', '[Alamat Pengiriman Contoh] Jl. Kenari No. 18, Kota Contoh', 'Pesanan contoh menunggu verifikasi pembayaran.', 'menunggu', 1, NOW())
ON DUPLICATE KEY UPDATE kode = VALUES(kode);

-- 5. Data Contoh Laporan
INSERT INTO laporan (id, judul, periode, ringkasan, berkas, status, is_contoh, dibuat_pada) VALUES
(1, '[Judul Laporan] Laporan Transparansi Q1 2026', 'Januari - Maret 2026', '[Ringkasan Laporan Contoh] Rekapitulasi penyaluran hasil penjualan produk dan donasi masyarakat untuk kegiatan pemberdayaan komunitas pekarangan.', 'laporan-contoh-q1-2026.pdf', 'tampil', 1, NOW())
ON DUPLICATE KEY UPDATE judul = VALUES(judul);

-- 6. Pengaturan Sistem
INSERT INTO pengaturan (kunci, nilai) VALUES
('nama_situs', 'Pekarangan Institute'),
('tagline', 'Berbelanja, Berbagi, Berdampak'),
('hero_judul', 'Berbelanja, Berbagi, Berdampak.'),
('hero_teks', 'Inisiatif kewirausahaan sosial yang menghubungkan produk hasil pekarangan dengan pemberdayaan dan program sosial kemasyarakatan.'),
('tentang_teks', 'Pekarangan Institute adalah inisiatif kewirausahaan sosial yang bergerak di bidang pemberdayaan pekarangan produktif dan penguatan ekonomi komunitas. Melalui pendekatan Berbelanja, Berbagi, Berdampak, kami mengalirkan sebagian hasil usaha untuk program sosial berkelanjutan.'),
('whatsapp', '08xx-xxxx-xxxx'),
('email', 'kontak@pekarangan.org'),
('alamat', '[Alamat Kantor/Sekretariat Contoh] Jl. Contoh Pekarangan No. 12, RT 01/RW 02, Desa Contoh, Kecamatan Contoh, Kabupaten Contoh'),
('info_pembayaran', '[Nama Bank] [Nomor Rekening] a.n. [Nama Rekening]'),
('donasi_minimum', '10000'),
('alokasi_operasional', '30'),
('alokasi_sosial', '70'),
('catatan_transparansi', 'Alokasi dana operasional (30%) dan dana sosial (70%) di atas adalah angka contoh untuk simulasi transparansi dana.')
ON DUPLICATE KEY UPDATE nilai = VALUES(nilai);
