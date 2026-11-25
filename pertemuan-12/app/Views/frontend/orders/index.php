<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<section class="section">
    <div class="container">
        <h1 class="section__title mb-4">Pesanan saya</h1>

        <?php if ($orders === []): ?>
            <div class="empty-state">
                <i class="bi bi-receipt" aria-hidden="true"></i>
                <p class="mb-3">Belum ada pesanan.</p>
                <a class="btn btn-brand" href="<?= site_url('catalog') ?>">Mulai belanja</a>
            </div>
        <?php else: ?>
            <div class="table-responsive admin-table">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Nomor pesanan</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col" class="text-end">Total</th>
                            <th scope="col">Pembayaran</th>
                            <th scope="col">Status pesanan</th>
                            <th scope="col"><span class="visually-hidden">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td class="fw-semibold"><?= esc($order['order_number']) ?></td>
                                <td><?= format_date($order['created_at']) ?></td>
                                <td class="text-end text-nowrap"><?= format_rupiah($order['total']) ?></td>
                                <td><?= status_badge(payment_status_label($order['payment_status'])) ?></td>
                                <td><?= status_badge(order_status_label($order['order_status'])) ?></td>
                                <td class="text-end"><a class="btn btn-ghost btn-sm" href="<?= site_url('orders/' . (int) $order['id']) ?>">Detail</a></td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-4"><?= $pager->links('default', 'bootstrap') ?></div>
        <?php endif ?>
    </div>
</section>

<?= $this->endSection() ?>
