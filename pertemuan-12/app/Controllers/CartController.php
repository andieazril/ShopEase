<?php

namespace App\Controllers;

use App\Models\CartItemModel;
use App\Models\CartModel;
use App\Models\ProductModel;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * CartController – keranjang belanja customer.
 *
 * index()          : isi keranjang, harga satuan, subtotal, total
 * add()            : tambah produk (atau tambah jumlah jika sudah ada)
 * update($itemId)  : ubah jumlah
 * remove($itemId)  : hapus item
 *
 * Route dilindungi filter auth:customer. Harga dan stok selalu dibaca
 * ulang dari database, bukan dari data yang dikirim browser.
 */
class CartController extends BaseController
{
    protected $helpers = ['url', 'form', 'shopease'];

    public function index()
    {
        $summary = (new CartModel())->summary($this->userId());

        return view('frontend/cart', [
            'title'      => 'Keranjang – ShopEase',
            'activeMenu' => 'cart',
            'cart'       => $summary,
        ]);
    }

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

        $cart      = (new CartModel())->getOrCreate($this->userId());
        $itemModel = new CartItemModel();
        $existing  = $itemModel->findInCart((int) $cart['id'], $productId);
        $newQty    = $quantity + (int) ($existing['quantity'] ?? 0);

        // Total di keranjang tidak boleh melebihi stok.
        if ($newQty > (int) $product['stock']) {
            $inCart = (int) ($existing['quantity'] ?? 0);
            $message = 'Stok tidak mencukupi. Sisa stok: ' . $product['stock'] . '.';

            if ($inCart > 0) {
                $message .= ' Di keranjang Anda sudah ada ' . $inCart . '.';
            }

            return redirect()->back()->with('error', $message);
        }

        if ($existing !== null) {
            $itemModel->update($existing['id'], ['quantity' => $newQty]);
        } else {
            $itemModel->insert([
                'cart_id'    => $cart['id'],
                'product_id' => $productId,
                'quantity'   => $quantity,
            ]);
        }

        $this->refreshCartCount();

        return redirect()->to('/cart')
            ->with('success', $quantity . ' × ' . $product['name'] . ' ditambahkan ke keranjang.');
    }

    public function update(int $itemId)
    {
        $itemModel = new CartItemModel();
        $item      = $itemModel->findOwned($itemId, $this->userId());

        if ($item === null) {
            throw PageNotFoundException::forPageNotFound('Item keranjang tidak ditemukan.');
        }

        $quantity = (int) $this->request->getPost('quantity');
        $product  = (new ProductModel())->find($item['product_id']);

        if ($quantity < 1) {
            return redirect()->to('/cart')->with('error', 'Jumlah minimal 1. Gunakan tombol Hapus untuk mengeluarkan produk.');
        }

        if ($product === null || $product['status'] !== 'active') {
            return redirect()->to('/cart')->with('error', 'Produk ini sudah tidak dijual. Hapus dari keranjang.');
        }

        if ($quantity > (int) $product['stock']) {
            return redirect()->to('/cart')->with('error', 'Jumlah ' . $product['name'] . ' melebihi stok. Sisa stok: ' . $product['stock'] . '.');
        }

        $itemModel->update($itemId, ['quantity' => $quantity]);
        $this->refreshCartCount();

        return redirect()->to('/cart')->with('success', 'Jumlah ' . $product['name'] . ' diubah menjadi ' . $quantity . '.');
    }

    public function remove(int $itemId)
    {
        $itemModel = new CartItemModel();
        $item      = $itemModel->findOwned($itemId, $this->userId());

        if ($item === null) {
            throw PageNotFoundException::forPageNotFound('Item keranjang tidak ditemukan.');
        }

        $itemModel->delete($itemId);
        $this->refreshCartCount();

        return redirect()->to('/cart')->with('success', 'Produk dihapus dari keranjang.');
    }

    private function userId(): int
    {
        return (int) session('user_id');
    }

    private function refreshCartCount(): void
    {
        session()->set('cart_count', (new CartModel())->countItems($this->userId()));
    }
}
