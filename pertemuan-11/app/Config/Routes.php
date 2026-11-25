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
// Authentication (HOL Praktikum 6)
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
    $routes->get('checkout', 'CheckoutController::index');
    $routes->get('orders', 'OrderController::index');
    $routes->get('orders/(:num)', 'OrderController::detail/$1');
});

// --------------------------------------------------------------------
// Admin & operator
// --------------------------------------------------------------------
$routes->group('admin', ['filter' => 'auth:admin,operator'], static function ($routes) {
    $routes->get('dashboard', 'Admin\DashboardController::index');
    $routes->get('orders', 'Admin\OrderController::index');
});

// Khusus admin (operator tidak boleh mengelola produk)
$routes->group('admin', ['filter' => 'auth:admin'], static function ($routes) {
    $routes->get('products', 'Admin\ProductController::index');
});
