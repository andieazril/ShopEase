<?php

namespace App\Controllers;

/**
 * CheckoutController – proses checkout (kerangka, HOL Praktikum 9)
 *
 * Route dilindungi filter auth:customer.
 */
class CheckoutController extends BaseController
{
    protected $helpers = ['url', 'form', 'shopease'];

    public function index()
    {
        return view('frontend/checkout', [
            'title'      => 'Checkout – ShopEase',
            'activeMenu' => 'cart',
        ]);
    }
}
