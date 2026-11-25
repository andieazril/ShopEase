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
