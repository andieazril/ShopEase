<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php $errors = session('errors') ?? []; ?>

<section class="section">
    <div class="container">
        <?= $this->include('admin/_nav') ?>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= site_url('admin/orders') ?>">Pesanan</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= esc($order['order_number']) ?></li>
            </ol>
        </nav>

        <div class="section__head">
            <div>
                <h1 class="section__title">Pesanan <?= esc($order['order_number']) ?></h1>
                <p class="text-secondary mb-0">
                    <?= esc($order['customer_name']) ?> (<?= esc($order['customer_email']) ?>) · dibuat <?= format_date($order['created_at']) ?>
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <span>Pembayaran: <?= status_badge(payment_status_label($order['payment_status'])) ?></span>
                <span>Pesanan: <?= status_badge(order_status_label($order['order_status'])) ?></span>
            </div>
        </div>

        <div class="row gy-4 gx-lg-5">
            <div class="col-lg-7">
                <div class="table-responsive admin-table">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Produk</th>
                                <th scope="col" class="text-end">Harga</th>
                                <th scope="col" class="text-center">Jumlah</th>
                                <th scope="col" class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?= esc($item['product_name']) ?></td>
                                    <td class="text-end text-nowrap"><?= format_rupiah($item['price']) ?></td>
                                    <td class="text-center"><?= (int) $item['quantity'] ?></td>
                                    <td class="text-end text-nowrap"><?= format_rupiah($item['subtotal']) ?></td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                        <tfoot>
                            <tr><td colspan="3" class="text-end">Subtotal</td><td class="text-end text-nowrap"><?= format_rupiah($order['subtotal']) ?></td></tr>
                            <tr><td colspan="3" class="text-end">Ongkos kirim</td><td class="text-end text-nowrap"><?= format_rupiah($order['shipping_cost']) ?></td></tr>
                            <tr class="fw-bold"><td colspan="3" class="text-end">Total</td><td class="text-end text-nowrap"><?= format_rupiah($order['total']) ?></td></tr>
                        </tfoot>
                    </table>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <div class="info-card">
                            <h2 class="info-card__title">Alamat pengiriman</h2>
                            <?php if ($address !== null): ?>
                                <p class="mb-0"><strong><?= esc($address['recipient_name']) ?></strong> · <?= esc($address['phone']) ?><br>
                                <?= esc($address['address']) ?>, <?= esc($address['city']) ?>, <?= esc($address['province']) ?> <?= esc($address['postal_code']) ?></p>
                            <?php else: ?>
                                <p class="mb-0 text-secondary">-</p>
                            <?php endif ?>
                            <?php if (! empty($order['notes'])): ?>
                                <p class="mt-2 mb-0 small"><span class="text-secondary">Catatan customer:</span> <?= esc($order['notes']) ?></p>
                            <?php endif ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-card">
                            <h2 class="info-card__title">Pengiriman</h2>
                            <?php if (! empty($order['tracking_number'])): ?>
                                <p class="mb-1">Kurir: <strong><?= esc($order['courier']) ?></strong></p>
                                <p class="mb-1">Nomor resi: <strong><?= esc($order['tracking_number']) ?></strong></p>
                                <p class="mb-1 small text-secondary">Dikirim <?= format_date($order['shipped_at']) ?></p>
                                <?php if (! empty($order['delivered_at'])): ?>
                                    <p class="mb-0 small text-secondary">Diterima <?= format_date($order['delivered_at']) ?></p>
                                <?php endif ?>
                            <?php else: ?>
                                <p class="mb-0 text-secondary">Belum dikirim.</p>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="summary-card mb-3">
                    <h2 class="summary-card__title">Pembayaran</h2>
                    <dl class="summary-list">
                        <div><dt>Total tagihan</dt><dd><?= format_rupiah($order['total']) ?></dd></div>
                        <div><dt>Status</dt><dd><?= status_badge(payment_status_label($order['payment_status'])) ?></dd></div>
                        <?php if ($verifier !== null): ?>
                            <div><dt>Diperiksa oleh</dt><dd><?= esc($verifier) ?>, <?= format_date($order['verified_at']) ?></dd></div>
                        <?php endif ?>
                    </dl>

                    <?php if (! empty($order['proof_file'])): ?>
                        <a href="<?= site_url('admin/orders/' . (int) $order['id'] . '/proof') ?>" target="_blank" rel="noopener">
                            <img class="proof-thumb" src="<?= site_url('admin/orders/' . (int) $order['id'] . '/proof') ?>" alt="Bukti transfer pesanan <?= esc($order['order_number'], 'attr') ?>">
                        </a>
                    <?php else: ?>
                        <p class="text-secondary mb-0">Customer belum mengunggah bukti pembayaran.</p>
                    <?php endif ?>

                    <?php if ($can['verify']): ?>
                        <div class="d-flex gap-2 mt-3">
                            <form action="<?= site_url('admin/orders/' . (int) $order['id'] . '/verify') ?>" method="post" class="flex-fill">
                                <?= csrf_field() ?>
                                <input type="hidden" name="decision" value="approve">
                                <button type="submit" class="btn btn-brand w-100">Terima pembayaran</button>
                            </form>
                            <form action="<?= site_url('admin/orders/' . (int) $order['id'] . '/verify') ?>" method="post" class="flex-fill"
                                  onsubmit="return confirm('Tolak bukti pembayaran ini? Customer akan diminta mengunggah ulang.');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="decision" value="reject">
                                <button type="submit" class="btn btn-outline-danger w-100">Tolak</button>
                            </form>
                        </div>
                    <?php elseif ($order['payment_status'] === 'submitted' && session('role') !== 'admin'): ?>
                        <p class="small text-secondary mt-3 mb-0">Verifikasi pembayaran dilakukan oleh admin.</p>
                    <?php endif ?>
                </div>

                <?php if ($can['ship']): ?>
                    <div class="summary-card mb-3">
                        <h2 class="summary-card__title">Kirim pesanan</h2>
                        <form action="<?= site_url('admin/orders/' . (int) $order['id'] . '/ship') ?>" method="post" novalidate>
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label for="courier" class="form-label">Kurir</label>
                                <input type="text" id="courier" name="courier" maxlength="100" required placeholder="Contoh: JNE"
                                       class="form-control<?= isset($errors['courier']) ? ' is-invalid' : '' ?>" value="<?= esc(old('courier'), 'attr') ?>">
                                <?php if (isset($errors['courier'])): ?><div class="invalid-feedback"><?= esc($errors['courier']) ?></div><?php endif ?>
                            </div>
                            <div class="mb-3">
                                <label for="tracking_number" class="form-label">Nomor resi</label>
                                <input type="text" id="tracking_number" name="tracking_number" maxlength="100" required
                                       class="form-control<?= isset($errors['tracking_number']) ? ' is-invalid' : '' ?>" value="<?= esc(old('tracking_number'), 'attr') ?>">
                                <?php if (isset($errors['tracking_number'])): ?><div class="invalid-feedback"><?= esc($errors['tracking_number']) ?></div><?php endif ?>
                            </div>
                            <button type="submit" class="btn btn-brand w-100">Tandai dikirim</button>
                        </form>
                    </div>
                <?php endif ?>

                <?php if ($can['complete']): ?>
                    <div class="summary-card mb-3">
                        <h2 class="summary-card__title">Selesaikan pesanan</h2>
                        <p class="small text-secondary">Gunakan setelah paket dipastikan sudah diterima customer.</p>
                        <form action="<?= site_url('admin/orders/' . (int) $order['id'] . '/complete') ?>" method="post">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-brand w-100">Tandai selesai</button>
                        </form>
                    </div>
                <?php endif ?>

                <?php if ($can['cancel']): ?>
                    <form action="<?= site_url('admin/orders/' . (int) $order['id'] . '/cancel') ?>" method="post"
                          onsubmit="return confirm('Batalkan pesanan ini? Stok produk akan dikembalikan.');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-outline-danger w-100">Batalkan pesanan</button>
                    </form>
                <?php endif ?>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
