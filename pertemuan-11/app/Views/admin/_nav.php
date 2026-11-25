<?php
/**
 * Menu halaman admin. Menu Produk hanya untuk role admin.
 */
$activeMenu = $activeMenu ?? '';
$items      = ['dashboard' => ['Dashboard', 'admin/dashboard', 'bi-speedometer2']];

if (session('role') === 'admin') {
    $items['admin-products'] = ['Produk', 'admin/products', 'bi-box-seam'];
}

$items['admin-orders'] = ['Pesanan', 'admin/orders', 'bi-receipt'];
?>
<nav class="admin-nav" aria-label="Menu admin">
    <?php foreach ($items as $key => [$label, $url, $icon]): ?>
        <a class="admin-nav__link<?= $activeMenu === $key ? ' is-active' : '' ?>" href="<?= site_url($url) ?>"<?= $activeMenu === $key ? ' aria-current="page"' : '' ?>>
            <i class="bi <?= $icon ?>" aria-hidden="true"></i><?= esc($label) ?>
        </a>
    <?php endforeach ?>
</nav>
