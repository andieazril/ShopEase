<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<section class="section">
    <div class="container">
        <?= $this->include('admin/_nav') ?>

        <div class="section__head">
            <h1 class="section__title">Produk</h1>
            <button type="button" class="btn btn-brand" disabled title="Dikerjakan di Praktikum 5">
                <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Tambah produk
            </button>
        </div>

        <?= view('layouts/partials/todo', [
            'todoText'      => 'Daftar produk sudah dibaca dari database. Tambah, ubah, hapus/nonaktifkan, dan upload gambar menyusul.',
            'todoPraktikum' => 'HOL Praktikum 5 (CRUD Produk dan Upload Gambar)',
        ]) ?>

        <div class="table-responsive admin-table mt-4">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Produk</th>
                        <th scope="col">Kategori</th>
                        <th scope="col" class="text-end">Harga</th>
                        <th scope="col" class="text-end">Stok</th>
                        <th scope="col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <?php $imageUrl = product_image_url($product['image']); ?>
                        <tr>
                            <td><?= esc($product['id']) ?></td>
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
                            <td class="text-end"><?= format_rupiah($product['price']) ?></td>
                            <td class="text-end<?= (int) $product['stock'] === 0 ? ' text-danger fw-semibold' : '' ?>"><?= esc($product['stock']) ?></td>
                            <td>
                                <?php if ($product['status'] === 'active'): ?>
                                    <span class="badge text-bg-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary">Nonaktif</span>
                                <?php endif ?>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            <?= $pager->links('default', 'bootstrap') ?>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
