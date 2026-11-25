<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<section class="section">
    <div class="container">
        <?= $this->include('admin/_nav') ?>

        <h1 class="section__title">Dashboard</h1>
        <p class="section__lead mb-4">
            Masuk sebagai <strong><?= esc(session('name')) ?></strong>, role <span class="badge text-bg-dark"><?= esc(session('role')) ?></span>.
        </p>

        <div class="row g-3">
            <?php
            $cards = [
                ['Total produk', $stats['products'], null],
                ['Total customer', $stats['customers'], null],
                ['Total pesanan', $stats['orders'], null],
                ['Total penjualan', format_rupiah($stats['sales']), 'Pesanan diproses, dikirim, dan selesai'],
                ['Pesanan pending', $stats['pending'], 'Menunggu pembayaran atau verifikasi'],
                ['Pembayaran menunggu verifikasi', $stats['waiting'], null],
            ];
            ?>
            <?php foreach ($cards as [$label, $value, $hint]): ?>
                <div class="col-sm-6 col-lg-4">
                    <div class="stat-card">
                        <span class="stat-card__label"><?= esc($label) ?></span>
                        <strong class="stat-card__value"><?= esc((string) $value) ?></strong>
                        <?php if ($hint !== null): ?>
                            <span class="small text-secondary"><?= esc($hint) ?></span>
                        <?php endif ?>
                    </div>
                </div>
            <?php endforeach ?>
        </div>

        <div class="row g-4 mt-1">
            <div class="col-lg-8">
                <div class="info-card h-100">
                    <h2 class="info-card__title">Penjualan 14 hari terakhir</h2>
                    <div class="chart-wrap">
                        <canvas id="salesChart" aria-label="Grafik penjualan harian 14 hari terakhir" role="img"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="info-card h-100">
                    <h2 class="info-card__title">Produk terlaris</h2>
                    <?php if ($bestSellers === []): ?>
                        <p class="text-secondary mb-0">Belum ada penjualan.</p>
                    <?php else: ?>
                        <ol class="rank-list">
                            <?php foreach ($bestSellers as $product): ?>
                                <li><span><?= esc($product['name']) ?></span><strong><?= (int) $product['total_sold'] ?> terjual</strong></li>
                            <?php endforeach ?>
                        </ol>
                    <?php endif ?>
                </div>
            </div>
        </div>

        <div class="section__head mt-5">
            <h2 class="section__title fs-4">Transaksi terbaru</h2>
            <a class="link-more" href="<?= site_url('admin/orders') ?>">Semua pesanan</a>
        </div>
        <div class="table-responsive admin-table">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Nomor</th>
                        <th scope="col">Customer</th>
                        <th scope="col">Tanggal</th>
                        <th scope="col" class="text-end">Total</th>
                        <th scope="col">Pembayaran</th>
                        <th scope="col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent as $order): ?>
                        <tr>
                            <td><a href="<?= site_url('admin/orders/' . (int) $order['id']) ?>"><?= esc($order['order_number']) ?></a></td>
                            <td><?= esc($order['customer_name']) ?></td>
                            <td><?= format_date($order['created_at']) ?></td>
                            <td class="text-end text-nowrap"><?= format_rupiah($order['total']) ?></td>
                            <td><?= status_badge(payment_status_label($order['payment_status'])) ?></td>
                            <td><?= status_badge(order_status_label($order['order_status'])) ?></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.js"></script>
<script>
    (function () {
        var labels = <?= json_encode($chart['labels']) ?>;
        var values = <?= json_encode($chart['values']) ?>;

        new Chart(document.getElementById('salesChart'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{ label: 'Penjualan (Rp)', data: values, backgroundColor: '#2F5BEA', borderRadius: 6 }]
            },
            options: {
                maintainAspectRatio: false,
                animation: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: function (c) { return 'Rp' + c.parsed.y.toLocaleString('id-ID'); } } }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { callback: function (v) { return 'Rp' + (v / 1000).toLocaleString('id-ID') + 'rb'; } } },
                    x: { grid: { display: false } }
                }
            }
        });
    })();
</script>
<?= $this->endSection() ?>
