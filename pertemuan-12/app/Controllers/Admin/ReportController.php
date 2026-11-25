<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\OrderModel;

/**
 * Admin\ReportController – rekap penjualan berdasarkan periode dan status
 * (khusus admin).
 */
class ReportController extends BaseController
{
    protected $helpers = ['url', 'form', 'shopease'];

    public function index()
    {
        $from   = (string) $this->request->getGet('from');
        $to     = (string) $this->request->getGet('to');
        $status = (string) $this->request->getGet('status');
        $error  = null;

        // Bawaan: awal bulan ini sampai hari ini.
        if (! $this->isDate($from)) {
            $from = date('Y-m-01');
        }

        if (! $this->isDate($to)) {
            $to = date('Y-m-d');
        }

        if ($from > $to) {
            $error = 'Tanggal awal tidak boleh setelah tanggal akhir. Tanggal ditukar otomatis.';
            [$from, $to] = [$to, $from];
        }

        // "terjual" = pesanan yang sudah dibayar dan diproses.
        if ($status === '' || $status === 'terjual') {
            $status   = 'terjual';
            $statuses = OrderModel::SOLD_STATUSES;
        } elseif ($status === 'semua') {
            $statuses = OrderModel::STATUSES;
        } elseif (in_array($status, OrderModel::STATUSES, true)) {
            $statuses = [$status];
        } else {
            $status   = 'terjual';
            $statuses = OrderModel::SOLD_STATUSES;
        }

        $rows   = (new OrderModel())->report($from, $to, $statuses);
        $totals = [
            'order_count' => array_sum(array_column($rows, 'order_count')),
            'subtotal'    => array_sum(array_column($rows, 'subtotal')),
            'shipping'    => array_sum(array_column($rows, 'shipping')),
            'total'       => array_sum(array_column($rows, 'total')),
        ];

        return view('admin/reports/index', [
            'title'      => 'Laporan penjualan – Admin ShopEase',
            'activeMenu' => 'admin-reports',
            'rows'       => $rows,
            'totals'     => $totals,
            'from'       => $from,
            'to'         => $to,
            'status'     => $status,
            'error'      => $error,
        ]);
    }

    private function isDate(string $value): bool
    {
        $date = \DateTime::createFromFormat('Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value;
    }
}
