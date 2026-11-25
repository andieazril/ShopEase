<?php

/**
 * ShopEase helper
 *
 * Fungsi bantu tampilan yang dipakai di beberapa view.
 * Muat dengan helper('shopease') atau lewat properti $helpers di controller.
 */

if (! function_exists('format_rupiah')) {
    /**
     * Format angka menjadi Rupiah, contoh: 125000 => "Rp125.000".
     */
    function format_rupiah(int|float|string|null $amount): string
    {
        return 'Rp' . number_format((float) $amount, 0, ',', '.');
    }
}

if (! function_exists('product_image_url')) {
    /**
     * URL gambar produk jika file ada di public/uploads/products,
     * atau null agar view menampilkan placeholder ikon kategori.
     */
    function product_image_url(?string $filename): ?string
    {
        if ($filename === null || $filename === '') {
            return null;
        }

        $name = basename($filename);

        return is_file(FCPATH . 'uploads/products/' . $name)
            ? base_url('uploads/products/' . rawurlencode($name))
            : null;
    }
}

if (! function_exists('category_style')) {
    /**
     * Ikon (Bootstrap Icons) dan warna latar untuk tiap kategori,
     * berdasarkan slug. Kategori baru otomatis memakai gaya bawaan.
     *
     * @return array{icon: string, tone: string}
     */
    function category_style(?string $slug): array
    {
        $styles = [
            'elektronik'   => ['icon' => 'bi-headphones', 'tone' => '#DCE6FF'],
            'fashion'      => ['icon' => 'bi-bag',        'tone' => '#FFE7D6'],
            'aksesoris'    => ['icon' => 'bi-smartwatch', 'tone' => '#E7E2FA'],
            'rumah-tangga' => ['icon' => 'bi-cup-hot',    'tone' => '#DDF3E8'],
            'kecantikan'   => ['icon' => 'bi-droplet',    'tone' => '#F7DDEB'],
            'olahraga'     => ['icon' => 'bi-bicycle',    'tone' => '#FFF1C2'],
        ];

        return $styles[$slug ?? ''] ?? ['icon' => 'bi-box-seam', 'tone' => '#EEF1F6'];
    }
}

if (! function_exists('order_status_label')) {
    /**
     * Label dan warna badge untuk status pesanan.
     *
     * @return array{label: string, class: string}
     */
    function order_status_label(?string $status): array
    {
        return [
            'pending_payment'      => ['label' => 'Menunggu pembayaran', 'class' => 'text-bg-warning'],
            'waiting_verification' => ['label' => 'Menunggu verifikasi', 'class' => 'text-bg-info'],
            'processing'           => ['label' => 'Diproses',            'class' => 'text-bg-primary'],
            'shipped'              => ['label' => 'Dikirim',             'class' => 'text-bg-dark'],
            'completed'            => ['label' => 'Selesai',             'class' => 'text-bg-success'],
            'cancelled'            => ['label' => 'Dibatalkan',          'class' => 'text-bg-secondary'],
        ][$status ?? ''] ?? ['label' => (string) $status, 'class' => 'text-bg-light'];
    }
}

if (! function_exists('payment_status_label')) {
    /**
     * Label dan warna badge untuk status pembayaran.
     *
     * @return array{label: string, class: string}
     */
    function payment_status_label(?string $status): array
    {
        return [
            'unpaid'    => ['label' => 'Belum dibayar',       'class' => 'text-bg-warning'],
            'submitted' => ['label' => 'Bukti dikirim',       'class' => 'text-bg-info'],
            'verified'  => ['label' => 'Terverifikasi',       'class' => 'text-bg-success'],
            'rejected'  => ['label' => 'Ditolak',             'class' => 'text-bg-danger'],
        ][$status ?? ''] ?? ['label' => (string) $status, 'class' => 'text-bg-light'];
    }
}

if (! function_exists('status_badge')) {
    /**
     * HTML badge dari hasil order_status_label() / payment_status_label().
     *
     * @param array{label: string, class: string} $status
     */
    function status_badge(array $status): string
    {
        return '<span class="badge ' . esc($status['class'], 'attr') . '">' . esc($status['label']) . '</span>';
    }
}

if (! function_exists('format_date')) {
    /**
     * Format tanggal, contoh: "25/11/2026 13:05".
     */
    function format_date(?string $datetime, bool $withTime = true): string
    {
        if ($datetime === null || $datetime === '') {
            return '-';
        }

        return date($withTime ? 'd/m/Y H:i' : 'd/m/Y', strtotime($datetime));
    }
}
