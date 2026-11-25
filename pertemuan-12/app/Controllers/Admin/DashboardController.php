<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\OrderModel;
use App\Models\PaymentModel;
use App\Models\ProductModel;
use App\Models\UserModel;

/**
 * Dashboard admin/operator: ringkasan toko, grafik penjualan,
 * transaksi terbaru, dan produk terlaris.
 */
class DashboardController extends BaseController
{
    protected $helpers = ['url', 'form', 'shopease'];

    public function index()
    {
        $orderModel = new OrderModel();

        $stats = [
            'products'  => (new ProductModel())->countAllResults(),
            'customers' => (new UserModel())->where('role', 'customer')->countAllResults(),
            'orders'    => $orderModel->countAllResults(),
            'sales'     => $orderModel->salesTotal(),
            'pending'   => (new OrderModel())->whereIn('order_status', OrderModel::CANCELLABLE_STATUSES)->countAllResults(),
            'waiting'   => (new PaymentModel())->countWaiting(),
        ];

        return view('admin/dashboard', [
            'title'       => 'Dashboard – ShopEase',
            'activeMenu'  => 'dashboard',
            'stats'       => $stats,
            'chart'       => (new OrderModel())->dailySales(14),
            'recent'      => (new OrderModel())->withDetails()->orderBy('orders.created_at', 'DESC')->findAll(5),
            'bestSellers' => (new ProductModel())->getBestSellers(5),
        ]);
    }
}
