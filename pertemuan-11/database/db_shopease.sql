-- =====================================================================
-- ShopEase – db_shopease (Pertemuan 11)
-- Tabel: users, categories, products (struktur mengikuti HOL Praktikum 2
-- dan Studi Kasus bagian 5.2).
--
-- Jalankan lewat phpMyAdmin (tab Import) atau:
--   mysql -u root -p < database/db_shopease.sql
-- File ini aman di-import ulang (tabel lama dihapus lalu dibuat lagi).
--
-- Akun demo (password di-hash dengan password_hash()):
--   admin@shopease.test     / admin12345     (admin)
--   operator@shopease.test  / operator12345  (operator)
--   customer@shopease.test  / customer12345  (customer)
-- =====================================================================

CREATE DATABASE IF NOT EXISTS db_shopease
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE db_shopease;

-- FOREIGN_KEY_CHECKS dimatikan sementara agar file ini tetap bisa di-import ulang
-- walaupun nanti sudah ada tabel lain (orders, carts, ...) yang merujuk ke tabel ini.
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- users (HOL Praktikum 2, Langkah 1)
-- ---------------------------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,          -- hasil password_hash(), bukan teks asli
    role ENUM('admin','customer','operator') DEFAULT 'customer',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- categories (HOL Praktikum 2, Langkah 2)
-- ---------------------------------------------------------------------
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) UNIQUE,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- products (HOL Praktikum 2 Langkah 3 + Studi Kasus 5.2)
-- Kolom "sold" sementara dipakai untuk section Produk terlaris; nanti
-- (Praktikum 9) jumlah terjual dihitung dari tabel order_items.
-- ---------------------------------------------------------------------
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(180) UNIQUE,
    description TEXT,
    price DECIMAL(12,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    image VARCHAR(255),
    status ENUM('active','inactive') DEFAULT 'active',
    sold INT NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_products_status (status),
    INDEX idx_products_name (name),
    FOREIGN KEY (category_id) REFERENCES categories(id)
) ENGINE=InnoDB;

-- =====================================================================
-- DATA AWAL
-- =====================================================================

INSERT INTO users (name, email, password, role) VALUES
('Admin ShopEase',  'admin@shopease.test',    '$2y$10$XX/X5QMP/anfyja1u1B/WOHq4UgSVO5MAKaXIO9Hi/9NmU/Yts0Be', 'admin'),
('Operator Gudang', 'operator@shopease.test', '$2y$10$s0r10bzqm5EQ9hXTLsqWjuTUWNTR.jNrpXNAUwUZeHsQS8z0j/fWS', 'operator'),
('Customer Contoh', 'customer@shopease.test', '$2y$10$sRP7L6oXzb6HupfRtDfl3uECLB5znjlkNwXQ6k06frr9mtH49DWaK', 'customer');

INSERT INTO categories (id, name, slug, description) VALUES
(1, 'Elektronik',   'elektronik',   'Perangkat audio, power bank, dan gawai pendukung.'),
(2, 'Fashion',      'fashion',      'Pakaian dan alas kaki sehari-hari.'),
(3, 'Aksesoris',    'aksesoris',    'Jam tangan, tas, dan pelengkap gaya.'),
(4, 'Rumah Tangga', 'rumah-tangga', 'Perlengkapan dapur dan penyimpanan.'),
(5, 'Kecantikan',   'kecantikan',   'Perawatan kulit dan wajah.'),
(6, 'Olahraga',     'olahraga',     'Perlengkapan latihan dan aktivitas luar ruang.');

-- 16 produk (15 aktif + 1 nonaktif). Gambar ada di public/uploads/products/.
INSERT INTO products (id, category_id, name, slug, description, price, stock, image, status, sold, created_at) VALUES
( 1, 1, 'Wireless Headphone',          'wireless-headphone',          'Headphone Bluetooth over-ear dengan bantalan empuk. Baterai tahan hingga 30 jam dan bisa dilipat untuk dibawa bepergian.', 299000, 20, 'wireless-headphone.jpg',   'active',   1, '2026-10-26 10:00:00'),
( 2, 2, 'Sneakers Casual',             'sneakers-casual',             'Sepatu kasual berbahan kulit sintetis dengan sol karet anti-slip. Nyaman dipakai seharian.',                              350000, 15, 'sneakers-casual.jpg',      'active',   0, '2026-10-27 10:00:00'),
( 3, 3, 'Smart Watch',                 'smart-watch',                 'Jam tangan pintar dengan notifikasi ponsel, penghitung langkah, dan pemantau tidur. Tali silikon mudah diganti.',        450000, 10, 'smart-watch.jpg',          'active',   0, '2026-10-28 10:00:00'),
( 4, 4, 'Tumbler Stainless 500 ml',    'tumbler-stainless-500ml',     'Botol stainless dua lapis yang menjaga minuman tetap panas atau dingin hingga 12 jam. Tutup ulir anti-bocor.',           89000, 57, 'tumbler-stainless.jpg',    'active',   6, '2026-10-30 10:00:00'),
( 5, 2, 'Kaos Katun Basic',            'kaos-katun-basic',            'Kaos katun combed 30s yang adem dan tidak mudah melar. Tersedia ukuran S sampai XL.',                                     79000, 60, 'kaos-katun-basic.jpg',     'active',   3, '2026-11-01 10:00:00'),
( 6, 5, 'Sunscreen SPF 50 PA++++',     'sunscreen-spf-50',            'Tabir surya bertekstur ringan untuk pemakaian harian. Melindungi dari sinar UVA dan UVB.',                                72000,  3, 'sunscreen-spf-50.jpg',     'active',   2, '2026-11-03 10:00:00'),
( 7, 1, 'Power Bank 10.000 mAh',       'power-bank-10000mah',         'Power bank tipis dengan pengisian cepat 20 W dan dua port USB. Cukup untuk dua kali isi penuh ponsel.',                  219000, 31, 'power-bank.jpg',           'active',   1, '2026-11-05 10:00:00'),
( 8, 6, 'Matras Yoga 6 mm',            'matras-yoga-6mm',             'Matras TPE setebal 6 mm dengan permukaan anti-slip. Ringan dan mudah digulung.',                                         159000,  0, 'matras-yoga.jpg',          'active',   0, '2026-11-07 10:00:00'),
( 9, 3, 'Tas Tote Kanvas',             'tas-tote-kanvas',             'Tas tote berbahan kanvas tebal dengan tali panjang. Muat laptop 14 inci dan perlengkapan harian.',                       129000, 22, 'tas-tote-kanvas.jpg',      'active',   0, '2026-11-10 10:00:00'),
(10, 2, 'Jaket Parasut (arsip)',       'jaket-parasut-arsip',         'Produk lama yang tidak dijual lagi.',                                                                                   249000,  5, NULL,                       'inactive', 0, '2026-11-11 10:00:00'),
(11, 4, 'Set Toples Kaca Kedap Udara', 'set-toples-kaca',             'Isi 6 toples kaca dengan tutup ulir kedap udara. Cocok untuk menyimpan bumbu, kopi, dan camilan.',                      135000, 18, 'set-toples-kaca.jpg',      'active',   0, '2026-11-13 10:00:00'),
(12, 5, 'Sabun Wajah Gentle',          'sabun-wajah-gentle',          'Pembersih wajah berbentuk gel dengan pH seimbang. Lembut untuk kulit sensitif.',                                          45000, 40, 'sabun-wajah-gentle.jpg',   'active',   0, '2026-11-15 10:00:00'),
(13, 6, 'Botol Minum Olahraga 1 L',    'botol-minum-olahraga-1l',     'Botol stainless dengan tutup bertali untuk dibawa berolahraga. Bebas BPA.',                                               65000, 35, 'botol-minum-olahraga.jpg', 'active',   0, '2026-11-18 10:00:00'),
(14, 1, 'Earbuds Nirkabel Pro',        'earbuds-nirkabel-pro',        'Earbuds dengan peredam bising aktif dan casing pengisi daya. Total pemakaian hingga 24 jam.',                           349000, 24, 'earbuds-nirkabel-pro.jpg', 'active',   0, '2026-11-20 10:00:00'),
(15, 3, 'Topi Baseball Polos',         'topi-baseball-polos',         'Topi model trucker dengan jaring di bagian belakang dan pengatur ukuran.',                                                59000,  4, 'topi-baseball-polos.jpg',  'active',   0, '2026-11-22 10:00:00'),
(16, 2, 'Kemeja Linen Lengan Panjang', 'kemeja-linen-lengan-panjang', 'Kemeja linen yang adem dengan potongan regular fit. Lengan bisa digulung.',                                              189000, 12, 'kemeja-linen.jpg',         'active',   0, '2026-11-24 10:00:00');
