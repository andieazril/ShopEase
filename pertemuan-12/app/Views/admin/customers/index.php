<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<section class="section">
    <div class="container">
        <?= $this->include('admin/_nav') ?>

        <h1 class="section__title mb-4">Pelanggan</h1>

        <form class="admin-filter" action="<?= site_url('admin/customers') ?>" method="get" role="search">
            <div>
                <label for="keyword" class="form-label">Cari</label>
                <input type="search" id="keyword" name="keyword" class="form-control" value="<?= esc($keyword, 'attr') ?>" placeholder="Nama atau email">
            </div>
            <div class="admin-filter__actions">
                <button type="submit" class="btn btn-brand">Cari</button>
                <a class="btn btn-ghost" href="<?= site_url('admin/customers') ?>">Reset</a>
            </div>
        </form>

        <?php if ($customers === []): ?>
            <div class="empty-state"><p class="mb-0">Tidak ada pelanggan yang cocok.</p></div>
        <?php else: ?>
            <div class="table-responsive admin-table">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Nama</th>
                            <th scope="col">Email</th>
                            <th scope="col">Terdaftar</th>
                            <th scope="col" class="text-end">Jumlah pesanan</th>
                            <th scope="col" class="text-end">Total belanja</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td class="fw-semibold"><?= esc($customer['name']) ?></td>
                                <td><?= esc($customer['email']) ?></td>
                                <td><?= format_date($customer['created_at'], false) ?></td>
                                <td class="text-end"><?= (int) $customer['order_count'] ?></td>
                                <td class="text-end text-nowrap"><?= format_rupiah($customer['total_spent']) ?></td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
            <p class="small text-secondary mt-2">Total belanja dihitung dari pesanan yang sudah dibayar (diproses, dikirim, atau selesai).</p>
            <div class="mt-3"><?= $pager->links('default', 'bootstrap') ?></div>
        <?php endif ?>
    </div>
</section>

<?= $this->endSection() ?>
