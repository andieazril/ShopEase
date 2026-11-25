# ShopEase – Online Store

ShopEase adalah aplikasi toko online berbasis web yang dibangun dengan **CodeIgniter 4** dan **MySQL**. Customer dapat:

- mencari produk dan memasukkannya ke keranjang,
- checkout,
- membayar lewat transfer bank dengan mengunggah bukti transfer,
- memantau pesanan sampai paket diterima.

Admin mengelola katalog, memverifikasi pembayaran, dan melihat laporan penjualan. Operator memproses pengiriman dan memperbarui stok.

Proyek ini dikerjakan untuk mata kuliah **Pemrograman Berbasis Kerangka Kerja (PBKK)**.

| | |
| :-- | :-- |
| Nama | Andie Azril Alfrianto |
| NRP | 5025241054 |

![Beranda ShopEase](screenshots/01-beranda.png)

## Daftar isi

1. [Fitur](#fitur)
2. [Teknologi](#teknologi)
3. [Instalasi](#instalasi)
4. [Akun demo](#akun-demo)
5. [Hak akses per role](#hak-akses-per-role)
6. [Alur transaksi](#alur-transaksi)
7. [Struktur project](#struktur-project)
8. [Database](#database)
9. [Keamanan](#keamanan)
10. [Pengujian](#pengujian)
11. [Deployment](#deployment)
12. [Riwayat pengembangan](#riwayat-pengembangan)
13. [Screenshot](#screenshot)

## Fitur

**Customer**
- **Beranda:** banner promosi, kategori, produk terbaru, produk terlaris, dan pencarian.
- **Katalog:** pencarian, filter kategori, urutan harga/nama, dan pagination.
- **Detail produk:** foto, deskripsi, harga, stok, dan produk lain di kategori yang sama.
- **Registrasi, login, dan logout.**
- **Keranjang:** tambah, ubah jumlah, hapus, dengan harga satuan, subtotal, dan total.
- **Checkout:** pilih alamat tersimpan atau tambah alamat baru, ringkasan pesanan, dan ongkos kirim.
- **Pesanan:** riwayat pesanan, detail pesanan, unggah bukti transfer, dan nomor resi pengiriman.

**Admin**
- **Dashboard:** jumlah produk, customer, pesanan, total penjualan, pesanan pending, dan pembayaran menunggu verifikasi. Dilengkapi grafik penjualan 14 hari, produk terlaris, dan transaksi terbaru.
- **CRUD kategori:** kategori yang masih dipakai produk tidak bisa dihapus.
- **CRUD produk:** termasuk upload gambar, pencarian, dan filter kategori/status. Produk yang pernah dipesan dinonaktifkan, bukan dihapus.
- **Pesanan:** verifikasi pembayaran (terima atau tolak) dan pembatalan pesanan.
- **Data pelanggan:** jumlah pesanan dan total belanja setiap pelanggan.
- **Laporan penjualan:** per periode tanggal dan status pesanan.

**Operator**
- **Pesanan:** melihat pesanan, mengisi kurir dan nomor resi, lalu menandai pesanan selesai.
- **Produk:** memperbarui stok.

## Teknologi

| Komponen | Teknologi |
| :-- | :-- |
| Framework | CodeIgniter 4.7 |
| Bahasa | PHP 8.2 atau lebih baru |
| Database | MySQL / MariaDB |
| Akses data | CodeIgniter Model dan Query Builder |
| Tampilan | HTML, CSS, Bootstrap 5.3, Bootstrap Icons, JavaScript |
| Grafik | Chart.js |
| Arsitektur | Model–View–Controller (MVC) |

Bootstrap, Bootstrap Icons, Chart.js, dan font dimuat dari CDN, jadi aplikasi membutuhkan koneksi internet saat dibuka.

## Instalasi

**Kebutuhan:**
- PHP 8.2+ dengan ekstensi `intl`, `mbstring`, `mysqli`, dan `zip`.
- Composer.
- MySQL/MariaDB, misalnya lewat XAMPP atau Laragon.

1. **Buat project CodeIgniter 4**
   ```bash
   composer create-project codeigniter4/appstarter shopease
   cd shopease
   ```
   Di XAMPP, kalau muncul error soal `intl` atau `zip`, buka `C:\xampp\php\php.ini`. Hapus tanda `;` di depan `extension=intl` dan `extension=zip`, lalu jalankan ulang perintahnya.

2. **Salin source code ShopEase.** Salin folder `app/`, `public/`, `database/`, dan `writable/uploads/` dari repository ini ke folder project, lalu timpa file yang sudah ada. Setelah itu hapus file bawaan CI4 yang tidak dipakai, yaitu `app/Controllers/Home.php` dan `app/Views/welcome_message.php`.

3. **Buat database.** Nyalakan MySQL, lalu import `database/db_shopease.sql` lewat phpMyAdmin (menu **Import**) atau terminal:
   ```bash
   mysql -u root -p < database/db_shopease.sql
   ```
   File ini membuat database `db_shopease`, 10 tabel, dan data awal.

4. **Atur `.env`.** Salin file `env` menjadi `.env`, lalu isi:
   ```ini
   CI_ENVIRONMENT = development

   app.baseURL = 'http://localhost:8080/'
   app.indexPage = ''
   app.appTimezone = 'Asia/Jakarta'

   database.default.hostname = localhost
   database.default.database = db_shopease
   database.default.username = root
   database.default.password =
   database.default.DBDriver = MySQLi
   database.default.port = 3306
   ```

5. **Jalankan aplikasi**
   ```bash
   php spark serve
   ```
   Buka <http://localhost:8080>.

## Akun demo

| Role | Email | Password |
| :-- | :-- | :-- |
| admin | admin@shopease.test | admin12345 |
| operator | operator@shopease.test | operator12345 |
| customer | customer@shopease.test | customer12345 |
| customer | customer2@shopease.test | customer12345 |

Akun customer baru bisa dibuat lewat halaman **Daftar**. Akun admin dan operator hanya bisa dibuat langsung di database. Semua nama, email, alamat, dan nomor telepon di data awal adalah data contoh.

## Hak akses per role

| Fitur | Tamu | Customer | Operator | Admin |
| :-- | :-- | :-- | :-- | :-- |
| Beranda, katalog, detail produk | Ya | Ya | Ya | Ya |
| Keranjang, checkout, pesanan saya | Tidak | Ya | Tidak | Tidak |
| Dashboard admin | Tidak | Tidak | Ya | Ya |
| Lihat pesanan, kirim, tandai selesai | Tidak | Tidak | Ya | Ya |
| Lihat produk, ubah stok | Tidak | Tidak | Ya | Ya |
| Tambah, ubah, hapus produk | Tidak | Tidak | Tidak | Ya |
| CRUD kategori | Tidak | Tidak | Tidak | Ya |
| Verifikasi pembayaran, batalkan pesanan | Tidak | Tidak | Tidak | Ya |
| Data pelanggan, laporan penjualan | Tidak | Tidak | Tidak | Ya |

Hak akses dicek di server oleh `AuthFilter` yang dipasang di route, misalnya `['filter' => 'auth:admin']`. Jadi halaman yang tidak diizinkan tetap ditolak walaupun URL-nya diketik langsung.

## Alur transaksi

| Langkah | Pelaku | Status pesanan | Status pembayaran |
| :-- | :-- | :-- | :-- |
| 1. Checkout | Customer | Menunggu pembayaran (`pending_payment`) | Belum dibayar (`unpaid`) |
| 2. Unggah bukti transfer | Customer | Menunggu verifikasi (`waiting_verification`) | Bukti dikirim (`submitted`) |
| 3a. Bukti diterima | Admin | Diproses (`processing`) | Terverifikasi (`verified`) |
| 3b. Bukti ditolak | Admin | Kembali ke menunggu pembayaran, customer bisa unggah ulang | Ditolak (`rejected`) |
| 4. Isi kurir dan nomor resi | Operator / admin | Dikirim (`shipped`) | Terverifikasi |
| 5. Paket diterima | Operator / admin | Selesai (`completed`) | Terverifikasi |
| Pembatalan (dari langkah 1 atau 2) | Admin | Dibatalkan (`cancelled`), stok dikembalikan | Tidak berubah |

**Aturan penting:**
- **Kapan stok berkurang:** saat pesanan dibuat. Stok dikembalikan jika pesanan dibatalkan.
- **Total pesanan:** dihitung ulang di server dari harga di database. Ongkos kirim nilai tetap Rp15.000.
- **Riwayat harga:** `order_items` menyimpan salinan nama dan harga produk saat transaksi, jadi riwayat tidak berubah walaupun harga katalog diubah.
- **Penghapusan pesanan:** pesanan tidak pernah dihapus. Pesanan yang tidak jadi dibayar dibatalkan lewat status.

## Struktur project

```
app/
├── Config/
│   ├── Routes.php                  semua route + filter role
│   ├── Filters.php                 alias 'auth' dan CSRF untuk semua request
│   └── Pager.php                   template pagination Bootstrap
├── Controllers/
│   ├── HomeController.php          beranda
│   ├── AuthController.php          registrasi, login, logout
│   ├── ProductController.php       katalog dan detail produk
│   ├── CartController.php          keranjang
│   ├── CheckoutController.php      checkout (transaksi database)
│   ├── OrderController.php         pesanan customer dan bukti transfer
│   └── Admin/
│       ├── DashboardController.php
│       ├── CategoryController.php
│       ├── ProductController.php
│       ├── OrderController.php
│       ├── CustomerController.php
│       └── ReportController.php
├── Filters/AuthFilter.php          cek login dan role
├── Helpers/shopease_helper.php     format Rupiah, label status, format tanggal
├── Models/
│   ├── UserModel.php               hash password otomatis
│   ├── CategoryModel.php
│   ├── ProductModel.php            filter katalog, terlaris, stok
│   ├── CartModel.php, CartItemModel.php
│   ├── AddressModel.php
│   ├── OrderModel.php              pesanan, statistik, laporan
│   ├── OrderItemModel.php
│   ├── PaymentModel.php
│   └── ShipmentModel.php
└── Views/
    ├── layouts/                    layout utama, navbar, footer, pesan flash
    ├── frontend/                   beranda, katalog, detail, keranjang, checkout, pesanan
    ├── auth/                       login, registrasi
    ├── admin/                      dashboard, kategori, produk, pesanan, pelanggan, laporan
    └── pager/                      tampilan pagination
database/
└── db_shopease.sql                 skema 10 tabel + data awal
public/
├── assets/css/shopease.css
└── uploads/products/               gambar produk
writable/
└── uploads/payments/               bukti transfer (tidak bisa diakses langsung dari URL)
```

## Database

Database `db_shopease` terdiri dari 10 tabel:

| Tabel | Isi | Relasi |
| :-- | :-- | :-- |
| `users` | Akun admin, operator, customer | Punya banyak `orders`, `addresses`; satu `carts` |
| `categories` | Kategori produk | Punya banyak `products` |
| `products` | Produk, harga, stok, gambar, status aktif/nonaktif | Milik satu `categories` |
| `carts` | Keranjang aktif (satu per customer) | Milik satu `users`; punya banyak `cart_items` |
| `cart_items` | Produk dan jumlah di keranjang | Milik `carts` dan `products` |
| `addresses` | Alamat pengiriman | Milik satu `users` |
| `orders` | Pesanan: nomor, subtotal, ongkir, total, status | Milik `users` dan `addresses`; punya banyak `order_items` |
| `order_items` | Item pesanan dengan salinan nama dan harga | Milik `orders` dan `products` |
| `payments` | Pembayaran, bukti transfer, siapa dan kapan diverifikasi | Milik satu `orders` |
| `shipments` | Kurir, nomor resi, waktu dikirim dan diterima | Milik satu `orders` |

Uang disimpan dengan tipe `DECIMAL(12,2)`. Kolom email, slug, dan nomor pesanan bersifat unik.

**Data awal:**
- 4 akun: 1 admin, 1 operator, dan 2 customer.
- 6 kategori.
- 16 produk: 15 aktif dan 1 nonaktif.
- 6 pesanan contoh dengan berbagai status.

**Backup:** ekspor lewat phpMyAdmin (menu **Export**, format SQL) atau jalankan `mysqldump -u root -p db_shopease > backup.sql`. Untuk memulihkan, import kembali file tersebut.

## Keamanan

- **Password:** di-hash dengan `password_hash()` dan dicek dengan `password_verify()`.
- **Login:**
  - Email yang tidak terdaftar dan password yang salah menghasilkan pesan yang sama, supaya tidak ketahuan email mana yang terdaftar.
  - ID session dibuat ulang setelah login.
  - Percobaan login dibatasi 5 kali per menit per IP.
- **CSRF:** aktif untuk semua request. Semua form yang mengubah data memakai method POST dengan token CSRF, termasuk logout.
- **Hak akses:** dicek di server untuk setiap route. Customer hanya bisa membuka pesanan, item keranjang, alamat, dan bukti transfer miliknya sendiri.
- **Validasi input:** semua input divalidasi di server, dan semua output di view di-escape dengan `esc()`.
- **Upload file:**
  - Hanya gambar JPG, PNG, atau WEBP, maksimal 2 MB. Jenis file dicek dari isi dan ekstensinya.
  - Nama file dibuat acak oleh sistem.
  - Bukti transfer disimpan di luar folder `public`, sedangkan `public/uploads/.htaccess` menolak eksekusi file script.
- **Transaksi database:** checkout dan pembatalan pesanan berjalan dalam transaksi database. Pengurangan stok hanya berhasil jika stok masih cukup, jadi stok tidak bisa minus.

## Pengujian

Pengujian dijalankan dengan request HTTP sungguhan ke aplikasi yang berjalan (CodeIgniter 4.7.6, MariaDB 10.11). Hasil terakhir: **154 dari 154 pengecekan lulus**.

| Kelompok | Jumlah pengecekan | Contoh skenario |
| :-- | :-- | :-- |
| Autentikasi | 28 | Registrasi valid dan tidak valid, login salah, logout, CSRF, batas percobaan login |
| Katalog dan routing | 41 | Pencarian, filter kategori, urutan, pagination, produk nonaktif 404, pembatasan role |
| Transaksi dan admin | 85 | CRUD kategori/produk, upload gambar, keranjang, checkout, stok, pembayaran, pengiriman, pembatalan, laporan |

Skenario keamanan yang diuji:
- Akses halaman admin tanpa login atau dengan role yang salah.
- Upload file bukan gambar.
- Checkout dengan stok tidak cukup.
- Manipulasi harga lewat browser.
- Membuka pesanan milik customer lain.
- Request tanpa token CSRF.
- Submit checkout dua kali.

Rincian setiap skenario ada di README pertemuan masing-masing.

## Deployment

1. Siapkan hosting yang mendukung PHP 8.2+ dan MySQL/MariaDB.
2. Unggah source code, buat database, lalu import `database/db_shopease.sql`.
3. **Atur `.env` untuk production:**
   ```ini
   CI_ENVIRONMENT = production
   app.baseURL = 'https://domain-anda.com/'
   app.indexPage = ''
   app.forceGlobalSecureRequests = true
   ```
   Isi juga pengaturan `database.default.*` sesuai database di hosting.
4. Arahkan **document root** ke folder `public/`, supaya `app/`, `writable/`, dan `.env` tidak bisa diakses dari browser.
5. **Atur permission folder:** pastikan folder `writable/` dan `public/uploads/products/` bisa ditulis oleh web server.
6. Aktifkan HTTPS.
7. Uji ulang setelah deployment: login setiap role, checkout, unggah bukti, verifikasi, dan kirim pesanan.

## Riwayat pengembangan

| Pertemuan | Fitur |
| :-- | :-- |
| 10 | Landing page: layout, navbar, footer, banner, kategori, produk terbaru dan terlaris |
| 11 | Autentikasi (registrasi, login, logout, role, `AuthFilter`), kerangka controller dan routing seluruh modul, katalog dan detail produk dari database |
| 12 | Keranjang, checkout, pesanan, pembayaran, pengiriman, CRUD kategori dan produk, dashboard dengan grafik, data pelanggan, laporan, pengujian, dan deployment |

## Screenshot

**Beranda**

![Beranda](screenshots/01-beranda.png)

**Katalog produk**

![Katalog](screenshots/02-katalog.png)

**Detail produk**

![Detail produk](screenshots/03-detail-produk.png)

**Login**

![Login](screenshots/11-login.png)

**Keranjang**

![Keranjang](screenshots/04-keranjang.png)

**Checkout**

![Checkout](screenshots/05-checkout.png)

**Detail pesanan customer dengan nomor resi**

![Detail pesanan](screenshots/06-detail-pesanan.png)

**Dashboard admin**

![Dashboard admin](screenshots/07-admin-dashboard.png)

**Verifikasi pembayaran**

![Verifikasi pembayaran](screenshots/08-admin-verifikasi.png)

**Manajemen produk**

![Manajemen produk](screenshots/09-admin-produk.png)

**Laporan penjualan**

![Laporan penjualan](screenshots/10-admin-laporan.png)

**Tampilan HP**

<table>
  <tr>
    <td align="center"><img src="screenshots/12-mobile-katalog.png" width="250" alt="Katalog di HP"><br>Katalog</td>
    <td align="center"><img src="screenshots/13-mobile-checkout.png" width="250" alt="Checkout di HP"><br>Checkout</td>
  </tr>
</table>

---

<sub>Foto produk diambil dari [Unsplash](https://unsplash.com) dan dipakai sesuai [Lisensi Unsplash](https://unsplash.com/license). Seluruh data akun, alamat, dan pesanan adalah data contoh.</sub>
