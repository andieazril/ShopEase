<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$errors = session('errors') ?? [];

/**
 * Atribut class + aria untuk input yang punya pesan error.
 */
$invalid = static fn (string $field): string => isset($errors[$field])
    ? ' is-invalid" aria-describedby="' . $field . 'Error'
    : '';
?>

<section class="auth">
    <div class="container">
        <div class="auth__card">
            <h1 class="auth__title">Buat akun ShopEase</h1>
            <p class="auth__lead">Cukup nama, email, dan password. Riwayat pesanan dan bukti bayar Anda akan tersimpan di akun ini.</p>

            <form action="<?= site_url('register') ?>" method="post" novalidate>
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="name" class="form-label">Nama lengkap</label>
                    <input type="text" id="name" name="name" class="form-control<?= $invalid('name') ?>"
                           value="<?= esc(old('name')) ?>" autocomplete="name" required autofocus>
                    <?php if (isset($errors['name'])): ?>
                        <div id="nameError" class="invalid-feedback"><?= esc($errors['name']) ?></div>
                    <?php endif ?>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control<?= $invalid('email') ?>"
                           value="<?= esc(old('email')) ?>" autocomplete="email" required>
                    <?php if (isset($errors['email'])): ?>
                        <div id="emailError" class="invalid-feedback"><?= esc($errors['email']) ?></div>
                    <?php endif ?>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group has-validation">
                        <input type="password" id="password" name="password" class="form-control<?= $invalid('password') ?>"
                               autocomplete="new-password" minlength="8" required>
                        <button class="btn btn-outline-secondary password-toggle" type="button" data-target="password" aria-label="Tampilkan password">
                            <i class="bi bi-eye" aria-hidden="true"></i>
                        </button>
                        <?php if (isset($errors['password'])): ?>
                            <div id="passwordError" class="invalid-feedback"><?= esc($errors['password']) ?></div>
                        <?php endif ?>
                    </div>
                    <?php if (! isset($errors['password'])): ?>
                        <div class="form-text">Minimal 8 karakter.</div>
                    <?php endif ?>
                </div>

                <div class="mb-4">
                    <label for="password_confirm" class="form-label">Ulangi password</label>
                    <input type="password" id="password_confirm" name="password_confirm" class="form-control<?= $invalid('password_confirm') ?>"
                           autocomplete="new-password" required>
                    <?php if (isset($errors['password_confirm'])): ?>
                        <div id="password_confirmError" class="invalid-feedback"><?= esc($errors['password_confirm']) ?></div>
                    <?php endif ?>
                </div>

                <button type="submit" class="btn btn-brand btn-lg w-100">Buat akun</button>
            </form>

            <p class="auth__switch">Sudah punya akun? <a href="<?= site_url('login') ?>">Masuk</a></p>
        </div>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?= $this->include('auth/_password_toggle') ?>
<?= $this->endSection() ?>
