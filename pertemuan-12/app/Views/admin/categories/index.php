<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<section class="section">
    <div class="container">
        <?= $this->include('admin/_nav') ?>

        <div class="section__head">
            <h1 class="section__title">Kategori</h1>
            <a class="btn btn-brand" href="<?= site_url('admin/categories/create') ?>"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Tambah kategori</a>
        </div>

        <div class="table-responsive admin-table">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Nama</th>
                        <th scope="col">Slug</th>
                        <th scope="col">Deskripsi</th>
                        <th scope="col" class="text-end">Produk</th>
                        <th scope="col" class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $start = ($pager->getCurrentPage() - 1) * $pager->getPerPage(); ?>
                    <?php foreach ($categories as $i => $category): ?>
                        <tr>
                            <td><?= $start + $i + 1 ?></td>
                            <td class="fw-semibold"><?= esc($category['name']) ?></td>
                            <td><code><?= esc($category['slug']) ?></code></td>
                            <td class="text-secondary small"><?= esc($category['description'] ?? '-') ?></td>
                            <td class="text-end"><?= (int) $category['product_count'] ?></td>
                            <td class="text-end text-nowrap">
                                <a class="btn btn-ghost btn-sm" href="<?= site_url('admin/categories/' . (int) $category['id'] . '/edit') ?>">Ubah</a>
                                <form class="d-inline" action="<?= site_url('admin/categories/' . (int) $category['id'] . '/delete') ?>" method="post"
                                      onsubmit="return confirm('Hapus kategori <?= esc($category['name'], 'js') ?>?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-link text-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
        <div class="mt-4"><?= $pager->links('default', 'bootstrap') ?></div>
    </div>
</section>

<?= $this->endSection() ?>
