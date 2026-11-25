<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- ============ BANNER UTAMA (HERO) ============ -->
<section class="hero">
    <div class="container">
        <div class="row align-items-center gy-5">
            <div class="col-lg-6">
                <h1 class="hero__title">Belanja kebutuhan harian, bayar dengan transfer, pantau sampai paket tiba.</h1>
                <p class="hero__lead">Harga dan stok di ShopEase selalu diperiksa ulang saat checkout, jadi total yang Anda bayar sama dengan yang tertera di pesanan.</p>

                <form class="hero-search" action="<?= site_url('catalog') ?>" method="get" role="search">
                    <label for="cariProduk" class="visually-hidden">Cari produk</label>
                    <i class="bi bi-search hero-search__icon" aria-hidden="true"></i>
                    <input type="search" id="cariProduk" name="keyword" class="form-control hero-search__input" placeholder="Cari headphone, tumbler, kaos…" autocomplete="off">
                    <button type="submit" class="btn btn-brand hero-search__btn">Cari produk</button>
                </form>

                <div class="hero__quick">
                    <span class="hero__quick-label">Kategori:</span>
                    <?php foreach (array_slice($categories, 0, 3) as $category): ?>
                        <a class="chip" href="<?= site_url('catalog?category=' . rawurlencode($category['slug'])) ?>"><?= esc($category['name']) ?></a>
                    <?php endforeach ?>
                    <a class="link-more ms-1" href="<?= site_url('catalog') ?>">Lihat katalog</a>
                </div>

                <dl class="hero__facts">
                    <div><dt><?= esc($stats['products']) ?></dt><dd>produk tersedia</dd></div>
                    <div><dt><?= esc($stats['categories']) ?></dt><dd>kategori</dd></div>
                    <div><dt>Transfer bank</dt><dd>dengan bukti bayar</dd></div>
                </dl>
            </div>

            <div class="col-lg-6">
                <div class="receipt-stage" aria-label="Contoh struk pesanan ShopEase">
                    <div class="receipt-slot" aria-hidden="true"></div>
                    <article class="receipt">
                        <header class="receipt__head">
                            <strong class="receipt__brand">SHOPEASE</strong>
                            <span>Struk pesanan</span>
                        </header>

                        <dl class="receipt__meta">
                            <div><dt>No.</dt><dd><?= esc($receipt['order_number']) ?></dd></div>
                            <div><dt>Tanggal</dt><dd><?= esc($receipt['date']) ?></dd></div>
                        </dl>

                        <ul class="receipt__items">
                            <?php foreach ($receipt['items'] as $item): ?>
                                <li>
                                    <span class="receipt__name"><?= esc($item['name']) ?></span>
                                    <span class="receipt__calc"><?= esc($item['quantity']) ?> × <?= format_rupiah($item['price']) ?></span>
                                    <span class="receipt__amount"><?= format_rupiah($item['subtotal']) ?></span>
                                </li>
                            <?php endforeach ?>
                        </ul>

                        <dl class="receipt__sum">
                            <div><dt>Subtotal</dt><dd><?= format_rupiah($receipt['subtotal']) ?></dd></div>
                            <div><dt>Ongkos kirim</dt><dd><?= format_rupiah($receipt['shipping_cost']) ?></dd></div>
                            <div class="receipt__total"><dt>Total</dt><dd><?= format_rupiah($receipt['total']) ?></dd></div>
                        </dl>

                        <p class="receipt__status"><i class="bi bi-hourglass-split" aria-hidden="true"></i> <?= esc($receipt['status']) ?></p>
                        <p class="receipt__foot">Total dihitung ulang oleh sistem saat checkout.</p>
                    </article>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============ KATEGORI PRODUK ============ -->
<section id="kategori" class="section">
    <div class="container">
        <div class="section__head">
            <h2 class="section__title">Belanja per kategori</h2>
            <a class="link-more" href="<?= site_url('catalog') ?>">Lihat semua produk</a>
        </div>

        <ul class="category-list">
            <?php foreach ($categories as $category): ?>
                <?php $style = category_style($category['slug']); ?>
                <li>
                    <a class="category" href="<?= site_url('catalog?category=' . rawurlencode($category['slug'])) ?>" style="--tone: <?= esc($style['tone'], 'attr') ?>">
                        <span class="category__icon" aria-hidden="true"><i class="bi <?= esc($style['icon'], 'attr') ?>"></i></span>
                        <span class="category__name"><?= esc($category['name']) ?></span>
                        <span class="category__count"><?= esc((int) $category['product_count']) ?> produk</span>
                    </a>
                </li>
            <?php endforeach ?>
        </ul>
    </div>
</section>

<!-- ============ BANNER PROMOSI ============ -->
<?php if ($promoBanners !== []): ?>
<section class="section section--flush" aria-label="Promosi">
    <div class="container">
        <div class="row g-3 g-md-4">
            <?php foreach ($promoBanners as $i => $promo): ?>
                <?php $style = category_style($promo['slug']); ?>
                <div class="col-md-6">
                    <a class="promo promo--<?= $i === 0 ? 'cobalt' : 'mango' ?>" href="<?= site_url('catalog?category=' . rawurlencode($promo['slug'])) ?>">
                        <span class="promo__text">
                            <strong class="promo__title"><?= esc($promo['name']) ?> mulai <?= format_rupiah($promo['min_price']) ?></strong>
                            <span class="promo__sub"><?= esc((int) $promo['product_count']) ?> pilihan produk siap dikirim</span>
                            <span class="promo__cta">Belanja <?= esc(strtolower($promo['name'])) ?></span>
                        </span>
                        <i class="bi <?= esc($style['icon'], 'attr') ?> promo__icon" aria-hidden="true"></i>
                    </a>
                </div>
            <?php endforeach ?>
        </div>
    </div>
</section>
<?php endif ?>

<!-- ============ PRODUK TERBARU ============ -->
<section id="produk-terbaru" class="section">
    <div class="container">
        <div class="section__head">
            <h2 class="section__title">Produk terbaru</h2>
            <a class="link-more" href="<?= site_url('catalog') ?>">Buka katalog</a>
        </div>

        <?php if ($latestProducts === []): ?>
            <div class="empty-state">
                <i class="bi bi-box-seam" aria-hidden="true"></i>
                <p>Belum ada produk aktif. Tambahkan produk dari halaman admin agar tampil di sini.</p>
            </div>
        <?php else: ?>
            <div class="row g-3 g-md-4">
                <?php foreach ($latestProducts as $product): ?>
                    <div class="col-6 col-md-4 col-xl-3">
                        <?= view('frontend/partials/product_card', ['product' => $product]) ?>
                    </div>
                <?php endforeach ?>
            </div>
        <?php endif ?>
    </div>
</section>

<!-- ============ PRODUK TERLARIS ============ -->
<section id="produk-terlaris" class="section section--tinted">
    <div class="container">
        <div class="section__head">
            <h2 class="section__title">Produk terlaris</h2>
            <span class="section__note">Berdasarkan jumlah terjual</span>
        </div>

        <?php if ($bestSellers === []): ?>
            <div class="empty-state">
                <i class="bi bi-graph-up" aria-hidden="true"></i>
                <p>Belum ada penjualan. Produk terlaris muncul setelah ada pesanan yang pembayarannya diverifikasi.</p>
            </div>
        <?php else: ?>
            <div class="row g-3 g-md-4">
                <?php foreach ($bestSellers as $product): ?>
                    <div class="col-6 col-md-4 col-xl-3">
                        <?= view('frontend/partials/product_card', ['product' => $product, 'showSold' => true]) ?>
                    </div>
                <?php endforeach ?>
            </div>
        <?php endif ?>
    </div>
</section>

<!-- ============ CARA BELANJA ============ -->
<section id="cara-belanja" class="section">
    <div class="container">
        <div class="section__head section__head--stack">
            <h2 class="section__title">Cara belanja di ShopEase</h2>
            <p class="section__lead">Empat langkah dari memilih barang sampai paket diterima. Status pembayaran dan status pengiriman bisa Anda lihat terpisah di halaman pesanan.</p>
        </div>

        <ol class="steps">
            <?php foreach ($steps as $step): ?>
                <li class="step">
                    <span class="step__icon" aria-hidden="true"><i class="bi <?= esc($step['icon'], 'attr') ?>"></i></span>
                    <h3 class="step__title"><?= esc($step['title']) ?></h3>
                    <p class="step__text"><?= esc($step['text']) ?></p>
                </li>
            <?php endforeach ?>
        </ol>
    </div>
</section>

<!-- ============ AJAKAN DAFTAR ============ -->
<section class="section section--flush section--last">
    <div class="container">
        <div class="cta">
            <div>
                <h2 class="cta__title">Simpan alamat sekali, checkout lebih cepat.</h2>
                <p class="cta__text">Buat akun dengan nama, email, dan password. Riwayat pesanan dan bukti bayar Anda tersimpan di satu tempat.</p>
            </div>
            <div class="cta__actions">
                <a class="btn btn-mango btn-lg" href="<?= site_url('register') ?>">Buat akun</a>
                <a class="btn btn-outline-light btn-lg" href="<?= site_url('login') ?>">Saya sudah punya akun</a>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
