<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
/**
 * Bangun URL katalog dengan filter yang sedang aktif, lalu ganti sebagian nilainya.
 * Nilai kosong dibuang dari query string.
 */
$catalogUrl = static function (array $changes = []) use ($keyword, $category, $sort): string {
    $params = array_merge([
        'keyword'  => $keyword,
        'category' => $category['slug'] ?? '',
        'sort'     => $sort === 'terbaru' ? '' : $sort,
    ], $changes);

    $query = http_build_query(array_filter($params, static fn ($v) => $v !== '' && $v !== null));

    return site_url('catalog') . ($query !== '' ? '?' . $query : '');
};
?>

<section class="page-head">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= site_url('/') ?>">Beranda</a></li>
                <?php if ($category !== null): ?>
                    <li class="breadcrumb-item"><a href="<?= site_url('catalog') ?>">Katalog</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= esc($category['name']) ?></li>
                <?php else: ?>
                    <li class="breadcrumb-item active" aria-current="page">Katalog</li>
                <?php endif ?>
            </ol>
        </nav>
        <h1 class="page-head__title"><?= esc($category['name'] ?? 'Katalog produk') ?></h1>
        <?php if ($category !== null && ! empty($category['description'])): ?>
            <p class="page-head__lead"><?= esc($category['description']) ?></p>
        <?php endif ?>
    </div>
</section>

<section class="section section--catalog">
    <div class="container">
        <div class="row gy-4 gx-lg-5">
            <!-- Filter kategori -->
            <aside class="col-lg-3">
                <h2 class="filter-title">Kategori</h2>
                <ul class="filter-list">
                    <li>
                        <a class="filter-list__link<?= $category === null ? ' is-active' : '' ?>"
                           href="<?= $catalogUrl(['category' => '']) ?>"<?= $category === null ? ' aria-current="true"' : '' ?>>
                            Semua kategori
                        </a>
                    </li>
                    <?php foreach ($categories as $item): ?>
                        <?php $isActive = $category !== null && $category['id'] == $item['id']; ?>
                        <li>
                            <a class="filter-list__link<?= $isActive ? ' is-active' : '' ?>"
                               href="<?= $catalogUrl(['category' => $item['slug']]) ?>"<?= $isActive ? ' aria-current="true"' : '' ?>>
                                <span><?= esc($item['name']) ?></span>
                                <span class="filter-list__count"><?= esc((int) $item['product_count']) ?></span>
                            </a>
                        </li>
                    <?php endforeach ?>
                </ul>
            </aside>

            <div class="col-lg-9">
                <!-- Pencarian & urutan -->
                <form class="catalog-toolbar" action="<?= site_url('catalog') ?>" method="get" role="search">
                    <?php if ($category !== null): ?>
                        <input type="hidden" name="category" value="<?= esc($category['slug'], 'attr') ?>">
                    <?php endif ?>

                    <div class="catalog-toolbar__search">
                        <label for="keyword" class="visually-hidden">Cari produk</label>
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <input type="search" id="keyword" name="keyword" class="form-control"
                               value="<?= esc($keyword, 'attr') ?>" placeholder="Cari nama produk…">
                    </div>

                    <div class="catalog-toolbar__sort">
                        <label for="sort" class="form-label mb-0">Urutkan</label>
                        <select id="sort" name="sort" class="form-select" onchange="this.form.submit()">
                            <?php foreach ($sorts as $value => $label): ?>
                                <option value="<?= esc($value, 'attr') ?>"<?= $sort === $value ? ' selected' : '' ?>><?= esc($label) ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-brand">Cari</button>
                </form>

                <div class="catalog-summary">
                    <p class="mb-0">
                        <?php if ($keyword !== ''): ?>
                            <?= esc($total) ?> produk untuk “<strong><?= esc($keyword) ?></strong>”
                        <?php else: ?>
                            <?= esc($total) ?> produk
                        <?php endif ?>
                    </p>
                    <?php if ($keyword !== '' || $category !== null): ?>
                        <a class="link-more" href="<?= site_url('catalog') ?>">Hapus semua filter</a>
                    <?php endif ?>
                </div>

                <?php if ($products === []): ?>
                    <div class="empty-state">
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <p class="mb-3">Tidak ada produk yang cocok. Coba kata kunci lain atau pilih kategori yang berbeda.</p>
                        <a class="btn btn-ghost" href="<?= site_url('catalog') ?>">Lihat semua produk</a>
                    </div>
                <?php else: ?>
                    <div class="row g-3 g-md-4">
                        <?php foreach ($products as $product): ?>
                            <div class="col-6 col-md-4 col-xl-3">
                                <?= view('frontend/partials/product_card', ['product' => $product]) ?>
                            </div>
                        <?php endforeach ?>
                    </div>

                    <div class="mt-5">
                        <?= $pager->links('default', 'bootstrap') ?>
                    </div>
                <?php endif ?>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
