<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<section class="section">
    <div class="container">
        <?= $this->include('admin/_nav') ?>

        <h1 class="section__title mb-4">Pesanan</h1>

        <?= view('layouts/partials/todo', [
            'todoText'      => 'Di sini nanti admin/operator melihat pesanan masuk, memverifikasi bukti bayar, memperbarui status, serta mengisi kurir dan nomor resi.',
            'todoPraktikum' => 'HOL Praktikum 9–10',
        ]) ?>
    </div>
</section>

<?= $this->endSection() ?>
