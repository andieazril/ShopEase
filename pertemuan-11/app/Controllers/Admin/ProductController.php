<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ProductModel;

/**
 * Admin\ProductController – manajemen produk (kerangka, HOL Praktikum 5)
 *
 * Route dilindungi filter auth:admin.
 * Saat ini baru menampilkan daftar produk (Read). Tambah, ubah,
 * hapus/nonaktifkan, dan upload gambar dikerjakan di Praktikum 5.
 */
class ProductController extends BaseController
{
    protected $helpers = ['url', 'form', 'shopease'];

    public function index()
    {
        $productModel = new ProductModel();

        // Admin melihat semua produk, termasuk yang nonaktif.
        $products = $productModel
            ->select('products.*, categories.name AS category_name, categories.slug AS category_slug')
            ->join('categories', 'categories.id = products.category_id')
            ->orderBy('products.id', 'ASC')
            ->paginate(10);

        return view('admin/products/index', [
            'title'      => 'Produk – Admin ShopEase',
            'activeMenu' => 'admin-products',
            'products'   => $products,
            'pager'      => $productModel->pager,
        ]);
    }
}
