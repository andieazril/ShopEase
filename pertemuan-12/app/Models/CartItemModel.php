<?php

namespace App\Models;

use CodeIgniter\Model;

class CartItemModel extends Model
{
    protected $table         = 'cart_items';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['cart_id', 'product_id', 'quantity'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Item keranjang berdasarkan id, hanya jika milik user tersebut.
     */
    public function findOwned(int $itemId, int $userId): ?array
    {
        return $this->select('cart_items.*')
            ->join('carts', 'carts.id = cart_items.cart_id')
            ->where('cart_items.id', $itemId)
            ->where('carts.user_id', $userId)
            ->first();
    }

    public function findInCart(int $cartId, int $productId): ?array
    {
        return $this->where('cart_id', $cartId)->where('product_id', $productId)->first();
    }
}
