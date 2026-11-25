<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$errors    = session('errors') ?? [];
$oldOption = old('address_option', $addresses !== [] ? (string) $addresses[0]['id'] : 'new');
$total     = $cart['total'] + $shippingCost;

$field = static function (string $name, string $label, string $type = 'text', string $extra = '') use ($errors): string {
    $invalid = isset($errors[$name]);
    $html    = '<div class="mb-3"><label for="' . $name . '" class="form-label">' . esc($label) . '</label>';
    $html   .= '<input type="' . $type . '" id="' . $name . '" name="' . $name . '" class="form-control' . ($invalid ? ' is-invalid' : '') . '" value="' . esc(old($name), 'attr') . '" ' . $extra . '>';

    if ($invalid) {
        $html .= '<div class="invalid-feedback">' . esc($errors[$name]) . '</div>';
    }

    return $html . '</div>';
};
?>

<section class="section">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= site_url('cart') ?>">Keranjang</a></li>
                <li class="breadcrumb-item active" aria-current="page">Checkout</li>
            </ol>
        </nav>
        <h1 class="section__title mb-4">Checkout</h1>

        <form id="checkoutForm" action="<?= site_url('checkout') ?>" method="post" novalidate>
            <?= csrf_field() ?>

            <div class="row gy-4 gx-lg-5">
                <div class="col-lg-7">
                    <fieldset>
                        <legend class="checkout-legend">Alamat pengiriman</legend>

                        <?php if (isset($errors['address_option'])): ?>
                            <p class="text-danger small fw-semibold"><?= esc($errors['address_option']) ?></p>
                        <?php endif ?>

                        <div class="address-options">
                            <?php foreach ($addresses as $address): ?>
                                <label class="address-option">
                                    <input class="form-check-input" type="radio" name="address_option" value="<?= (int) $address['id'] ?>"<?= $oldOption === (string) $address['id'] ? ' checked' : '' ?>>
                                    <span>
                                        <strong><?= esc($address['recipient_name']) ?></strong> · <?= esc($address['phone']) ?><br>
                                        <?= esc($address['address']) ?>, <?= esc($address['city']) ?>, <?= esc($address['province']) ?> <?= esc($address['postal_code']) ?>
                                    </span>
                                </label>
                            <?php endforeach ?>

                            <label class="address-option">
                                <input class="form-check-input" type="radio" name="address_option" value="new" id="addressNew"<?= $oldOption === 'new' ? ' checked' : '' ?>>
                                <span><strong>Kirim ke alamat baru</strong></span>
                            </label>
                        </div>

                        <div id="newAddressFields" class="new-address"<?= $oldOption === 'new' ? '' : ' hidden' ?>>
                            <div class="row">
                                <div class="col-sm-6"><?= $field('recipient_name', 'Nama penerima', 'text', 'autocomplete="name"') ?></div>
                                <div class="col-sm-6"><?= $field('phone', 'Nomor telepon', 'tel', 'autocomplete="tel"') ?></div>
                            </div>
                            <?= $field('address', 'Alamat lengkap (jalan, nomor rumah, RT/RW)', 'text', 'autocomplete="street-address"') ?>
                            <div class="row">
                                <div class="col-sm-5"><?= $field('city', 'Kota/kabupaten') ?></div>
                                <div class="col-sm-4"><?= $field('province', 'Provinsi') ?></div>
                                <div class="col-sm-3"><?= $field('postal_code', 'Kode pos', 'text', 'inputmode="numeric" maxlength="5" autocomplete="postal-code"') ?></div>
                            </div>
                        </div>
                    </fieldset>

                    <div class="mt-3">
                        <label for="notes" class="form-label">Catatan untuk toko (opsional)</label>
                        <textarea id="notes" name="notes" rows="2" maxlength="500" class="form-control<?= isset($errors['notes']) ? ' is-invalid' : '' ?>"><?= esc(old('notes')) ?></textarea>
                        <?php if (isset($errors['notes'])): ?>
                            <div class="invalid-feedback"><?= esc($errors['notes']) ?></div>
                        <?php endif ?>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="summary-card">
                        <h2 class="summary-card__title">Ringkasan pesanan</h2>
                        <ul class="checkout-items">
                            <?php foreach ($cart['items'] as $item): ?>
                                <li>
                                    <span><?= esc($item['name']) ?> <span class="text-secondary">× <?= (int) $item['quantity'] ?></span></span>
                                    <span class="text-nowrap"><?= format_rupiah($item['subtotal']) ?></span>
                                </li>
                            <?php endforeach ?>
                        </ul>
                        <dl class="summary-list">
                            <div><dt>Subtotal</dt><dd><?= format_rupiah($cart['total']) ?></dd></div>
                            <div><dt>Ongkos kirim</dt><dd><?= format_rupiah($shippingCost) ?></dd></div>
                            <div class="summary-list__total"><dt>Total</dt><dd><?= format_rupiah($total) ?></dd></div>
                        </dl>
                        <p class="small text-secondary">Total dihitung ulang oleh sistem saat pesanan dibuat. Pembayaran melalui transfer bank.</p>
                        <button type="submit" id="placeOrder" class="btn btn-brand btn-lg w-100">Buat pesanan</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Tampilkan form alamat baru hanya jika opsi "alamat baru" dipilih.
    document.querySelectorAll('input[name="address_option"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            document.getElementById('newAddressFields').hidden = !document.getElementById('addressNew').checked;
        });
    });

    // Cegah submit ganda: tombol dinonaktifkan setelah ditekan.
    document.getElementById('checkoutForm').addEventListener('submit', function () {
        var button = document.getElementById('placeOrder');
        button.disabled = true;
        button.textContent = 'Memproses pesanan…';
    });
</script>
<?= $this->endSection() ?>
