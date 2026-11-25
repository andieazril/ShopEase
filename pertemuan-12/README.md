# ShopEase – Transaksi & Admin

**Tugas Pertemuan 12 – Pemrograman Berbasis Kerangka Kerja (PBKK)**

| | |
| :-- | :-- |
| Mata kuliah | Pemrograman Berbasis Kerangka Kerja (PBKK) |
| Pertemuan | 12 |
| Proyek | ShopEase – Online Store |
| Framework | CodeIgniter 4 |
| Nama | Andie Azril Alfrianto |
| NRP | 5025241054 |

## Penjelasan tugas

Pertemuan 12 menyelesaikan semua fitur ShopEase yang masih berupa kerangka di pertemuan 11, sehingga siklus belanja berjalan dari awal sampai akhir:

1. **Customer:** keranjang (tambah, ubah jumlah, hapus), checkout dengan pilihan alamat, riwayat dan detail pesanan, serta unggah bukti transfer.
2. **Admin:** CRUD kategori, CRUD produk dengan upload gambar, verifikasi pembayaran, pengiriman (kurir dan nomor resi), pembatalan pesanan, data pelanggan, dashboard dengan grafik penjualan, dan laporan penjualan per periode dan status.
3. **Operator:** memperbarui stok produk serta memproses pengiriman pesanan.
4. **Pengujian dan deployment:** pengujian fungsional dan keamanan, beserta langkah deployment.

![Dashboard admin](screenshots/01-admin-dashboard.png)

## Perubahan dari pertemuan 11

**File baru**

```
app/
├── Controllers/Admin/
│   ├── CategoryController.php      CRUD kategori
│   ├── CustomerController.php      data pelanggan
│   └── ReportController.php        laporan penjualan
├── Models/
│   ├── CartModel.php               keranjang aktif per customer
│   ├── CartItemModel.php           item keranjang
│   ├── AddressModel.php            alamat pengiriman
│   ├── OrderModel.php              pesanan, statistik, laporan
│   ├── OrderItemModel.php          item pesanan (salinan nama & harga)
│   ├── PaymentModel.php            pembayaran & bukti transfer
│   └── ShipmentModel.php           pengiriman
└── Views/admin/
    ├── categories/index.php, form.php
    ├── products/form.php
    ├── orders/detail.php
    ├── customers/index.php
    └── reports/index.php
public/uploads/.htaccess            mencegah file script dijalankan dari folder upload
writable/uploads/payments/bukti-contoh.png   bukti transfer contoh untuk data awal
```

**File yang diubah**

| File | Perubahan |
| :-- | :-- |
| `app/Config/Routes.php` | Route keranjang, checkout, pembayaran, dan seluruh halaman admin, dengan filter role |
| `app/Controllers/CartController.php` | Keranjang tersimpan di database: tambah, ubah jumlah, hapus |
| `app/Controllers/CheckoutController.php` | Pilih atau tambah alamat, hitung ulang total, buat pesanan dalam satu transaksi database |
| `app/Controllers/OrderController.php` | Riwayat dan detail pesanan, unggah dan lihat bukti transfer |
| `app/Controllers/Admin/ProductController.php` | CRUD produk + upload gambar, update stok untuk operator |
| `app/Controllers/Admin/OrderController.php` | Daftar & detail pesanan, verifikasi, kirim, selesai, batal |
| `app/Controllers/Admin/DashboardController.php` | Statistik, grafik penjualan 14 hari, transaksi terbaru, produk terlaris |
| `app/Controllers/AuthController.php` | Jumlah item keranjang dimuat ke session saat login |
| `app/Controllers/HomeController.php` | Ongkos kirim memakai nilai yang sama dengan checkout |
| `app/Models/ProductModel.php` | Terlaris dihitung dari `order_items`, pengurangan stok yang aman, cek relasi sebelum hapus |
| `app/Models/CategoryModel.php` | Daftar admin, jumlah produk per kategori, slug unik |
| `app/Helpers/shopease_helper.php` | Label status pesanan/pembayaran dan format tanggal |
| `app/Views/frontend/cart.php`, `checkout.php`, `orders/*` | Halaman keranjang, checkout, dan pesanan yang berfungsi |
| `app/Views/admin/*` | Menu admin lengkap, dashboard dengan grafik, tabel produk dengan filter |
| `app/Views/layouts/partials/navbar.php` | Di layar HP, menu akun hanya menampilkan ikon agar navbar tidak melebar |
| `database/db_shopease.sql` | 10 tabel lengkap + data awal pesanan (lihat bagian Database) |
| `public/assets/css/shopease.css` | Gaya keranjang, checkout, pesanan, form admin, grafik |

**File yang dihapus:** `app/Views/layouts/partials/todo.php` (penanda halaman kerangka, sudah tidak dipakai).

## Cara menjalankan

Langkah ini melanjutkan project dari pertemuan 11.

1. Salin folder `app/`, `public/`, `database/`, dan `writable/uploads/` dari paket ini ke folder project. Izinkan file yang sudah ada ditimpa.
2. Hapus `app/Views/layouts/partials/todo.php` jika masih ada.
3. Import ulang `database/db_shopease.sql` lewat phpMyAdmin (menu **Import**). File ini menghapus lalu membuat ulang semua tabel beserta data awalnya, jadi akun yang pernah didaftarkan sebelumnya akan hilang.
4. Pengaturan `.env` tetap sama seperti pertemuan 11.
5. Jalankan `php spark serve`, lalu buka <http://localhost:8080>.

Grafik di dashboard memakai Chart.js dari CDN, jadi perlu koneksi internet.

## Akun demo

| Role | Email | Password |
| :-- | :-- | :-- |
| admin | admin@shopease.test | admin12345 |
| operator | operator@shopease.test | operator12345 |
| customer | customer@shopease.test | customer12345 |
| customer | customer2@shopease.test | customer12345 |

## Hak akses per role

| Fitur | Customer | Operator | Admin |
| :-- | :-- | :-- | :-- |
| Keranjang, checkout, pesanan saya, unggah bukti bayar | Ya | Tidak | Tidak |
| Dashboard admin | Tidak | Ya | Ya |
| Lihat pesanan, kirim pesanan (kurir & resi), tandai selesai | Tidak | Ya | Ya |
| Lihat daftar produk, ubah stok | Tidak | Ya | Ya |
| Tambah, ubah, hapus produk | Tidak | Tidak | Ya |
| CRUD kategori | Tidak | Tidak | Ya |
| Verifikasi pembayaran (terima / tolak) | Tidak | Tidak | Ya |
| Batalkan pesanan | Tidak | Tidak | Ya |
| Data pelanggan, laporan penjualan | Tidak | Tidak | Ya |

Semua pembatasan dicek di server lewat `AuthFilter` pada route. Halaman yang tidak diizinkan tetap ditolak walaupun URL-nya diketik langsung.

## Route baru

| Method | URL | Controller | Akses |
| :-- | :-- | :-- | :-- |
| POST | `/cart/update/(:num)` | `CartController::update` | customer |
| POST | `/cart/remove/(:num)` | `CartController::remove` | customer |
| POST | `/checkout` | `CheckoutController::store` | customer |
| POST | `/orders/(:num)/payment` | `OrderController::uploadPayment` | customer |
| GET | `/orders/(:num)/proof` | `OrderController::proof` | customer (pemilik pesanan) |
| GET | `/admin/orders/(:num)` | `Admin\OrderController::detail` | admin, operator |
| GET | `/admin/orders/(:num)/proof` | `Admin\OrderController::proof` | admin, operator |
| POST | `/admin/orders/(:num)/ship` | `Admin\OrderController::ship` | admin, operator |
| POST | `/admin/orders/(:num)/complete` | `Admin\OrderController::complete` | admin, operator |
| POST | `/admin/orders/(:num)/verify` | `Admin\OrderController::verify` | admin |
| POST | `/admin/orders/(:num)/cancel` | `Admin\OrderController::cancel` | admin |
| POST | `/admin/products/(:num)/stock` | `Admin\ProductController::updateStock` | admin, operator |
| GET / POST | `/admin/products/create`, `/store` | `Admin\ProductController::create` / `store` | admin |
| GET / POST | `/admin/products/(:num)/edit`, `/update` | `Admin\ProductController::edit` / `update` | admin |
| POST | `/admin/products/(:num)/delete` | `Admin\ProductController::delete` | admin |
| GET / POST | `/admin/categories`, `/create`, `/store` | `Admin\CategoryController` | admin |
| GET / POST | `/admin/categories/(:num)/edit`, `/update`, `/delete` | `Admin\CategoryController` | admin |
| GET | `/admin/customers` | `Admin\CustomerController::index` | admin |
| GET | `/admin/reports` | `Admin\ReportController::index` | admin |

Semua aksi yang mengubah data memakai method POST dengan token CSRF.

## Alur pesanan

| Langkah | Pelaku | Status pesanan | Status pembayaran |
| :-- | :-- | :-- | :-- |
| 1. Checkout | Customer | `pending_payment` (Menunggu pembayaran) | `unpaid` (Belum dibayar) |
| 2. Unggah bukti transfer | Customer | `waiting_verification` (Menunggu verifikasi) | `submitted` (Bukti dikirim) |
| 3a. Bukti diterima | Admin | `processing` (Diproses) | `verified` (Terverifikasi) |
| 3b. Bukti ditolak | Admin | kembali ke `pending_payment`, customer bisa unggah ulang | `rejected` (Ditolak) |
| 4. Isi kurir dan nomor resi | Operator / admin | `shipped` (Dikirim) | `verified` |
| 5. Paket diterima | Operator / admin | `completed` (Selesai) | `verified` |
| Pembatalan (hanya dari langkah 1 atau 2) | Admin | `cancelled` (Dibatalkan), stok dikembalikan | tidak berubah |

Status pesanan dan status pembayaran disimpan di kolom yang berbeda (`orders.order_status` dan `payments.payment_status`). Setiap aksi hanya berjalan jika status saat ini mengizinkannya. Contohnya, pesanan yang belum diverifikasi tidak bisa dikirim, dan pesanan yang sudah dibayar tidak bisa dibatalkan.

## Aturan yang diterapkan

**Keranjang**
- Jumlah minimal 1 dan tidak boleh melebihi stok, baik saat menambah maupun saat mengubah jumlah.
- Harga dan stok selalu dibaca ulang dari database.
- Item yang produknya sudah nonaktif atau stoknya kurang ditandai merah, dan checkout dikunci sampai item itu diperbaiki.

**Checkout**
- Subtotal dan total dihitung ulang di server. Nilai harga atau total yang dikirim dari browser diabaikan.
- Pembuatan pesanan, item pesanan, pengurangan stok, data pembayaran, dan pengosongan keranjang berjalan dalam **satu transaksi database**. Jika ada yang gagal, semuanya dibatalkan (rollback).
- Stok dikurangi saat pesanan dibuat, dengan query yang hanya berhasil jika stok masih cukup. Dua checkout bersamaan tidak bisa membuat stok minus.
- **Submit ganda dicegah dua lapis:** tombol dinonaktifkan setelah ditekan, dan di server keranjang yang sudah kosong tidak bisa diproses lagi.
- `order_items` menyimpan salinan nama dan harga produk, jadi riwayat pesanan tidak berubah walaupun harga katalog diubah.
- Ongkos kirim nilai tetap Rp15.000.

**Pesanan dan pembayaran**
- Customer hanya bisa membuka pesanan dan bukti transfer miliknya sendiri. Pesanan orang lain dianggap tidak ada (404).
- **Validasi bukti transfer dan gambar produk:**
  - Harus gambar (JPG, PNG, atau WEBP), dicek dari isi file dan ekstensinya.
  - Ukuran maksimal 2 MB.
  - Nama file dibuat acak oleh sistem.
- **Penyimpanan bukti transfer:** disimpan di `writable/uploads/payments/` (di luar folder `public`). File hanya bisa dibuka lewat controller setelah cek hak akses.
- **Proteksi folder upload:** `public/uploads/.htaccess` menolak eksekusi file script di folder upload.
- **Pencatatan verifikasi:** setiap verifikasi mencatat siapa yang memeriksa dan kapan (`verified_by`, `verified_at`).
- Pesanan tidak pernah dihapus. Pesanan yang tidak jadi dibayar dibatalkan lewat status `cancelled`.

**Kategori dan produk**
- Kategori yang masih dipakai produk tidak bisa dihapus.
- Produk yang pernah dipesan atau sedang ada di keranjang tidak dihapus permanen, tetapi dinonaktifkan. Produk yang belum pernah dipakai dihapus bersama file gambarnya.
- **Validasi produk:** harga tidak boleh negatif, stok harus bilangan bulat 0 atau lebih, kategori harus valid, dan slug URL dibuat otomatis dan unik.

## Database

`database/db_shopease.sql` sekarang berisi 10 tabel:

| Tabel | Isi |
| :-- | :-- |
| `users` | Akun admin, operator, customer |
| `categories` | Kategori produk |
| `products` | Produk, harga, stok, gambar, status |
| `carts`, `cart_items` | Keranjang aktif (satu per customer) dan isinya |
| `addresses` | Alamat pengiriman customer |
| `orders`, `order_items` | Pesanan dan item pesanan (salinan nama & harga) |
| `payments` | Pembayaran, bukti transfer, siapa dan kapan diverifikasi |
| `shipments` | Kurir, nomor resi, waktu dikirim dan diterima |

Data awal:
- 4 akun: 1 admin, 1 operator, dan 2 customer.
- 6 kategori.
- 16 produk: 15 aktif dan 1 nonaktif.
- 6 pesanan contoh dengan status yang berbeda-beda (selesai, dikirim, diproses, menunggu verifikasi, menunggu pembayaran).

Kolom `products.sold` dari pertemuan 11 dihapus. Jumlah terjual sekarang dihitung dari `order_items` pada pesanan yang sudah diproses, dikirim, atau selesai.

**Backup dan restore:** ekspor database lewat phpMyAdmin (menu **Export**, format SQL) atau `mysqldump -u root -p db_shopease > backup.sql`. Untuk memulihkan, import kembali file tersebut.

## Pengujian

Pengujian dijalankan dengan request HTTP sungguhan ke aplikasi yang berjalan (CodeIgniter 4.7.6, MariaDB 10.11). Hasilnya **154 dari 154 pengecekan lulus**:

| Kelompok | Jumlah pengecekan |
| :-- | :-- |
| Fitur pertemuan 12 (transaksi, admin, keamanan) | 85 |
| Autentikasi | 28 |
| Katalog dan routing | 41 |

**Pengujian fungsional**

| No. | Skenario | Hasil yang diharapkan | Hasil |
| :-- | :-- | :-- | :-- |
| 1 | Tambah kategori valid | Tersimpan dengan slug otomatis | Lulus |
| 2 | Tambah kategori dengan nama yang sudah ada / kurang dari 3 karakter | Ditolak dengan pesan validasi | Lulus |
| 3 | Ubah kategori | Nama dan slug ikut berubah | Lulus |
| 4 | Hapus kategori yang masih dipakai produk | Ditolak, kategori tetap ada | Lulus |
| 5 | Hapus kategori kosong | Terhapus | Lulus |
| 6 | Tambah produk dengan gambar | Tersimpan, nama file acak, tampil di katalog | Lulus |
| 7 | Tambah produk tanpa gambar, harga negatif, stok negatif, atau kategori tidak valid | Ditolak dengan pesan validasi | Lulus |
| 8 | Ubah harga produk tanpa mengganti gambar | Harga berubah, gambar tetap | Lulus |
| 9 | Hapus produk yang belum pernah dipesan | Produk dan file gambarnya terhapus | Lulus |
| 10 | Hapus produk yang pernah dipesan | Produk dinonaktifkan, hilang dari katalog, detailnya 404 | Lulus |
| 11 | Cari dan filter produk di halaman admin | Hasil sesuai kata kunci dan status | Lulus |
| 12 | Operator mengubah stok | Stok berubah; nilai negatif ditolak | Lulus |
| 13 | Tambah produk yang sama dua kali ke keranjang | Jumlah dijumlahkan, bukan baris baru | Lulus |
| 14 | Tambah atau ubah jumlah melebihi stok | Ditolak dengan sisa stok | Lulus |
| 15 | Keranjang menampilkan harga satuan, subtotal, total, dan badge jumlah | Angka sesuai | Lulus |
| 16 | Hapus item keranjang | Item hilang | Lulus |
| 17 | Checkout dengan alamat baru yang tidak lengkap | Pesan validasi per kolom | Lulus |
| 18 | Checkout dengan alamat tersimpan | Pesanan dibuat, nomor unik, status menunggu pembayaran | Lulus |
| 19 | Total pesanan | Subtotal + Rp15.000 dihitung server, sesuai item | Lulus |
| 20 | Setelah checkout | Stok berkurang, keranjang kosong, pembayaran belum dibayar | Lulus |
| 21 | Harga katalog diubah setelah pesanan dibuat | Harga di riwayat pesanan tetap | Lulus |
| 22 | Checkout saat stok sudah tidak cukup | Ditolak, stok dan pesanan tidak berubah | Lulus |
| 23 | Checkout dengan alamat baru | Alamat tersimpan dan dipakai pesanan | Lulus |
| 24 | Unggah bukti transfer valid | Status menjadi bukti dikirim & menunggu verifikasi | Lulus |
| 25 | Admin menolak bukti | Pembayaran ditolak, customer bisa unggah ulang | Lulus |
| 26 | Admin menerima bukti | Pembayaran terverifikasi, pesanan diproses, pemeriksa dan waktu tercatat | Lulus |
| 27 | Kirim pesanan tanpa kurir/resi | Ditolak dengan pesan validasi | Lulus |
| 28 | Kirim pesanan dengan kurir dan resi | Status dikirim, customer melihat nomor resi | Lulus |
| 29 | Tandai pesanan selesai | Status selesai, waktu diterima tercatat | Lulus |
| 30 | Admin membatalkan pesanan yang belum dibayar | Status dibatalkan, stok kembali | Lulus |
| 31 | Laporan penjualan per periode dan status | Total sesuai jumlah pesanan di database | Lulus |
| 32 | Laporan periode kosong / tanggal terbalik | Pesan kosong / tanggal ditukar otomatis | Lulus |
| 33 | Dashboard | Total penjualan dan jumlah menunggu verifikasi sesuai database, grafik tampil | Lulus |
| 34 | Data pelanggan | Hanya akun customer yang tampil | Lulus |

**Pengujian keamanan**

| No. | Skenario | Hasil yang diharapkan | Hasil |
| :-- | :-- | :-- | :-- |
| 1 | Request POST tanpa token CSRF | Ditolak (403) | Lulus |
| 2 | Tamu membuka keranjang / checkout | Diarahkan ke login | Lulus |
| 3 | Customer membuka halaman admin | Ditolak | Lulus |
| 4 | Operator membuka CRUD kategori/produk, pelanggan, laporan | Ditolak | Lulus |
| 5 | Operator menghapus produk, memverifikasi pembayaran, membatalkan pesanan | Ditolak, data tidak berubah | Lulus |
| 6 | Upload file bukan gambar yang diberi nama `.jpg` | Ditolak | Lulus |
| 7 | Upload file `.php` sebagai gambar produk | Ditolak | Lulus |
| 8 | Mengirim harga/total palsu saat checkout | Diabaikan, total dihitung dari database | Lulus |
| 9 | Customer membuka pesanan atau bukti transfer customer lain | 404 | Lulus |
| 10 | Customer mengubah item keranjang customer lain | 404, data tidak berubah | Lulus |
| 11 | Memakai alamat milik customer lain saat checkout | Ditolak | Lulus |
| 12 | Membuka file bukti transfer langsung lewat URL publik | Tidak bisa (404) | Lulus |
| 13 | Menekan tombol "Buat pesanan" dua kali | Hanya satu pesanan dibuat | Lulus |
| 14 | Verifikasi ulang, kirim ulang, atau batalkan pesanan yang statusnya tidak sesuai | Ditolak, status tidak berubah | Lulus |
| 15 | Nama kategori berisi `<script>` | Ditampilkan sebagai teks biasa (di-escape) | Lulus |

## Deployment

1. Siapkan hosting yang mendukung PHP 8.2+ dan MySQL/MariaDB, dengan ekstensi `intl`, `mbstring`, dan `mysqli` aktif.
2. Unggah source code, lalu buat database dan import `database/db_shopease.sql`.
3. **Atur `.env` untuk production:**
   ```ini
   CI_ENVIRONMENT = production
   app.baseURL = 'https://domain-anda.com/'
   app.indexPage = ''
   app.forceGlobalSecureRequests = true
   database.default.hostname = ...
   database.default.database = ...
   database.default.username = ...
   database.default.password = ...
   ```
4. Arahkan **document root** ke folder `public/`, bukan ke root project. Dengan begitu `app/`, `writable/`, dan `.env` tidak bisa diakses dari browser.
5. **Atur permission folder:** `writable/` (terutama `writable/cache`, `writable/logs`, `writable/session`, `writable/uploads`) dan `public/uploads/products` harus bisa ditulis oleh web server.
6. Aktifkan HTTPS (misalnya sertifikat Let's Encrypt dari panel hosting).
7. Uji ulang setelah deployment: login tiap role, checkout, unggah bukti, verifikasi, dan kirim pesanan.

## Screenshot

**Dashboard admin.** Statistik, grafik penjualan 14 hari, produk terlaris, dan transaksi terbaru.

![Dashboard](screenshots/01-admin-dashboard.png)

**CRUD kategori**

![Kategori](screenshots/02-admin-kategori.png)

![Validasi kategori](screenshots/03-admin-kategori-validasi.png)

**CRUD produk**

![Daftar produk admin](screenshots/04-admin-produk.png)

![Form produk](screenshots/05-admin-produk-form.png)

**Operator mengubah stok**

![Operator stok](screenshots/06-operator-stok.png)

**Keranjang**

![Keranjang](screenshots/07-keranjang.png)

**Checkout**

![Checkout](screenshots/08-checkout.png)

**Pesanan menunggu pembayaran.** Instruksi transfer dan form unggah bukti.

![Pesanan menunggu bayar](screenshots/09-pesanan-menunggu-bayar.png)

**Bukti terkirim**

![Bukti terkirim](screenshots/10-pesanan-bukti-terkirim.png)

**Admin memverifikasi pembayaran**

![Verifikasi](screenshots/11-admin-verifikasi.png)

**Operator mengisi kurir dan nomor resi**

![Kirim pesanan](screenshots/12-operator-kirim.png)

**Customer melihat nomor resi**

![Nomor resi](screenshots/13-customer-resi.png)

**Riwayat pesanan customer**

![Riwayat pesanan](screenshots/14-riwayat-pesanan.png)

**Daftar pesanan admin**

![Pesanan admin](screenshots/15-admin-pesanan.png)

**Data pelanggan**

![Pelanggan](screenshots/16-admin-pelanggan.png)

**Laporan penjualan**

![Laporan](screenshots/17-admin-laporan.png)

**Tampilan HP – keranjang dan checkout**

<table>
  <tr>
    <td align="center"><img src="screenshots/18-mobile-keranjang.png" width="250" alt="Keranjang di HP"><br>Keranjang</td>
    <td align="center"><img src="screenshots/19-mobile-checkout.png" width="250" alt="Checkout di HP"><br>Checkout</td>
  </tr>
</table>
