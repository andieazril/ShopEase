<?php

namespace App\Controllers;

use App\Models\ProductModel;

/**
 * CartController – keranjang belanja (kerangka, HOL Praktikum 8)
 *
 * Route dilindungi filter auth:customer.
 * Penyimpanan ke tabel carts & cart_items dikerjakan di Praktikum 8.
 */
class CartController extends BaseController
{
    protected $helpers = ['url', 'form', 'shopease'];

    public function index()
    {
        return view('frontend/cart', [
            'title'      => 'Keranjang – ShopEase',
            'activeMenu' => 'cart',
        ]);
    }

    /**
     * Validasi tambah ke keranjang (HOL Praktikum 8, Langkah 3).
     */
    public function add()
    {
        $productId = (int) $this->request->getPost('product_id');
        $quantity  = (int) $this->request->getPost('quantity');

        if ($quantity < 1) {
            return redirect()->back()->with('error', 'Jumlah tidak valid. Minimal 1.');
        }

        $product = (new ProductModel())->findActive($productId);

        if ($product === null) {
            return redirect()->back()->with('error', 'Produk tidak ditemukan atau sudah tidak dijual.');
        }

        if ((int) $product['stock'] < $quantity) {
            return redirect()->back()->with('error', 'Stok tidak mencukupi. Sisa stok: ' . $product['stock'] . '.');
        }

        // TODO Praktikum 8: cari/buat cart milik user yang login,
        // lalu tambahkan item atau perbarui quantity di cart_items.

        return redirect()->to('/cart')->with(
            'success',
            $quantity . ' × ' . $product['name'] . ' lolos validasi. Penyimpanan ke keranjang dibuat di Praktikum 8.',
        );
    }
}
