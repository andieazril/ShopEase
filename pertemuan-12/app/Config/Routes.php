<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// --------------------------------------------------------------------
// Halaman publik
// --------------------------------------------------------------------
$routes->get('/', 'HomeController::index');
$routes->get('catalog', 'ProductController::catalog');              // ?keyword=&category=&sort=&page=
$routes->get('product/(:num)', 'ProductController::detail/$1');

// --------------------------------------------------------------------
// Autentikasi
// --------------------------------------------------------------------
$routes->get('register', 'AuthController::register');
$routes->post('register', 'AuthController::store');
$routes->get('login', 'AuthController::login');
$routes->post('login', 'AuthController::attempt');
$routes->post('logout', 'AuthController::logout', ['filter' => 'auth']);

// --------------------------------------------------------------------
// Customer – wajib login sebagai customer
// --------------------------------------------------------------------
$routes->group('', ['filter' => 'auth:customer'], static function ($routes) {
    $routes->get('cart', 'CartController::index');
    $routes->post('cart/add', 'CartController::add');
    $routes->post('cart/update/(:num)', 'CartController::update/$1');
    $routes->post('cart/remove/(:num)', 'CartController::remove/$1');

    $routes->get('checkout', 'CheckoutController::index');
    $routes->post('checkout', 'CheckoutController::store');

    $routes->get('orders', 'OrderController::index');
    $routes->get('orders/(:num)', 'OrderController::detail/$1');
    $routes->post('orders/(:num)/payment', 'OrderController::uploadPayment/$1');
    $routes->get('orders/(:num)/proof', 'OrderController::proof/$1');
});

// --------------------------------------------------------------------
// Admin & operator
// --------------------------------------------------------------------
$routes->group('admin', ['filter' => 'auth:admin,operator'], static function ($routes) {
    $routes->get('dashboard', 'Admin\DashboardController::index');

    // Pesanan: lihat, proses pengiriman
    $routes->get('orders', 'Admin\OrderController::index');
    $routes->get('orders/(:num)', 'Admin\OrderController::detail/$1');
    $routes->get('orders/(:num)/proof', 'Admin\OrderController::proof/$1');
    $routes->post('orders/(:num)/ship', 'Admin\OrderController::ship/$1');
    $routes->post('orders/(:num)/complete', 'Admin\OrderController::complete/$1');

    // Produk: lihat daftar & perbarui stok
    $routes->get('products', 'Admin\ProductController::index');
    $routes->post('products/(:num)/stock', 'Admin\ProductController::updateStock/$1');
});

// --------------------------------------------------------------------
// Khusus admin
// --------------------------------------------------------------------
$routes->group('admin', ['filter' => 'auth:admin'], static function ($routes) {
    // Kategori
    $routes->get('categories', 'Admin\CategoryController::index');
    $routes->get('categories/create', 'Admin\CategoryController::create');
    $routes->post('categories/store', 'Admin\CategoryController::store');
    $routes->get('categories/(:num)/edit', 'Admin\CategoryController::edit/$1');
    $routes->post('categories/(:num)/update', 'Admin\CategoryController::update/$1');
    $routes->post('categories/(:num)/delete', 'Admin\CategoryController::delete/$1');

    // Produk
    $routes->get('products/create', 'Admin\ProductController::create');
    $routes->post('products/store', 'Admin\ProductController::store');
    $routes->get('products/(:num)/edit', 'Admin\ProductController::edit/$1');
    $routes->post('products/(:num)/update', 'Admin\ProductController::update/$1');
    $routes->post('products/(:num)/delete', 'Admin\ProductController::delete/$1');

    // Verifikasi pembayaran & pembatalan pesanan
    $routes->post('orders/(:num)/verify', 'Admin\OrderController::verify/$1');
    $routes->post('orders/(:num)/cancel', 'Admin\OrderController::cancel/$1');

    // Pelanggan & laporan
    $routes->get('customers', 'Admin\CustomerController::index');
    $routes->get('reports', 'Admin\ReportController::index');
});
