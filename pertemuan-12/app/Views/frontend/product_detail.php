<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$style     = category_style($product['category_slug']);
$imageUrl  = product_image_url($product['image']);
$stock     = (int) $product['stock'];
$isSoldOut = $stock < 1;
$isLoggedIn = (bool) session('logged_in');
$isCustomer = session('role') === 'customer';
?>

<section class="section section--detail">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= site_url('/') ?>">Beranda</a></li>
                <li class="breadcrumb-item"><a href="<?= site_url('catalog') ?>">Katalog</a></li>
                <li class="breadcrumb-item"><a href="<?= site_url('catalog?category=' . rawurlencode($product['category_slug'])) ?>"><?= esc($product['category_name']) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= esc($product['name']) ?></li>
            </ol>
        </nav>

        <div class="row gy-4 gx-lg-5 align-items-start">
            <div class="col-md-6">
                <div class="detail-media<?= $isSoldOut ? ' detail-media--soldout' : '' ?>" style="--tone: <?= esc($style['tone'], 'attr') ?>">
                    <?php if ($imageUrl !== null): ?>
                        <img src="<?= esc($imageUrl, 'attr') ?>" alt="<?= esc($product['name'], 'attr') ?>">
                    <?php else: ?>
                        <i class="bi <?= esc($style['icon'], 'attr') ?>" aria-hidden="true"></i>
                    <?php endif ?>
                </div>
            </div>

            <div class="col-md-6">
                <a class="detail-category" href="<?= site_url('catalog?category=' . rawurlencode($product['category_slug'])) ?>"><?= esc($product['category_name']) ?></a>
                <h1 class="detail-title"><?= esc($product['name']) ?></h1>
                <p class="detail-price"><?= format_rupiah($product['price']) ?></p>

                <p class="detail-stock<?= $isSoldOut ? ' detail-stock--out' : ($stock <= 5 ? ' detail-stock--low' : '') ?>">
                    <i class="bi <?= $isSoldOut ? 'bi-x-circle' : 'bi-check-circle' ?>" aria-hidden="true"></i>
                    <?php if ($isSoldOut): ?>
                        Stok habis
                    <?php elseif ($stock <= 5): ?>
                        Sisa <?= esc($stock) ?> lagi
                    <?php else: ?>
                        Stok tersedia: <?= esc($stock) ?>
                    <?php endif ?>
                </p>

                <?php if (! empty($product['description'])): ?>
                    <div class="detail-desc">
                        <h2 class="detail-desc__title">Deskripsi</h2>
                        <p><?= nl2br(esc($product['description'])) ?></p>
                    </div>
                <?php endif ?>

                <form class="detail-buy" action="<?= site_url('cart/add') ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">

                    <div class="detail-buy__qty">
                        <label for="quantity" class="form-label">Jumlah</label>
                        <input type="number" id="quantity" name="quantity" class="form-control"
                               value="1" min="1" max="<?= max(1, $stock) ?>" step="1" required<?= $isSoldOut ? ' disabled' : '' ?>>
                    </div>

                    <button type="submit" class="btn btn-brand btn-lg detail-buy__btn"<?= $isSoldOut ? ' disabled' : '' ?>>
                        <i class="bi bi-cart-plus me-2" aria-hidden="true"></i>Tambah ke keranjang
                    </button>
                </form>

                <?php if (! $isSoldOut && ! $isLoggedIn): ?>
                    <p class="detail-note">Anda perlu <a href="<?= site_url('login') ?>">masuk</a> sebagai customer untuk menambahkan produk ke keranjang.</p>
                <?php elseif (! $isSoldOut && ! $isCustomer): ?>
                    <p class="detail-note">Keranjang hanya untuk akun customer.</p>
                <?php endif ?>
            </div>
        </div>
    </div>
</section>

<?php if ($related !== []): ?>
    <section class="section section--tinted">
        <div class="container">
            <div class="section__head">
                <h2 class="section__title">Produk lain di <?= esc($product['category_name']) ?></h2>
                <a class="link-more" href="<?= site_url('catalog?category=' . rawurlencode($product['category_slug'])) ?>">Lihat semua</a>
            </div>
            <div class="row g-3 g-md-4">
                <?php foreach ($related as $item): ?>
                    <div class="col-6 col-md-4 col-xl-3">
                        <?= view('frontend/partials/product_card', ['product' => $item]) ?>
                    </div>
                <?php endforeach ?>
            </div>
        </div>
    </section>
<?php endif ?>

<?= $this->endSection() ?>
