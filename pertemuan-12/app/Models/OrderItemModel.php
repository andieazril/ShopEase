<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Rincian item pesanan. Nama dan harga disimpan sebagai salinan (snapshot)
 * saat transaksi, jadi riwayat tidak berubah ketika harga katalog diubah.
 */
class OrderItemModel extends Model
{
    protected $table         = 'order_items';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['order_id', 'product_id', 'product_name', 'price', 'quantity', 'subtotal'];
}
