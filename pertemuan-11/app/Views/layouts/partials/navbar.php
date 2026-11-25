<?php
/**
 * Navbar ShopEase.
 * Variabel opsional: $activeMenu ('home', 'catalog').
 * Status login dibaca dari session (nama kunci mengikuti HOL):
 * logged_in, name, role. Jumlah item keranjang: cart_count.
 */
$activeMenu = $activeMenu ?? (uri_string() === '' ? 'home' : '');
$isLoggedIn = (bool) session()->get('logged_in');
$userName   = (string) session()->get('name');
$userRole   = (string) session()->get('role');
$cartCount  = (int) session()->get('cart_count');

$menu = [
    'home'    => ['label' => 'Beranda', 'url' => site_url('/')],
    'catalog' => ['label' => 'Katalog', 'url' => site_url('catalog')],
    'kategori' => ['label' => 'Kategori', 'url' => site_url('/') . '#kategori'],
    'terlaris' => ['label' => 'Terlaris', 'url' => site_url('/') . '#produk-terlaris'],
];
?>
<header class="site-header sticky-top">
    <nav class="navbar navbar-expand-lg" aria-label="Navigasi utama">
        <div class="container">
            <a class="navbar-brand brand" href="<?= site_url('/') ?>">
                <span class="brand__mark" aria-hidden="true"><i class="bi bi-bag-heart-fill"></i></span>
                <span class="brand__name">ShopEase</span>
            </a>

            <div class="d-flex align-items-center gap-2 order-lg-3">
                <a class="btn btn-icon position-relative" href="<?= site_url('cart') ?>" aria-label="Keranjang belanja<?= $cartCount > 0 ? ', ' . $cartCount . ' barang' : '' ?>">
                    <i class="bi bi-cart3" aria-hidden="true"></i>
                    <?php if ($cartCount > 0): ?>
                        <span class="cart-badge"><?= $cartCount ?></span>
                    <?php endif ?>
                </a>

                <?php if ($isLoggedIn): ?>
                    <div class="dropdown">
                        <button class="btn btn-ghost dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1" aria-hidden="true"></i><?= esc($userName ?: 'Akun saya') ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if (in_array($userRole, ['admin', 'operator'], true)): ?>
                                <li><a class="dropdown-item" href="<?= site_url('admin/dashboard') ?>">Dashboard admin</a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item" href="<?= site_url('orders') ?>">Pesanan saya</a></li>
                            <?php endif ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <?php /* Logout lewat POST + token CSRF agar tidak bisa dipicu dari link luar. */ ?>
                                <form action="<?= site_url('logout') ?>" method="post">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="dropdown-item">Keluar</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a class="btn btn-ghost d-none d-sm-inline-flex" href="<?= site_url('login') ?>">Masuk</a>
                    <a class="btn btn-brand d-none d-sm-inline-flex" href="<?= site_url('register') ?>">Daftar</a>
                <?php endif ?>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuUtama" aria-controls="menuUtama" aria-expanded="false" aria-label="Buka menu">
                    <i class="bi bi-list" aria-hidden="true"></i>
                </button>
            </div>

            <div class="collapse navbar-collapse order-lg-2" id="menuUtama">
                <ul class="navbar-nav mx-lg-auto">
                    <?php foreach ($menu as $key => $item): ?>
                        <li class="nav-item">
                            <a class="nav-link<?= $activeMenu === $key ? ' active' : '' ?>"<?= $activeMenu === $key ? ' aria-current="page"' : '' ?> href="<?= $item['url'] ?>"><?= esc($item['label']) ?></a>
                        </li>
                    <?php endforeach ?>
                </ul>

                <?php if (! $isLoggedIn): ?>
                    <div class="d-flex d-sm-none gap-2 pb-3">
                        <a class="btn btn-ghost flex-fill" href="<?= site_url('login') ?>">Masuk</a>
                        <a class="btn btn-brand flex-fill" href="<?= site_url('register') ?>">Daftar</a>
                    </div>
                <?php endif ?>
            </div>
        </div>
    </nav>
</header>
