<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderModel extends Model
{
    protected $table         = 'orders';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'user_id', 'address_id', 'order_number', 'subtotal',
        'shipping_cost', 'total', 'order_status', 'notes',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ongkos kirim nilai tetap.
     */
    public const SHIPPING_COST = 15000;

    /**
     * Status pesanan yang dihitung sebagai penjualan.
     */
    public const SOLD_STATUSES = ['processing', 'shipped', 'completed'];

    /**
     * Status yang masih boleh dibatalkan (belum dibayar / belum diverifikasi).
     */
    public const CANCELLABLE_STATUSES = ['pending_payment', 'waiting_verification'];

    public const STATUSES = [
        'pending_payment', 'waiting_verification', 'processing',
        'shipped', 'completed', 'cancelled',
    ];

    /**
     * Query pesanan lengkap dengan status pembayaran, data customer, dan pengiriman.
     */
    public function withDetails(): static
    {
        return $this->select('orders.*, payments.payment_status, payments.proof_file, payments.verified_at')
            ->select('users.name AS customer_name, users.email AS customer_email')
            ->select('shipments.courier, shipments.tracking_number, shipments.shipment_status, shipments.shipped_at, shipments.delivered_at')
            ->join('payments', 'payments.order_id = orders.id', 'left')
            ->join('users', 'users.id = orders.user_id')
            ->join('shipments', 'shipments.order_id = orders.id', 'left');
    }

    /**
     * Pesanan milik customer tertentu (customer hanya boleh melihat miliknya).
     */
    public function findOwned(int $id, int $userId): ?array
    {
        return $this->withDetails()
            ->where('orders.id', $id)
            ->where('orders.user_id', $userId)
            ->first();
    }

    public function findWithDetails(int $id): ?array
    {
        return $this->withDetails()->where('orders.id', $id)->first();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function items(int $orderId): array
    {
        return $this->db->table('order_items')
            ->where('order_id', $orderId)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function address(?int $addressId): ?array
    {
        if ($addressId === null) {
            return null;
        }

        return $this->db->table('addresses')->where('id', $addressId)->get()->getRowArray();
    }

    /**
     * Nomor pesanan unik, contoh: SE-20261125-7F3A2C.
     */
    public function generateOrderNumber(): string
    {
        do {
            $number = 'SE-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
        } while ($this->where('order_number', $number)->countAllResults() > 0);

        return $number;
    }

    // ------------------------------------------------------------------
    // Statistik dashboard & laporan
    // ------------------------------------------------------------------

    public function salesTotal(): float
    {
        $row = $this->selectSum('total', 'sales')
            ->whereIn('order_status', self::SOLD_STATUSES)
            ->first();

        return (float) ($row['sales'] ?? 0);
    }

    /**
     * Penjualan per hari dalam N hari terakhir (hari tanpa penjualan bernilai 0).
     *
     * @return array{labels: list<string>, values: list<float>}
     */
    public function dailySales(int $days = 14): array
    {
        $start = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));

        $rows = $this->select('DATE(created_at) AS day, SUM(total) AS sales')
            ->whereIn('order_status', self::SOLD_STATUSES)
            ->where('created_at >=', $start . ' 00:00:00')
            ->groupBy('DATE(created_at)')
            ->findAll();

        $byDay = array_column($rows, 'sales', 'day');
        $labels = [];
        $values = [];

        for ($i = 0; $i < $days; $i++) {
            $day      = date('Y-m-d', strtotime($start . ' +' . $i . ' days'));
            $labels[] = date('d/m', strtotime($day));
            $values[] = (float) ($byDay[$day] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * Rekap penjualan per hari untuk laporan.
     *
     * @param list<string> $statuses
     *
     * @return list<array<string, mixed>>
     */
    public function report(string $from, string $to, array $statuses): array
    {
        return $this->select('DATE(created_at) AS day, COUNT(*) AS order_count, SUM(subtotal) AS subtotal, SUM(shipping_cost) AS shipping, SUM(total) AS total')
            ->where('created_at >=', $from . ' 00:00:00')
            ->where('created_at <=', $to . ' 23:59:59')
            ->whereIn('order_status', $statuses)
            ->groupBy('DATE(created_at)')
            ->orderBy('day', 'ASC')
            ->findAll();
    }
}
