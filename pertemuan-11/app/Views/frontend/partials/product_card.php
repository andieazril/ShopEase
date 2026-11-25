<?php
/**
 * Kartu produk – dipakai di section produk terbaru dan produk terlaris.
 *
 * @var array<string, mixed> $product  Data produk (dengan category_name, category_slug, sold)
 * @var bool                 $showSold Tampilkan jumlah terjual (untuk section terlaris)
 */
$showSold   = $showSold ?? false;
$style      = category_style($product['category_slug'] ?? null);
$imageUrl   = product_image_url($product['image'] ?? null);
$stock      = (int) $product['stock'];
$isSoldOut  = $stock < 1;
$isLowStock = $stock > 0 && $stock <= 5;
$detailUrl  = site_url('product/' . (int) $product['id']);
?>
<article class="product<?= $isSoldOut ? ' product--soldout' : '' ?>">
    <a class="product__media" href="<?= $detailUrl ?>" style="--tone: <?= esc($style['tone'], 'attr') ?>" tabindex="-1" aria-hidden="true">
        <?php if ($imageUrl !== null): ?>
            <img src="<?= esc($imageUrl, 'attr') ?>" alt="" loading="lazy">
        <?php else: ?>
            <i class="bi <?= esc($style['icon'], 'attr') ?>"></i>
        <?php endif ?>
        <?php if ($isSoldOut): ?>
            <span class="product__flag">Stok habis</span>
        <?php elseif ($showSold && ! empty($product['sold'])): ?>
            <span class="product__flag product__flag--sold"><?= esc((int) $product['sold']) ?> terjual</span>
        <?php endif ?>
    </a>

    <div class="product__body">
        <span class="product__category"><?= esc($product['category_name'] ?? '') ?></span>
        <h3 class="product__name"><a href="<?= $detailUrl ?>"><?= esc($product['name']) ?></a></h3>
        <p class="product__price"><?= format_rupiah($product['price']) ?></p>
        <p class="product__stock<?= $isLowStock ? ' product__stock--low' : '' ?>">
            <?php if ($isSoldOut): ?>
                Sedang kosong
            <?php elseif ($isLowStock): ?>
                Sisa <?= esc($stock) ?>
            <?php else: ?>
                Stok <?= esc($stock) ?>
            <?php endif ?>
        </p>
    </div>
</article>
