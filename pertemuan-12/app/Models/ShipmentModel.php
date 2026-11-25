<?php

namespace App\Models;

use CodeIgniter\Model;

class ShipmentModel extends Model
{
    protected $table         = 'shipments';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'order_id', 'courier', 'tracking_number',
        'shipment_status', 'shipped_at', 'delivered_at',
    ];

    public function forOrder(int $orderId): ?array
    {
        return $this->where('order_id', $orderId)->first();
    }
}
