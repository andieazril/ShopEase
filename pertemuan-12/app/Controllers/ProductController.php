<?php

namespace App\Controllers;

use App\Models\CategoryModel;
use App\Models\ProductModel;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * ProductController – halaman customer (HOL Praktikum 7)
 *
 * catalog()     : daftar produk + pencarian, filter kategori, urutan, pagination
 * detail($id)   : detail satu produk
 */
class ProductController extends BaseController
{
    protected $helpers = ['url', 'form', 'shopease'];

    /**
     * Jumlah produk per halaman katalog.
     */
    private const PER_PAGE = 8;

    public function catalog()
    {
        $productModel  = new ProductModel();
        $categoryModel = new CategoryModel();

        // Ambil filter dari query string, contoh: /catalog?keyword=kaos&category=fashion&sort=termurah
        $keyword      = trim((string) $this->request->getGet('keyword'));
        $categorySlug = (string) $this->request->getGet('category');
        $sort         = (string) $this->request->getGet('sort');

        if (! array_key_exists($sort, ProductModel::SORTS)) {
            $sort = 'terbaru';
        }

        $category = $categorySlug !== '' ? $categoryModel->findBySlug($categorySlug) : null;

        $products = $productModel
            ->filter($keyword, $category['id'] ?? null, $sort)
            ->paginate(self::PER_PAGE);

        $data = [
            'title'      => $category !== null ? $category['name'] . ' – Katalog ShopEase' : 'Katalog Produk – ShopEase',
            'activeMenu' => 'catalog',
            'products'   => $products,
            'pager'      => $productModel->pager,
            'total'      => $productModel->pager->getTotal(),
            'categories' => $categoryModel->getWithStats(),
            'category'   => $category,
            'keyword'    => $keyword,
            'sort'       => $sort,
            'sorts'      => [
                'terbaru'  => 'Terbaru',
                'termurah' => 'Harga termurah',
                'termahal' => 'Harga termahal',
                'nama'     => 'Nama (A–Z)',
            ],
        ];

        return view('frontend/catalog', $data);
    }

    public function detail(int $id)
    {
        $productModel = new ProductModel();
        $product      = $productModel->findActive($id);

        // Produk tidak ada atau nonaktif → 404 (PRD BR-002).
        if ($product === null) {
            throw PageNotFoundException::forPageNotFound('Produk tidak ditemukan.');
        }

        $data = [
            'title'      => $product['name'] . ' – ShopEase',
            'activeMenu' => 'catalog',
            'product'    => $product,
            'related'    => $productModel->getRelated($product, 4),
        ];

        return view('frontend/product_detail', $data);
    }
}
