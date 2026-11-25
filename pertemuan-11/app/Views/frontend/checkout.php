<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<section class="section">
    <div class="container">
        <h1 class="section__title mb-4">Checkout</h1>

        <?= view('layouts/partials/todo', [
            'todoText'      => 'Di sini nanti customer memilih alamat, memeriksa ringkasan pesanan, dan membuat pesanan. Total dihitung ulang di server.',
            'todoPraktikum' => 'HOL Praktikum 9 (Checkout dan Order Management)',
        ]) ?>

        <a class="btn btn-ghost mt-4" href="<?= site_url('cart') ?>">Kembali ke keranjang</a>
    </div>
</section>

<?= $this->endSection() ?>
