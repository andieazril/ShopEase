<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<section class="section">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= site_url('orders') ?>">Pesanan saya</a></li>
                <li class="breadcrumb-item active" aria-current="page">Pesanan #<?= esc($orderId) ?></li>
            </ol>
        </nav>
        <h1 class="section__title mb-4">Detail pesanan #<?= esc($orderId) ?></h1>

        <?= view('layouts/partials/todo', [
            'todoText'      => 'Di sini nanti tampil item pesanan dengan harga snapshot, total, status pembayaran, form unggah bukti bayar, dan nomor resi.',
            'todoPraktikum' => 'HOL Praktikum 9–10',
        ]) ?>
    </div>
</section>

<?= $this->endSection() ?>
