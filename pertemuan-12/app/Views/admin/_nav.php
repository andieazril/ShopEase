<?php
/**
 * Menu halaman admin. Kategori, pelanggan, dan laporan hanya untuk admin.
 */
$activeMenu = $activeMenu ?? '';
$isAdmin    = session('role') === 'admin';

$items = [
    'dashboard'      => ['Dashboard', 'admin/dashboard', 'bi-speedometer2', true],
    'admin-orders'   => ['Pesanan', 'admin/orders', 'bi-receipt', true],
    'admin-products' => ['Produk', 'admin/products', 'bi-box-seam', true],
    'admin-categories' => ['Kategori', 'admin/categories', 'bi-tags', $isAdmin],
    'admin-customers'  => ['Pelanggan', 'admin/customers', 'bi-people', $isAdmin],
    'admin-reports'    => ['Laporan', 'admin/reports', 'bi-graph-up', $isAdmin],
];
?>
<nav class="admin-nav" aria-label="Menu admin">
    <?php foreach ($items as $key => [$label, $url, $icon, $visible]): ?>
        <?php if (! $visible) { continue; } ?>
        <a class="admin-nav__link<?= $activeMenu === $key ? ' is-active' : '' ?>" href="<?= site_url($url) ?>"<?= $activeMenu === $key ? ' aria-current="page"' : '' ?>>
            <i class="bi <?= $icon ?>" aria-hidden="true"></i><?= esc($label) ?>
        </a>
    <?php endforeach ?>
</nav>
