<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<section class="section">
    <div class="container">
        <h1 class="section__title mb-4">Pesanan saya</h1>

        <?= view('layouts/partials/todo', [
            'todoText'      => 'Di sini nanti tampil nomor pesanan, tanggal, total, status pembayaran, dan status pesanan.',
            'todoPraktikum' => 'HOL Praktikum 9 (Checkout dan Order Management)',
        ]) ?>

        <div class="empty-state mt-4">
            <i class="bi bi-receipt" aria-hidden="true"></i>
            <p class="mb-3">Belum ada pesanan.</p>
            <a class="btn btn-brand" href="<?= site_url('catalog') ?>">Mulai belanja</a>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
