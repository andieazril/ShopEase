<?php

namespace App\Controllers;

/**
 * HomeController – landing page ShopEase.
 *
 * Data kategori dan produk masih berupa data contoh di controller.
 * Setelah database dibuat, ganti getCategories() dan getProducts()
 * dengan query dari CategoryModel dan ProductModel.
 */
class HomeController extends BaseController
{
    /**
     * Helper yang dimuat otomatis oleh initController().
     *
     * @var list<string>
     */
    protected $helpers = ['url', 'shopease'];

    /**
     * Ongkos kirim nilai tetap (asumsi PRD bagian 20).
     */
    private const SHIPPING_COST = 15000;

    public function index()
    {
        // Produk nonaktif tidak ditampilkan (PRD BR-002).
        $products = array_values(array_filter(
            $this->getProducts(),
            static fn (array $p): bool => $p['status'] === 'active',
        ));

        $categories  = $this->withStats($this->getCategories(), $products);
        $products    = $this->withCategory($products, $categories);
        $latest      = $this->getLatest($products, 8);
        $bestSellers = $this->getBestSellers($products, 4);

        $data = [
            'title'           => 'ShopEase Online Store',
            'metaDescription' => 'ShopEase adalah toko online untuk elektronik, fashion, aksesoris, dan kebutuhan harian.',
            'categories'      => $categories,
            'promoBanners'    => $this->getPromoBanners($categories),
            'latestProducts'  => $latest,
            'bestSellers'     => $bestSellers,
            'receipt'         => $this->buildReceiptPreview($bestSellers),
            'steps'           => $this->getShoppingSteps(),
            'stats'           => [
                'products'   => count($products),
                'categories' => count($categories),
            ],
        ];

        return view('frontend/home', $data);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function getCategories(): array
    {
        return [
            ['id' => 1, 'name' => 'Elektronik',   'slug' => 'elektronik'],
            ['id' => 2, 'name' => 'Fashion',      'slug' => 'fashion'],
            ['id' => 3, 'name' => 'Aksesoris',    'slug' => 'aksesoris'],
            ['id' => 4, 'name' => 'Rumah Tangga', 'slug' => 'rumah-tangga'],
            ['id' => 5, 'name' => 'Kecantikan',   'slug' => 'kecantikan'],
            ['id' => 6, 'name' => 'Olahraga',     'slug' => 'olahraga'],
        ];
    }

    /**
     * Data contoh produk. Kolom mengikuti tabel products (Studi Kasus 5.2);
     * "sold" adalah jumlah terjual untuk section produk terlaris.
     * File gambar ada di public/uploads/products/ (foto dari Unsplash).
     *
     * @return list<array<string, mixed>>
     */
    private function getProducts(): array
    {
        return [
            ['id' => 1,  'category_id' => 1, 'name' => 'Wireless Headphone',          'price' => 299000, 'stock' => 20, 'image' => 'wireless-headphone.jpg', 'status' => 'active',   'created_at' => '2026-10-26', 'sold' => 1],
            ['id' => 2,  'category_id' => 2, 'name' => 'Sneakers Casual',             'price' => 350000, 'stock' => 15, 'image' => 'sneakers-casual.jpg', 'status' => 'active',   'created_at' => '2026-10-27', 'sold' => 0],
            ['id' => 3,  'category_id' => 3, 'name' => 'Smart Watch',                 'price' => 450000, 'stock' => 10, 'image' => 'smart-watch.jpg', 'status' => 'active',   'created_at' => '2026-10-28', 'sold' => 0],
            ['id' => 4,  'category_id' => 4, 'name' => 'Tumbler Stainless 500 ml',    'price' => 89000,  'stock' => 57, 'image' => 'tumbler-stainless.jpg', 'status' => 'active',   'created_at' => '2026-10-30', 'sold' => 6],
            ['id' => 5,  'category_id' => 2, 'name' => 'Kaos Katun Basic',            'price' => 79000,  'stock' => 60, 'image' => 'kaos-katun-basic.jpg', 'status' => 'active',   'created_at' => '2026-11-01', 'sold' => 3],
            ['id' => 6,  'category_id' => 5, 'name' => 'Sunscreen SPF 50 PA++++',     'price' => 72000,  'stock' => 3,  'image' => 'sunscreen-spf-50.jpg', 'status' => 'active',   'created_at' => '2026-11-03', 'sold' => 2],
            ['id' => 7,  'category_id' => 1, 'name' => 'Power Bank 10.000 mAh',       'price' => 219000, 'stock' => 31, 'image' => 'power-bank.jpg', 'status' => 'active',   'created_at' => '2026-11-05', 'sold' => 1],
            ['id' => 8,  'category_id' => 6, 'name' => 'Matras Yoga 6 mm',            'price' => 159000, 'stock' => 0,  'image' => 'matras-yoga.jpg', 'status' => 'active',   'created_at' => '2026-11-07', 'sold' => 0],
            ['id' => 9,  'category_id' => 3, 'name' => 'Tas Tote Kanvas',             'price' => 129000, 'stock' => 22, 'image' => 'tas-tote-kanvas.jpg', 'status' => 'active',   'created_at' => '2026-11-10', 'sold' => 0],
            ['id' => 10, 'category_id' => 2, 'name' => 'Jaket Parasut (arsip)',       'price' => 249000, 'stock' => 5,  'image' => null, 'status' => 'inactive', 'created_at' => '2026-11-11', 'sold' => 0],
            ['id' => 11, 'category_id' => 4, 'name' => 'Set Toples Kaca Kedap Udara', 'price' => 135000, 'stock' => 18, 'image' => 'set-toples-kaca.jpg', 'status' => 'active',   'created_at' => '2026-11-13', 'sold' => 0],
            ['id' => 12, 'category_id' => 5, 'name' => 'Sabun Wajah Gentle',          'price' => 45000,  'stock' => 40, 'image' => 'sabun-wajah-gentle.jpg', 'status' => 'active',   'created_at' => '2026-11-15', 'sold' => 0],
            ['id' => 13, 'category_id' => 6, 'name' => 'Botol Minum Olahraga 1 L',    'price' => 65000,  'stock' => 35, 'image' => 'botol-minum-olahraga.jpg', 'status' => 'active',   'created_at' => '2026-11-18', 'sold' => 0],
            ['id' => 14, 'category_id' => 1, 'name' => 'Earbuds Nirkabel Pro',        'price' => 349000, 'stock' => 24, 'image' => 'earbuds-nirkabel-pro.jpg', 'status' => 'active',   'created_at' => '2026-11-20', 'sold' => 0],
            ['id' => 15, 'category_id' => 3, 'name' => 'Topi Baseball Polos',         'price' => 59000,  'stock' => 4,  'image' => 'topi-baseball-polos.jpg', 'status' => 'active',   'created_at' => '2026-11-22', 'sold' => 0],
            ['id' => 16, 'category_id' => 2, 'name' => 'Kemeja Linen Lengan Panjang', 'price' => 189000, 'stock' => 12, 'image' => 'kemeja-linen.jpg', 'status' => 'active',   'created_at' => '2026-11-24', 'sold' => 0],
        ];
    }

    /**
     * Tambahkan jumlah produk aktif dan harga termurah ke setiap kategori.
     *
     * @return list<array<string, mixed>>
     */
    private function withStats(array $categories, array $products): array
    {
        foreach ($categories as &$category) {
            $prices = array_column(
                array_filter($products, static fn (array $p): bool => $p['category_id'] === $category['id']),
                'price',
            );

            $category['product_count'] = count($prices);
            $category['min_price']     = $prices === [] ? 0 : min($prices);
        }

        return $categories;
    }

    /**
     * Lengkapi produk dengan nama dan slug kategorinya.
     *
     * @return list<array<string, mixed>>
     */
    private function withCategory(array $products, array $categories): array
    {
        $byId = array_column($categories, null, 'id');

        return array_map(static function (array $p) use ($byId): array {
            $p['category_name'] = $byId[$p['category_id']]['name'] ?? '';
            $p['category_slug'] = $byId[$p['category_id']]['slug'] ?? '';

            return $p;
        }, $products);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function getLatest(array $products, int $limit): array
    {
        usort($products, static fn (array $a, array $b): int => [$b['created_at'], $b['id']] <=> [$a['created_at'], $a['id']]);

        return array_slice($products, 0, $limit);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function getBestSellers(array $products, int $limit): array
    {
        $sold = array_filter($products, static fn (array $p): bool => $p['sold'] > 0);

        usort($sold, static fn (array $a, array $b): int => [$b['sold'], $a['id']] <=> [$a['sold'], $b['id']]);

        return array_slice($sold, 0, $limit);
    }

    /**
     * Dua kategori dengan produk aktif terbanyak untuk banner promosi.
     *
     * @return list<array<string, mixed>>
     */
    private function getPromoBanners(array $categories): array
    {
        $available = array_filter($categories, static fn (array $c): bool => $c['product_count'] > 0);

        usort($available, static fn (array $a, array $b): int => $b['product_count'] <=> $a['product_count']);

        return array_slice($available, 0, 2);
    }

    /**
     * Contoh struk di hero: subtotal = harga × kuantitas, total dihitung
     * di server (PRD BR-005).
     *
     * @return array<string, mixed>
     */
    private function buildReceiptPreview(array $products): array
    {
        $quantities = [2, 1, 1];
        $items      = [];
        $subtotal   = 0;

        foreach ($products as $product) {
            if (count($items) === count($quantities)) {
                break;
            }

            if ($product['stock'] < 1) {
                continue;
            }

            $quantity  = min($quantities[count($items)], $product['stock']);
            $lineTotal = $product['price'] * $quantity;
            $subtotal += $lineTotal;

            $items[] = [
                'name'     => $product['name'],
                'price'    => $product['price'],
                'quantity' => $quantity,
                'subtotal' => $lineTotal,
            ];
        }

        return [
            'order_number'  => 'SE-' . date('Ymd') . '-0042',
            'date'          => date('d/m/Y H:i'),
            'items'         => $items,
            'subtotal'      => $subtotal,
            'shipping_cost' => self::SHIPPING_COST,
            'total'         => $subtotal + self::SHIPPING_COST,
            'status'        => 'Menunggu pembayaran',
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    private function getShoppingSteps(): array
    {
        return [
            ['icon' => 'bi-search',  'title' => 'Pilih produk',              'text' => 'Cari nama barang atau buka kategori, cek harga dan stok, lalu masukkan ke keranjang.'],
            ['icon' => 'bi-geo-alt', 'title' => 'Checkout',                  'text' => 'Masuk ke akun, pilih alamat pengiriman, dan periksa total sebelum membuat pesanan.'],
            ['icon' => 'bi-receipt', 'title' => 'Transfer dan unggah bukti', 'text' => 'Bayar sesuai instruksi, lalu unggah foto bukti transfer dari halaman pesanan.'],
            ['icon' => 'bi-truck',   'title' => 'Pantau pesanan',            'text' => 'Lihat status verifikasi, pengemasan, dan nomor resi sampai paket diterima.'],
        ];
    }
}
