<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$errors = session('errors') ?? [];
$isEdit = $category !== null;
$action = $isEdit ? site_url('admin/categories/' . (int) $category['id'] . '/update') : site_url('admin/categories/store');
?>

<section class="section">
    <div class="container">
        <?= $this->include('admin/_nav') ?>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= site_url('admin/categories') ?>">Kategori</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= $isEdit ? 'Ubah' : 'Tambah' ?></li>
            </ol>
        </nav>
        <h1 class="section__title mb-4"><?= $isEdit ? 'Ubah kategori' : 'Tambah kategori' ?></h1>

        <form class="admin-form" action="<?= $action ?>" method="post" novalidate>
            <?= csrf_field() ?>

            <div class="mb-3">
                <label for="name" class="form-label">Nama kategori</label>
                <input type="text" id="name" name="name" maxlength="100" required
                       class="form-control<?= isset($errors['name']) ? ' is-invalid' : '' ?>"
                       value="<?= esc(old('name', $category['name'] ?? ''), 'attr') ?>">
                <?php if (isset($errors['name'])): ?>
                    <div class="invalid-feedback"><?= esc($errors['name']) ?></div>
                <?php else: ?>
                    <div class="form-text">Slug URL dibuat otomatis dari nama.</div>
                <?php endif ?>
            </div>

            <div class="mb-4">
                <label for="description" class="form-label">Deskripsi (opsional)</label>
                <textarea id="description" name="description" rows="3" maxlength="1000"
                          class="form-control<?= isset($errors['description']) ? ' is-invalid' : '' ?>"><?= esc(old('description', $category['description'] ?? '')) ?></textarea>
                <?php if (isset($errors['description'])): ?>
                    <div class="invalid-feedback"><?= esc($errors['description']) ?></div>
                <?php endif ?>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-brand"><?= $isEdit ? 'Simpan perubahan' : 'Tambah kategori' ?></button>
                <a class="btn btn-ghost" href="<?= site_url('admin/categories') ?>">Batal</a>
            </div>
        </form>
    </div>
</section>

<?= $this->endSection() ?>
