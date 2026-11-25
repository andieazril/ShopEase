<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * Admin\OrderController – pemrosesan pesanan, verifikasi pembayaran,
 * dan pengiriman (kerangka, HOL Praktikum 9–10)
 *
 * Route dilindungi filter auth:admin,operator.
 */
class OrderController extends BaseController
{
    protected $helpers = ['url', 'form', 'shopease'];

    public function index()
    {
        return view('admin/orders/index', [
            'title'      => 'Pesanan – Admin ShopEase',
            'activeMenu' => 'admin-orders',
        ]);
    }
}
