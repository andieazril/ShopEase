<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<section class="section">
    <div class="container">
        <?= $this->include('admin/_nav') ?>

        <div class="section__head">
            <h1 class="section__title">Produk</h1>
            <?php if ($isAdmin): ?>
                <a class="btn btn-brand" href="<?= site_url('admin/products/create') ?>"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Tambah produk</a>
            <?php endif ?>
        </div>

        <?php if (! $isAdmin): ?>
            <p class="text-secondary">Sebagai operator, Anda dapat memperbarui stok produk.</p>
        <?php endif ?>

        <form class="admin-filter" action="<?= site_url('admin/products') ?>" method="get" role="search">
            <div>
                <label for="keyword" class="form-label">Cari</label>
                <input type="search" id="keyword" name="keyword" class="form-control" value="<?= esc($filters['keyword'], 'attr') ?>" placeholder="Nama produk">
            </div>
            <div>
                <label for="category" class="form-label">Kategori</label>
                <select id="category" name="category" class="form-select">
                    <option value="">Semua</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>"<?= $filters['category'] == $category['id'] ? ' selected' : '' ?>><?= esc($category['name']) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div>
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="">Semua</option>
                    <option value="active"<?= $filters['status'] === 'active' ? ' selected' : '' ?>>Aktif</option>
                    <option value="inactive"<?= $filters['status'] === 'inactive' ? ' selected' : '' ?>>Nonaktif</option>
                </select>
            </div>
            <div class="admin-filter__actions">
                <button type="submit" class="btn btn-brand">Terapkan</button>
                <a class="btn btn-ghost" href="<?= site_url('admin/products') ?>">Reset</a>
            </div>
        </form>

        <?php if ($products === []): ?>
            <div class="empty-state">
                <p class="mb-0">Tidak ada produk yang cocok dengan filter.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive admin-table">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Produk</th>
                            <th scope="col">Kategori</th>
                            <th scope="col" class="text-end">Harga</th>
                            <th scope="col">Stok</th>
                            <th scope="col">Status</th>
                            <?php if ($isAdmin): ?><th scope="col" class="text-end">Aksi</th><?php endif ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <?php $imageUrl = product_image_url($product['image']); ?>
                            <tr>
                                <td><?= (int) $product['id'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if ($imageUrl !== null): ?>
                                            <img class="admin-thumb" src="<?= esc($imageUrl, 'attr') ?>" alt="">
                                        <?php else: ?>
                                            <span class="admin-thumb admin-thumb--empty" aria-hidden="true"><i class="bi bi-image"></i></span>
                                        <?php endif ?>
                                        <?= esc($product['name']) ?>
                                    </div>
                                </td>
                                <td><?= esc($product['category_name']) ?></td>
                                <td class="text-end text-nowrap"><?= format_rupiah($product['price']) ?></td>
                                <td>
                                    <form class="stock-form" action="<?= site_url('admin/products/' . (int) $product['id'] . '/stock') ?>" method="post">
                                        <?= csrf_field() ?>
                                        <label for="stock-<?= (int) $product['id'] ?>" class="visually-hidden">Stok <?= esc($product['name']) ?></label>
                                        <input type="number" id="stock-<?= (int) $product['id'] ?>" name="stock" min="0" step="1" required
                                               class="form-control form-control-sm<?= (int) $product['stock'] === 0 ? ' is-empty' : '' ?>" value="<?= (int) $product['stock'] ?>">
                                        <button type="submit" class="btn btn-ghost btn-sm">Simpan</button>
                                    </form>
                                </td>
                                <td>
                                    <?php if ($product['status'] === 'active'): ?>
                                        <span class="badge text-bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-secondary">Nonaktif</span>
                                    <?php endif ?>
                                </td>
                                <?php if ($isAdmin): ?>
                                    <td class="text-end text-nowrap">
                                        <a class="btn btn-ghost btn-sm" href="<?= site_url('admin/products/' . (int) $product['id'] . '/edit') ?>">Ubah</a>
                                        <form class="d-inline" action="<?= site_url('admin/products/' . (int) $product['id'] . '/delete') ?>" method="post"
                                              onsubmit="return confirm('Hapus produk <?= esc($product['name'], 'js') ?>? Produk yang pernah dipesan akan dinonaktifkan, bukan dihapus.');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-link text-danger btn-sm">Hapus</button>
                                        </form>
                                    </td>
                                <?php endif ?>
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
