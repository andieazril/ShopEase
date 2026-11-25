<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    protected $table         = 'payments';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'order_id', 'method', 'amount', 'proof_file',
        'payment_status', 'verified_by', 'verified_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Folder bukti bayar. Disimpan di writable/ (di luar public/), jadi file
     * tidak bisa dibuka atau dijalankan langsung lewat URL.
     */
    public static function proofPath(): string
    {
        return WRITEPATH . 'uploads/payments/';
    }

    public function forOrder(int $orderId): ?array
    {
        return $this->where('order_id', $orderId)->first();
    }

    public function countWaiting(): int
    {
        return $this->where('payment_status', 'submitted')->countAllResults();
    }
}
