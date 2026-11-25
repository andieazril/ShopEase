<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$statusOptions = ['terjual' => 'Terjual (diproses, dikirim, selesai)', 'semua' => 'Semua status'];

foreach (\App\Models\OrderModel::STATUSES as $value) {
    $statusOptions[$value] = order_status_label($value)['label'];
}
?>

<section class="section">
    <div class="container">
        <?= $this->include('admin/_nav') ?>

        <h1 class="section__title mb-4">Laporan penjualan</h1>

        <form class="admin-filter" action="<?= site_url('admin/reports') ?>" method="get">
            <div>
                <label for="from" class="form-label">Dari tanggal</label>
                <input type="date" id="from" name="from" class="form-control" value="<?= esc($from, 'attr') ?>">
            </div>
            <div>
                <label for="to" class="form-label">Sampai tanggal</label>
                <input type="date" id="to" name="to" class="form-control" value="<?= esc($to, 'attr') ?>">
            </div>
            <div>
                <label for="status" class="form-label">Status pesanan</label>
                <select id="status" name="status" class="form-select">
                    <?php foreach ($statusOptions as $value => $label): ?>
                        <option value="<?= esc($value, 'attr') ?>"<?= $status === $value ? ' selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="admin-filter__actions">
                <button type="submit" class="btn btn-brand">Tampilkan</button>
            </div>
        </form>

        <?php if ($error !== null): ?>
            <div class="alert alert-warning"><?= esc($error) ?></div>
        <?php endif ?>

        <p class="text-secondary">
            Periode <?= format_date($from, false) ?> sampai <?= format_date($to, false) ?>, status: <?= esc($statusOptions[$status]) ?>.
        </p>

        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3"><div class="stat-card"><span class="stat-card__label">Jumlah pesanan</span><strong class="stat-card__value"><?= (int) $totals['order_count'] ?></strong></div></div>
            <div class="col-sm-6 col-lg-3"><div class="stat-card"><span class="stat-card__label">Subtotal produk</span><strong class="stat-card__value"><?= format_rupiah($totals['subtotal']) ?></strong></div></div>
            <div class="col-sm-6 col-lg-3"><div class="stat-card"><span class="stat-card__label">Ongkos kirim</span><strong class="stat-card__value"><?= format_rupiah($totals['shipping']) ?></strong></div></div>
            <div class="col-sm-6 col-lg-3"><div class="stat-card"><span class="stat-card__label">Total</span><strong class="stat-card__value"><?= format_rupiah($totals['total']) ?></strong></div></div>
        </div>

        <?php if ($rows === []): ?>
            <div class="empty-state"><p class="mb-0">Tidak ada pesanan pada periode dan status ini.</p></div>
        <?php else: ?>
            <div class="table-responsive admin-table">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Tanggal</th>
                            <th scope="col" class="text-end">Jumlah pesanan</th>
                            <th scope="col" class="text-end">Subtotal produk</th>
                            <th scope="col" class="text-end">Ongkos kirim</th>
                            <th scope="col" class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <td><?= format_date($row['day'], false) ?></td>
                                <td class="text-end"><?= (int) $row['order_count'] ?></td>
                                <td class="text-end text-nowrap"><?= format_rupiah($row['subtotal']) ?></td>
                                <td class="text-end text-nowrap"><?= format_rupiah($row['shipping']) ?></td>
                                <td class="text-end text-nowrap fw-semibold"><?= format_rupiah($row['total']) ?></td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold">
                            <td>Total</td>
                            <td class="text-end"><?= (int) $totals['order_count'] ?></td>
                            <td class="text-end text-nowrap"><?= format_rupiah($totals['subtotal']) ?></td>
                            <td class="text-end text-nowrap"><?= format_rupiah($totals['shipping']) ?></td>
                            <td class="text-end text-nowrap"><?= format_rupiah($totals['total']) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif ?>
    </div>
</section>

<?= $this->endSection() ?>
