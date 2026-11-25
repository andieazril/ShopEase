<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<section class="section">
    <div class="container">
        <?= $this->include('admin/_nav') ?>

        <h1 class="section__title mb-4">Pesanan</h1>

        <form class="admin-filter" action="<?= site_url('admin/orders') ?>" method="get" role="search">
            <div>
                <label for="keyword" class="form-label">Cari</label>
                <input type="search" id="keyword" name="keyword" class="form-control" value="<?= esc($keyword, 'attr') ?>" placeholder="Nomor pesanan, nama, atau email">
            </div>
            <div>
                <label for="status" class="form-label">Status pesanan</label>
                <select id="status" name="status" class="form-select">
                    <option value="">Semua</option>
                    <?php foreach (\App\Models\OrderModel::STATUSES as $value): ?>
                        <option value="<?= esc($value, 'attr') ?>"<?= $status === $value ? ' selected' : '' ?>><?= esc(order_status_label($value)['label']) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="admin-filter__actions">
                <button type="submit" class="btn btn-brand">Terapkan</button>
                <a class="btn btn-ghost" href="<?= site_url('admin/orders') ?>">Reset</a>
            </div>
        </form>

        <?php if ($orders === []): ?>
            <div class="empty-state"><p class="mb-0">Tidak ada pesanan yang cocok dengan filter.</p></div>
        <?php else: ?>
            <div class="table-responsive admin-table">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Nomor</th>
                            <th scope="col">Customer</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col" class="text-end">Total</th>
                            <th scope="col">Pembayaran</th>
                            <th scope="col">Status</th>
                            <th scope="col"><span class="visually-hidden">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td class="fw-semibold"><?= esc($order['order_number']) ?></td>
                                <td><?= esc($order['customer_name']) ?><div class="small text-secondary"><?= esc($order['customer_email']) ?></div></td>
                                <td><?= format_date($order['created_at']) ?></td>
                                <td class="text-end text-nowrap"><?= format_rupiah($order['total']) ?></td>
                                <td><?= status_badge(payment_status_label($order['payment_status'])) ?></td>
                                <td><?= status_badge(order_status_label($order['order_status'])) ?></td>
                                <td class="text-end"><a class="btn btn-ghost btn-sm" href="<?= site_url('admin/orders/' . (int) $order['id']) ?>">Detail</a></td>
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
