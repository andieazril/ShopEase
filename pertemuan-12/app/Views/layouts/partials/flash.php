<?php
/**
 * Pesan singkat (flashdata) dari controller/filter: 'success' dan 'error'.
 */
$success = session()->getFlashdata('success');
$error   = session()->getFlashdata('error');
?>
<?php if ($success || $error): ?>
    <div class="container flash-wrap">
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show flash" role="status">
                <i class="bi bi-check-circle-fill me-2" aria-hidden="true"></i><?= esc($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        <?php endif ?>
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show flash" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2" aria-hidden="true"></i><?= esc($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        <?php endif ?>
    </div>
<?php endif ?>
