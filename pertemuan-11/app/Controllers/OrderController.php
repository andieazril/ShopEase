<?php

namespace App\Controllers;

/**
 * OrderController – riwayat & detail pesanan customer
 * (kerangka, HOL Praktikum 9–10)
 *
 * Route dilindungi filter auth:customer.
 */
class OrderController extends BaseController
{
    protected $helpers = ['url', 'form', 'shopease'];

    public function index()
    {
        return view('frontend/orders/index', [
            'title'      => 'Pesanan saya – ShopEase',
            'activeMenu' => 'orders',
        ]);
    }

    public function detail(int $id)
    {
        // TODO Praktikum 9: ambil pesanan milik user yang login saja (PRD BR-006).
        return view('frontend/orders/detail', [
            'title'      => 'Detail pesanan – ShopEase',
            'activeMenu' => 'orders',
            'orderId'    => $id,
        ]);
    }
}
