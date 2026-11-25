<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php $errors = session('errors') ?? []; ?>

<section class="auth">
    <div class="container">
        <div class="auth__card">
            <h1 class="auth__title">Masuk ke ShopEase</h1>
            <p class="auth__lead">Masuk untuk checkout, mengunggah bukti bayar, dan melihat status pesanan.</p>

            <form action="<?= site_url('login') ?>" method="post" novalidate>
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email"
                           class="form-control<?= isset($errors['email']) ? ' is-invalid' : '' ?>"
                           value="<?= esc(old('email', session('email') ?? '')) ?>"
                           autocomplete="email" required autofocus
                           <?= isset($errors['email']) ? 'aria-describedby="emailError"' : '' ?>>
                    <?php if (isset($errors['email'])): ?>
                        <div id="emailError" class="invalid-feedback"><?= esc($errors['email']) ?></div>
                    <?php endif ?>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group has-validation">
                        <input type="password" id="password" name="password"
                               class="form-control<?= isset($errors['password']) ? ' is-invalid' : '' ?>"
                               autocomplete="current-password" required
                               <?= isset($errors['password']) ? 'aria-describedby="passwordError"' : '' ?>>
                        <button class="btn btn-outline-secondary password-toggle" type="button" data-target="password" aria-label="Tampilkan password">
                            <i class="bi bi-eye" aria-hidden="true"></i>
                        </button>
                        <?php if (isset($errors['password'])): ?>
                            <div id="passwordError" class="invalid-feedback"><?= esc($errors['password']) ?></div>
                        <?php endif ?>
                    </div>
                </div>

                <button type="submit" class="btn btn-brand btn-lg w-100">Masuk</button>
            </form>

            <p class="auth__switch">Belum punya akun? <a href="<?= site_url('register') ?>">Daftar sekarang</a></p>
        </div>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?= $this->include('auth/_password_toggle') ?>
<?= $this->endSection() ?>
