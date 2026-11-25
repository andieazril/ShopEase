-- =====================================================================
-- ShopEase – database db_shopease
--
-- Jalankan lewat phpMyAdmin (tab Import) atau:
--   mysql -u root -p < database/db_shopease.sql
-- File ini aman di-import ulang (semua tabel dihapus lalu dibuat lagi).
--
-- Akun demo (password di-hash dengan password_hash()):
--   admin@shopease.test      / admin12345     (admin)
--   operator@shopease.test   / operator12345  (operator)
--   customer@shopease.test   / customer12345  (customer)
--   customer2@shopease.test  / customer12345  (customer)
-- Semua nama, alamat, dan nomor telepon adalah data contoh.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS db_shopease
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE db_shopease;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS shipments, payments, order_items, orders, addresses,
                     cart_items, carts, products, categories, users;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- users – akun admin, operator, dan customer
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
-- categories – kategori produk
-- ---------------------------------------------------------------------
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) UNIQUE,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- products – produk
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
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    INDEX idx_products_status (status),
    INDEX idx_products_name (name),
    CONSTRAINT chk_products_price CHECK (price >= 0),
    CONSTRAINT chk_products_stock CHECK (stock >= 0),
    FOREIGN KEY (category_id) REFERENCES categories(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- carts & cart_items – keranjang aktif milik customer
-- ---------------------------------------------------------------------
CREATE TABLE carts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,             -- satu keranjang aktif per customer
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cart_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    FOREIGN KEY (cart_id) REFERENCES carts(id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    UNIQUE KEY unique_cart_product (cart_id, product_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- addresses – alamat pengiriman customer
-- ---------------------------------------------------------------------
CREATE TABLE addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    recipient_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    province VARCHAR(100) NOT NULL,
    postal_code VARCHAR(10) NOT NULL,
    is_default TINYINT(1) DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- orders & order_items – pesanan
-- order_items menyimpan salinan nama dan harga produk saat transaksi,
-- jadi riwayat tidak berubah walaupun harga katalog diubah.
-- ---------------------------------------------------------------------
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    address_id INT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    shipping_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
    total DECIMAL(12,2) NOT NULL,
    order_status VARCHAR(30) DEFAULT 'pending_payment',
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    INDEX idx_orders_status (order_status),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (address_id) REFERENCES addresses(id)
) ENGINE=InnoDB;

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(150) NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    quantity INT NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- payments – pembayaran manual dengan bukti transfer
-- ---------------------------------------------------------------------
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL UNIQUE,
    method VARCHAR(50),
    amount DECIMAL(12,2) NOT NULL,
    proof_file VARCHAR(255),
    payment_status ENUM('unpaid','submitted','verified','rejected') DEFAULT 'unpaid',
    verified_by INT NULL,
    verified_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (verified_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- shipments – pengiriman
-- ---------------------------------------------------------------------
CREATE TABLE shipments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL UNIQUE,
    courier VARCHAR(100),
    tracking_number VARCHAR(100),
    shipment_status VARCHAR(30),
    shipped_at DATETIME NULL,
    delivered_at DATETIME NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id)
) ENGINE=InnoDB;

-- =====================================================================
-- DATA AWAL
-- =====================================================================

INSERT INTO users (id, name, email, password, role) VALUES
(1, 'Admin ShopEase',  'admin@shopease.test',     '$2y$10$XX/X5QMP/anfyja1u1B/WOHq4UgSVO5MAKaXIO9Hi/9NmU/Yts0Be', 'admin'),
(2, 'Operator Gudang', 'operator@shopease.test',  '$2y$10$s0r10bzqm5EQ9hXTLsqWjuTUWNTR.jNrpXNAUwUZeHsQS8z0j/fWS', 'operator'),
(3, 'Customer Contoh', 'customer@shopease.test',  '$2y$10$sRP7L6oXzb6HupfRtDfl3uECLB5znjlkNwXQ6k06frr9mtH49DWaK', 'customer'),
(4, 'Customer Kedua',  'customer2@shopease.test', '$2y$10$OOFZWyjbI5V3mOxZjUoP3u6SuiL0mmoboM8uoEVaAQDo44QYLaNtu', 'customer');

INSERT INTO categories (id, name, slug, description) VALUES
(1, 'Elektronik',   'elektronik',   'Perangkat audio, power bank, dan gawai pendukung.'),
(2, 'Fashion',      'fashion',      'Pakaian dan alas kaki sehari-hari.'),
(3, 'Aksesoris',    'aksesoris',    'Jam tangan, tas, dan pelengkap gaya.'),
(4, 'Rumah Tangga', 'rumah-tangga', 'Perlengkapan dapur dan penyimpanan.'),
(5, 'Kecantikan',   'kecantikan',   'Perawatan kulit dan wajah.'),
(6, 'Olahraga',     'olahraga',     'Perlengkapan latihan dan aktivitas luar ruang.');

-- 16 produk (15 aktif + 1 nonaktif). Gambar ada di public/uploads/products/.
INSERT INTO products (id, category_id, name, slug, description, price, stock, image, status, created_at) VALUES
( 1, 1, 'Wireless Headphone',          'wireless-headphone',          'Headphone Bluetooth over-ear dengan bantalan empuk. Baterai tahan hingga 30 jam dan bisa dilipat untuk dibawa bepergian.', 299000, 20, 'wireless-headphone.jpg',   'active',   '2026-10-26 10:00:00'),
( 2, 2, 'Sneakers Casual',             'sneakers-casual',             'Sepatu kasual berbahan kulit sintetis dengan sol karet anti-slip. Nyaman dipakai seharian.',                              350000, 15, 'sneakers-casual.jpg',      'active',   '2026-10-27 10:00:00'),
( 3, 3, 'Smart Watch',                 'smart-watch',                 'Jam tangan pintar dengan notifikasi ponsel, penghitung langkah, dan pemantau tidur. Tali silikon mudah diganti.',        450000, 10, 'smart-watch.jpg',          'active',   '2026-10-28 10:00:00'),
( 4, 4, 'Tumbler Stainless 500 ml',    'tumbler-stainless-500ml',     'Botol stainless dua lapis yang menjaga minuman tetap panas atau dingin hingga 12 jam. Tutup ulir anti-bocor.',           89000, 57, 'tumbler-stainless.jpg',    'active',   '2026-10-30 10:00:00'),
( 5, 2, 'Kaos Katun Basic',            'kaos-katun-basic',            'Kaos katun combed 30s yang adem dan tidak mudah melar. Tersedia ukuran S sampai XL.',                                     79000, 60, 'kaos-katun-basic.jpg',     'active',   '2026-11-01 10:00:00'),
( 6, 5, 'Sunscreen SPF 50 PA++++',     'sunscreen-spf-50',            'Tabir surya bertekstur ringan untuk pemakaian harian. Melindungi dari sinar UVA dan UVB.',                                72000,  3, 'sunscreen-spf-50.jpg',     'active',   '2026-11-03 10:00:00'),
( 7, 1, 'Power Bank 10.000 mAh',       'power-bank-10000mah',         'Power bank tipis dengan pengisian cepat 20 W dan dua port USB. Cukup untuk dua kali isi penuh ponsel.',                  219000, 31, 'power-bank.jpg',           'active',   '2026-11-05 10:00:00'),
( 8, 6, 'Matras Yoga 6 mm',            'matras-yoga-6mm',             'Matras TPE setebal 6 mm dengan permukaan anti-slip. Ringan dan mudah digulung.',                                         159000,  0, 'matras-yoga.jpg',          'active',   '2026-11-07 10:00:00'),
( 9, 3, 'Tas Tote Kanvas',             'tas-tote-kanvas',             'Tas tote berbahan kanvas tebal dengan tali panjang. Muat laptop 14 inci dan perlengkapan harian.',                       129000, 22, 'tas-tote-kanvas.jpg',      'active',   '2026-11-10 10:00:00'),
(10, 2, 'Jaket Parasut (arsip)',       'jaket-parasut-arsip',         'Produk lama yang tidak dijual lagi.',                                                                                   249000,  5, NULL,                       'inactive', '2026-11-11 10:00:00'),
(11, 4, 'Set Toples Kaca Kedap Udara', 'set-toples-kaca',             'Isi 6 toples kaca dengan tutup ulir kedap udara. Cocok untuk menyimpan bumbu, kopi, dan camilan.',                      135000, 18, 'set-toples-kaca.jpg',      'active',   '2026-11-13 10:00:00'),
(12, 5, 'Sabun Wajah Gentle',          'sabun-wajah-gentle',          'Pembersih wajah berbentuk gel dengan pH seimbang. Lembut untuk kulit sensitif.',                                          45000, 40, 'sabun-wajah-gentle.jpg',   'active',   '2026-11-15 10:00:00'),
(13, 6, 'Botol Minum Olahraga 1 L',    'botol-minum-olahraga-1l',     'Botol stainless dengan tutup bertali untuk dibawa berolahraga. Bebas BPA.',                                               65000, 35, 'botol-minum-olahraga.jpg', 'active',   '2026-11-18 10:00:00'),
(14, 1, 'Earbuds Nirkabel Pro',        'earbuds-nirkabel-pro',        'Earbuds dengan peredam bising aktif dan casing pengisi daya. Total pemakaian hingga 24 jam.',                           349000, 24, 'earbuds-nirkabel-pro.jpg', 'active',   '2026-11-20 10:00:00'),
(15, 3, 'Topi Baseball Polos',         'topi-baseball-polos',         'Topi model trucker dengan jaring di bagian belakang dan pengatur ukuran.',                                                59000,  4, 'topi-baseball-polos.jpg',  'active',   '2026-11-22 10:00:00'),
(16, 2, 'Kemeja Linen Lengan Panjang', 'kemeja-linen-lengan-panjang', 'Kemeja linen yang adem dengan potongan regular fit. Lengan bisa digulung.',                                              189000, 12, 'kemeja-linen.jpg',         'active',   '2026-11-24 10:00:00');

INSERT INTO addresses (id, user_id, recipient_name, phone, address, city, province, postal_code, is_default) VALUES
(1, 3, 'Customer Contoh', '081200000001', 'Jl. Contoh Raya No. 10', 'Kota Contoh', 'Jawa Timur', '60000', 1),
(2, 4, 'Customer Kedua',  '081200000002', 'Jl. Percobaan No. 5',    'Kota Uji',    'Jawa Barat', '40000', 1);

-- Pesanan contoh dengan berbagai status (untuk produk terlaris, dashboard, dan laporan).
-- Ongkos kirim nilai tetap Rp15.000.
INSERT INTO orders (id, user_id, address_id, order_number, subtotal, shipping_cost, total, order_status, created_at) VALUES
(1, 3, 1, 'SE-0001', 477000, 15000, 492000, 'completed',            NOW() - INTERVAL 9 DAY),
(2, 4, 2, 'SE-0002', 425000, 15000, 440000, 'shipped',              NOW() - INTERVAL 6 DAY),
(3, 3, 1, 'SE-0003', 223000, 15000, 238000, 'processing',           NOW() - INTERVAL 4 DAY),
(4, 4, 2, 'SE-0004', 308000, 15000, 323000, 'completed',            NOW() - INTERVAL 3 DAY),
(5, 4, 2, 'SE-0005', 450000, 15000, 465000, 'waiting_verification', NOW() - INTERVAL 1 DAY),
(6, 3, 1, 'SE-0006',  90000, 15000, 105000, 'pending_payment',      NOW() - INTERVAL 2 HOUR);

INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal) VALUES
(1, 1, 'Wireless Headphone',       299000, 1, 299000),
(1, 4, 'Tumbler Stainless 500 ml',  89000, 2, 178000),
(2, 4, 'Tumbler Stainless 500 ml',  89000, 3, 267000),
(2, 5, 'Kaos Katun Basic',          79000, 2, 158000),
(3, 6, 'Sunscreen SPF 50 PA++++',   72000, 2, 144000),
(3, 5, 'Kaos Katun Basic',          79000, 1,  79000),
(4, 4, 'Tumbler Stainless 500 ml',  89000, 1,  89000),
(4, 7, 'Power Bank 10.000 mAh',    219000, 1, 219000),
(5, 3, 'Smart Watch',              450000, 1, 450000),
(6, 12, 'Sabun Wajah Gentle',       45000, 2,  90000);

-- File bukti contoh ada di writable/uploads/payments/bukti-contoh.png
INSERT INTO payments (order_id, method, amount, proof_file, payment_status, verified_by, verified_at) VALUES
(1, 'transfer_bank', 492000, 'bukti-contoh.png', 'verified',  1,    NOW() - INTERVAL 9 DAY),
(2, 'transfer_bank', 440000, 'bukti-contoh.png', 'verified',  1,    NOW() - INTERVAL 6 DAY),
(3, 'transfer_bank', 238000, 'bukti-contoh.png', 'verified',  1,    NOW() - INTERVAL 4 DAY),
(4, 'transfer_bank', 323000, 'bukti-contoh.png', 'verified',  1,    NOW() - INTERVAL 3 DAY),
(5, 'transfer_bank', 465000, 'bukti-contoh.png', 'submitted', NULL, NULL),
(6, 'transfer_bank', 105000, NULL,               'unpaid',    NULL, NULL);

INSERT INTO shipments (order_id, courier, tracking_number, shipment_status, shipped_at, delivered_at) VALUES
(1, 'JNE', 'CONTOH0000001', 'delivered', NOW() - INTERVAL 8 DAY, NOW() - INTERVAL 6 DAY),
(2, 'J&T', 'CONTOH0000002', 'shipped',   NOW() - INTERVAL 5 DAY, NULL),
(4, 'JNE', 'CONTOH0000004', 'delivered', NOW() - INTERVAL 2 DAY, NOW() - INTERVAL 1 DAY);
