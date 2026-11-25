<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<section class="section">
    <div class="container">
        <h1 class="section__title mb-4">Keranjang belanja</h1>

        <?php if ($cart['items'] === []): ?>
            <div class="empty-state">
                <i class="bi bi-cart3" aria-hidden="true"></i>
                <p class="mb-3">Keranjang Anda masih kosong.</p>
                <a class="btn btn-brand" href="<?= site_url('catalog') ?>">Belanja di katalog</a>
            </div>
        <?php else: ?>
            <div class="row gy-4 gx-lg-5">
                <div class="col-lg-8">
                    <div class="table-responsive admin-table">
                        <table class="table align-middle mb-0 cart-table">
                            <thead>
                                <tr>
                                    <th scope="col">Produk</th>
                                    <th scope="col" class="text-end">Harga satuan</th>
                                    <th scope="col" class="text-center">Jumlah</th>
                                    <th scope="col" class="text-end">Subtotal</th>
                                    <th scope="col"><span class="visually-hidden">Aksi</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart['items'] as $item): ?>
                                    <?php $imageUrl = product_image_url($item['image']); ?>
                                    <tr<?= $item['problem'] !== null ? ' class="cart-row--problem"' : '' ?>>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <?php if ($imageUrl !== null): ?>
                                                    <img class="cart-thumb" src="<?= esc($imageUrl, 'attr') ?>" alt="">
                                                <?php endif ?>
                                                <div>
                                                    <a class="cart-name" href="<?= site_url('product/' . (int) $item['product_id']) ?>"><?= esc($item['name']) ?></a>
                                                    <div class="small text-secondary"><?= esc($item['category_name']) ?></div>
                                                    <?php if ($item['problem'] !== null): ?>
                                                        <div class="small text-danger fw-semibold mt-1"><?= esc($item['problem']) ?></div>
                                                    <?php endif ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end text-nowrap" data-label="Harga satuan"><?= format_rupiah($item['price']) ?></td>
                                        <td class="text-center" data-label="Jumlah">
                                            <form class="cart-qty" action="<?= site_url('cart/update/' . (int) $item['id']) ?>" method="post">
                                                <?= csrf_field() ?>
                                                <label for="qty-<?= (int) $item['id'] ?>" class="visually-hidden">Jumlah <?= esc($item['name']) ?></label>
                                                <input type="number" id="qty-<?= (int) $item['id'] ?>" name="quantity" class="form-control form-control-sm"
                                                       value="<?= (int) $item['quantity'] ?>" min="1" max="<?= max(1, (int) $item['stock']) ?>" required>
                                                <button type="submit" class="btn btn-ghost btn-sm">Ubah</button>
                                            </form>
                                        </td>
                                        <td class="text-end text-nowrap fw-semibold" data-label="Subtotal"><?= format_rupiah($item['subtotal']) ?></td>
                                        <td class="text-end cart-remove">
                                            <form action="<?= site_url('cart/remove/' . (int) $item['id']) ?>" method="post">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-link text-danger btn-sm" aria-label="Hapus <?= esc($item['name'], 'attr') ?>">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                    <a class="link-more d-inline-block mt-3" href="<?= site_url('catalog') ?>">Lanjut belanja</a>
                </div>

                <div class="col-lg-4">
                    <div class="summary-card">
                        <h2 class="summary-card__title">Ringkasan</h2>
                        <dl class="summary-list">
                            <div><dt>Jumlah barang</dt><dd><?= esc($cart['count']) ?></dd></div>
                            <div class="summary-list__total"><dt>Total belanja</dt><dd><?= format_rupiah($cart['total']) ?></dd></div>
                        </dl>
                        <p class="small text-secondary">Ongkos kirim dihitung di halaman checkout.</p>

                        <?php if ($cart['hasProblem']): ?>
                            <p class="small text-danger fw-semibold">Perbaiki item yang ditandai merah sebelum checkout.</p>
                            <button type="button" class="btn btn-brand btn-lg w-100" disabled>Checkout</button>
                        <?php else: ?>
                            <a class="btn btn-brand btn-lg w-100" href="<?= site_url('checkout') ?>">Checkout</a>
                        <?php endif ?>
                    </div>
                </div>
            </div>
        <?php endif ?>
    </div>
</section>

<?= $this->endSection() ?>
