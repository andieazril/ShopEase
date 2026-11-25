<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php $errors = session('errors') ?? []; ?>

<section class="section">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= site_url('orders') ?>">Pesanan saya</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= esc($order['order_number']) ?></li>
            </ol>
        </nav>

        <div class="section__head">
            <div>
                <h1 class="section__title">Pesanan <?= esc($order['order_number']) ?></h1>
                <p class="text-secondary mb-0">Dibuat <?= format_date($order['created_at']) ?></p>
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
                                <p class="mt-2 mb-0 small"><span class="text-secondary">Catatan:</span> <?= esc($order['notes']) ?></p>
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
                                <p class="mb-0 text-secondary">Nomor resi muncul di sini setelah pesanan dikirim.</p>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="summary-card">
                    <h2 class="summary-card__title">Pembayaran</h2>

                    <?php if ($order['order_status'] === 'cancelled'): ?>
                        <p class="mb-0">Pesanan ini dibatalkan.</p>

                    <?php elseif ($canUpload): ?>
                        <?php if ($order['payment_status'] === 'rejected'): ?>
                            <div class="alert alert-danger small">Bukti pembayaran sebelumnya ditolak. Pastikan nominal dan rekening tujuan sesuai, lalu unggah ulang.</div>
                        <?php endif ?>

                        <p class="mb-2">Transfer <strong><?= format_rupiah($order['total']) ?></strong> ke:</p>
                        <dl class="summary-list">
                            <div><dt>Bank</dt><dd><?= esc($bank['bank']) ?></dd></div>
                            <div><dt>Nomor rekening</dt><dd><?= esc($bank['number']) ?></dd></div>
                            <div><dt>Atas nama</dt><dd><?= esc($bank['holder']) ?></dd></div>
                        </dl>

                        <form action="<?= site_url('orders/' . (int) $order['id'] . '/payment') ?>" method="post" enctype="multipart/form-data">
                            <?= csrf_field() ?>
                            <label for="proof" class="form-label fw-semibold">Bukti transfer</label>
                            <input type="file" id="proof" name="proof" accept="image/jpeg,image/png,image/webp"
                                   class="form-control<?= isset($errors['proof']) ? ' is-invalid' : '' ?>" required>
                            <?php if (isset($errors['proof'])): ?>
                                <div class="invalid-feedback"><?= esc($errors['proof']) ?></div>
                            <?php else: ?>
                                <div class="form-text">JPG, PNG, atau WEBP. Maksimal 2 MB.</div>
                            <?php endif ?>
                            <button type="submit" class="btn btn-brand w-100 mt-3">Unggah bukti pembayaran</button>
                        </form>

                    <?php else: ?>
                        <p class="mb-2">Total: <strong><?= format_rupiah($order['total']) ?></strong></p>
                        <?php if ($order['payment_status'] === 'submitted'): ?>
                            <p>Bukti pembayaran sedang diperiksa admin.</p>
                        <?php elseif ($order['payment_status'] === 'verified'): ?>
                            <p>Pembayaran sudah diverifikasi pada <?= format_date($order['verified_at']) ?>.</p>
                        <?php endif ?>
                    <?php endif ?>

                    <?php if (! empty($order['proof_file'])): ?>
                        <a class="link-more" href="<?= site_url('orders/' . (int) $order['id'] . '/proof') ?>" target="_blank" rel="noopener">Lihat bukti yang diunggah</a>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
