<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<section class="section">
    <div class="container">
        <?= $this->include('admin/_nav') ?>

        <h1 class="section__title">Dashboard</h1>
        <p class="section__lead mb-4">
            Masuk sebagai <strong><?= esc(session('name')) ?></strong>
            (<?= esc(session('email')) ?>), role <span class="badge text-bg-dark"><?= esc(session('role')) ?></span>.
            Halaman ini hanya bisa dibuka oleh admin dan operator.
        </p>

        <div class="row g-3">
            <?php foreach (['customer' => 'Customer', 'operator' => 'Operator', 'admin' => 'Admin'] as $role => $label): ?>
                <div class="col-sm-4">
                    <div class="stat-card">
                        <span class="stat-card__label">Akun <?= esc($label) ?></span>
                        <strong class="stat-card__value"><?= esc($userCounts[$role]) ?></strong>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
