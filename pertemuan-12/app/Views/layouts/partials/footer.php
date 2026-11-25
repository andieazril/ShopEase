<footer class="site-footer">
    <div class="container">
        <div class="row gy-4">
            <div class="col-lg-4">
                <a class="brand brand--light" href="<?= site_url('/') ?>">
                    <span class="brand__mark" aria-hidden="true"><i class="bi bi-bag-heart-fill"></i></span>
                    <span class="brand__name">ShopEase</span>
                </a>
                <p class="site-footer__about">Toko online untuk kebutuhan harian. Pembayaran dengan transfer bank, dan bukti bayar diperiksa langsung oleh admin toko.</p>
            </div>

            <div class="col-6 col-lg-2 offset-lg-1">
                <h2 class="site-footer__title">Belanja</h2>
                <ul class="list-unstyled site-footer__links">
                    <li><a href="<?= site_url('catalog') ?>">Katalog produk</a></li>
                    <li><a href="<?= site_url('/') ?>#kategori">Kategori</a></li>
                    <li><a href="<?= site_url('cart') ?>">Keranjang</a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-2">
                <h2 class="site-footer__title">Akun</h2>
                <ul class="list-unstyled site-footer__links">
                    <li><a href="<?= site_url('login') ?>">Masuk</a></li>
                    <li><a href="<?= site_url('register') ?>">Daftar</a></li>
                    <li><a href="<?= site_url('orders') ?>">Riwayat pesanan</a></li>
                </ul>
            </div>

            <div class="col-lg-3">
                <h2 class="site-footer__title">Bantuan</h2>
                <ul class="list-unstyled site-footer__links">
                    <li><i class="bi bi-envelope me-2" aria-hidden="true"></i>halo@shopease.test</li>
                    <li><i class="bi bi-clock me-2" aria-hidden="true"></i>Senin–Sabtu, 08.00–17.00 WIB</li>
                </ul>
            </div>
        </div>

        <div class="site-footer__bottom">
            <span>&copy; <?= date('Y') ?> ShopEase. Proyek pembelajaran mata kuliah Web Framework.</span>
            <span>Dibangun dengan CodeIgniter <?= esc(\CodeIgniter\CodeIgniter::CI_VERSION) ?></span>
        </div>
    </div>
</footer>
