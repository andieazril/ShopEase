<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Keranjang aktif milik customer (satu keranjang per customer).
 */
class CartModel extends Model
{
    protected $table         = 'carts';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['user_id'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil keranjang milik user, buat baru jika belum ada.
     */
    public function getOrCreate(int $userId): array
    {
        $cart = $this->where('user_id', $userId)->first();

        if ($cart === null) {
            $id   = $this->insert(['user_id' => $userId]);
            $cart = $this->find($id);
        }

        return $cart;
    }

    /**
     * Isi keranjang + data produk terbaru dari database.
     * Harga selalu diambil dari tabel products, bukan dari browser.
     *
     * @return list<array<string, mixed>>
     */
    public function getItems(int $userId): array
    {
        return $this->db->table('cart_items')
            ->select('cart_items.id, cart_items.product_id, cart_items.quantity')
            ->select('products.name, products.price, products.stock, products.image, products.status')
            ->select('categories.name AS category_name, categories.slug AS category_slug')
            ->join('carts', 'carts.id = cart_items.cart_id')
            ->join('products', 'products.id = cart_items.product_id')
            ->join('categories', 'categories.id = products.category_id')
            ->where('carts.user_id', $userId)
            ->orderBy('cart_items.id', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Ringkasan keranjang: item + subtotal per item + total + daftar masalah.
     * Masalah = produk nonaktif atau kuantitas melebihi stok.
     *
     * @return array{items: list<array<string, mixed>>, total: float, count: int, hasProblem: bool}
     */
    public function summary(int $userId): array
    {
        $items      = $this->getItems($userId);
        $total      = 0.0;
        $count      = 0;
        $hasProblem = false;

        foreach ($items as &$item) {
            $item['subtotal'] = (float) $item['price'] * (int) $item['quantity'];
            $item['problem']  = null;

            if ($item['status'] !== 'active') {
                $item['problem'] = 'Produk ini sudah tidak dijual. Hapus dari keranjang.';
            } elseif ((int) $item['stock'] < 1) {
                $item['problem'] = 'Stok habis. Hapus dari keranjang.';
            } elseif ((int) $item['quantity'] > (int) $item['stock']) {
                $item['problem'] = 'Jumlah melebihi stok. Sisa stok: ' . $item['stock'] . '.';
            }

            $hasProblem = $hasProblem || $item['problem'] !== null;
            $total += $item['subtotal'];
            $count += (int) $item['quantity'];
        }

        return ['items' => $items, 'total' => $total, 'count' => $count, 'hasProblem' => $hasProblem];
    }

    /**
     * Total kuantitas di keranjang (untuk badge di navbar).
     */
    public function countItems(int $userId): int
    {
        $row = $this->db->table('cart_items')
            ->selectSum('cart_items.quantity', 'qty')
            ->join('carts', 'carts.id = cart_items.cart_id')
            ->where('carts.user_id', $userId)
            ->get()
            ->getRowArray();

        return (int) ($row['qty'] ?? 0);
    }

    public function clear(int $userId): void
    {
        $cart = $this->where('user_id', $userId)->first();

        if ($cart !== null) {
            $this->db->table('cart_items')->where('cart_id', $cart['id'])->delete();
        }
    }
}
