<?php

namespace App\Controllers;

use App\Models\CategoryModel;
use App\Models\ProductModel;

/**
 * HomeController – landing page ShopEase.
 *
 * Data kategori dan produk diambil dari database lewat CategoryModel
 * dan ProductModel. Controller hanya menyusun data untuk view.
 */
class HomeController extends BaseController
{
    /**
     * @var list<string>
     */
    protected $helpers = ['url', 'form', 'shopease'];

    /**
     * Ongkos kirim nilai tetap (asumsi PRD bagian 20).
     */
    private const SHIPPING_COST = 15000;

    public function index()
    {
        $productModel  = new ProductModel();
        $categoryModel = new CategoryModel();

        $categories  = $categoryModel->getWithStats();
        $bestSellers = $productModel->getBestSellers(4);

        $data = [
            'title'           => 'ShopEase Online Store',
            'metaDescription' => 'ShopEase adalah toko online untuk elektronik, fashion, aksesoris, dan kebutuhan harian.',
            'activeMenu'      => 'home',
            'categories'      => $categories,
            'promoBanners'    => $this->getPromoBanners($categories),
            'latestProducts'  => $productModel->getLatest(8),
            'bestSellers'     => $bestSellers,
            'receipt'         => $this->buildReceiptPreview($bestSellers),
            'steps'           => $this->getShoppingSteps(),
            'stats'           => [
                'products'   => $productModel->countActive(),
                'categories' => count($categories),
            ],
        ];

        return view('frontend/home', $data);
    }

    /**
     * Dua kategori dengan produk aktif terbanyak untuk banner promosi.
     *
     * @return list<array<string, mixed>>
     */
    private function getPromoBanners(array $categories): array
    {
        $available = array_filter($categories, static fn (array $c): bool => (int) $c['product_count'] > 0);

        usort($available, static fn (array $a, array $b): int => (int) $b['product_count'] <=> (int) $a['product_count']);

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

            if ((int) $product['stock'] < 1) {
                continue;
            }

            $quantity  = min($quantities[count($items)], (int) $product['stock']);
            $price     = (float) $product['price'];
            $lineTotal = $price * $quantity;
            $subtotal += $lineTotal;

            $items[] = [
                'name'     => $product['name'],
                'price'    => $price,
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
