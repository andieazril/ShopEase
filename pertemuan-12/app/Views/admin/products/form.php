<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$errors   = session('errors') ?? [];
$isEdit   = $product !== null;
$action   = $isEdit ? site_url('admin/products/' . (int) $product['id'] . '/update') : site_url('admin/products/store');
$imageUrl = $isEdit ? product_image_url($product['image']) : null;
$value    = static fn (string $field, $default = '') => old($field, $product[$field] ?? $default);

$invalid = static fn (string $field): string => isset($errors[$field]) ? ' is-invalid' : '';
$error   = static fn (string $field): string => isset($errors[$field])
    ? '<div class="invalid-feedback">' . esc($errors[$field]) . '</div>'
    : '';
?>

<section class="section">
    <div class="container">
        <?= $this->include('admin/_nav') ?>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= site_url('admin/products') ?>">Produk</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= $isEdit ? 'Ubah' : 'Tambah' ?></li>
            </ol>
        </nav>
        <h1 class="section__title mb-4"><?= $isEdit ? 'Ubah produk' : 'Tambah produk' ?></h1>

        <form class="admin-form admin-form--wide" action="<?= $action ?>" method="post" enctype="multipart/form-data" novalidate>
            <?= csrf_field() ?>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama produk</label>
                        <input type="text" id="name" name="name" maxlength="150" required class="form-control<?= $invalid('name') ?>" value="<?= esc($value('name'), 'attr') ?>">
                        <?= $error('name') ?>
                    </div>

                    <div class="mb-3">
                        <label for="category_id" class="form-label">Kategori</label>
                        <select id="category_id" name="category_id" required class="form-select<?= $invalid('category_id') ?>">
                            <option value="">Pilih kategori</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= (int) $category['id'] ?>"<?= (string) $value('category_id') === (string) $category['id'] ? ' selected' : '' ?>><?= esc($category['name']) ?></option>
                            <?php endforeach ?>
                        </select>
                        <?= $error('category_id') ?>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea id="description" name="description" rows="5" maxlength="2000" class="form-control<?= $invalid('description') ?>"><?= esc($value('description')) ?></textarea>
                        <?= $error('description') ?>
                    </div>

                    <div class="row">
                        <div class="col-sm-4 mb-3">
                            <label for="price" class="form-label">Harga (Rp)</label>
                            <input type="number" id="price" name="price" min="0" step="1" required class="form-control<?= $invalid('price') ?>" value="<?= esc((string) (int) (float) $value('price'), 'attr') ?>">
                            <?= $error('price') ?>
                        </div>
                        <div class="col-sm-4 mb-3">
                            <label for="stock" class="form-label">Stok</label>
                            <input type="number" id="stock" name="stock" min="0" step="1" required class="form-control<?= $invalid('stock') ?>" value="<?= esc((string) $value('stock', 0), 'attr') ?>">
                            <?= $error('stock') ?>
                        </div>
                        <div class="col-sm-4 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select id="status" name="status" class="form-select<?= $invalid('status') ?>">
                                <option value="active"<?= $value('status', 'active') === 'active' ? ' selected' : '' ?>>Aktif (tampil di katalog)</option>
                                <option value="inactive"<?= $value('status', 'active') === 'inactive' ? ' selected' : '' ?>>Nonaktif</option>
                            </select>
                            <?= $error('status') ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <label for="image" class="form-label">Gambar produk</label>
                    <div class="image-preview mb-2">
                        <?php if ($imageUrl !== null): ?>
                            <img id="imagePreview" src="<?= esc($imageUrl, 'attr') ?>" alt="Gambar produk saat ini">
                        <?php else: ?>
                            <img id="imagePreview" alt="" hidden>
                            <span id="imagePlaceholder" class="text-secondary small">Belum ada gambar</span>
                        <?php endif ?>
                    </div>
                    <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp" class="form-control<?= $invalid('image') ?>"<?= $isEdit ? '' : ' required' ?>>
                    <?php if (isset($errors['image'])): ?>
                        <?= $error('image') ?>
                    <?php else: ?>
                        <div class="form-text">JPG, PNG, atau WEBP. Maksimal 2 MB.<?= $isEdit ? ' Kosongkan jika tidak ingin mengganti gambar.' : '' ?></div>
                    <?php endif ?>
                </div>
            </div>

            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-brand"><?= $isEdit ? 'Simpan perubahan' : 'Tambah produk' ?></button>
                <a class="btn btn-ghost" href="<?= site_url('admin/products') ?>">Batal</a>
            </div>
        </form>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Pratinjau gambar sebelum diunggah.
    document.getElementById('image').addEventListener('change', function (event) {
        var file = event.target.files[0];
        var preview = document.getElementById('imagePreview');
        var placeholder = document.getElementById('imagePlaceholder');

        if (!file) { return; }

        preview.src = URL.createObjectURL(file);
        preview.hidden = false;
        if (placeholder) { placeholder.hidden = true; }
    });
</script>
<?= $this->endSection() ?>
