<?php

use CodeIgniter\Pager\PagerRenderer;

/**
 * Template pagination Bootstrap 5.
 * Didaftarkan di app/Config/Pager.php dengan nama 'bootstrap'.
 *
 * @var PagerRenderer $pager
 */
$pager->setSurroundCount(2);
?>
<nav aria-label="Navigasi halaman">
    <ul class="pagination justify-content-center">
        <?php if ($pager->hasPrevious()) : ?>
            <li class="page-item">
                <a class="page-link" href="<?= $pager->getPrevious() ?>" aria-label="Halaman sebelumnya">
                    <i class="bi bi-chevron-left" aria-hidden="true"></i>
                </a>
            </li>
        <?php endif ?>

        <?php foreach ($pager->links() as $link) : ?>
            <li class="page-item<?= $link['active'] ? ' active' : '' ?>">
                <a class="page-link" href="<?= $link['uri'] ?>"<?= $link['active'] ? ' aria-current="page"' : '' ?>>
                    <?= $link['title'] ?>
                </a>
            </li>
        <?php endforeach ?>

        <?php if ($pager->hasNext()) : ?>
            <li class="page-item">
                <a class="page-link" href="<?= $pager->getNext() ?>" aria-label="Halaman berikutnya">
                    <i class="bi bi-chevron-right" aria-hidden="true"></i>
                </a>
            </li>
        <?php endif ?>
    </ul>
</nav>
