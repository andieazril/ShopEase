<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<section class="section">
    <div class="container">
        <h1 class="section__title mb-4">Keranjang belanja</h1>

        <?= view('layouts/partials/todo', [
            'todoText'      => 'Di sini nanti tampil daftar item, harga satuan, kuantitas, subtotal, dan total keranjang.',
            'todoPraktikum' => 'HOL Praktikum 8 (Shopping Cart)',
        ]) ?>

        <div class="empty-state mt-4">
            <i class="bi bi-cart3" aria-hidden="true"></i>
            <p class="mb-3">Keranjang Anda masih kosong.</p>
            <a class="btn btn-brand" href="<?= site_url('catalog') ?>">Belanja di katalog</a>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
