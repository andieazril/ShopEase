<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table         = 'categories';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['name', 'slug', 'description'];

    /**
     * Semua kategori beserta jumlah produk aktif dan harga termurahnya.
     *
     * @return list<array<string, mixed>>
     */
    public function getWithStats(): array
    {
        return $this->select('categories.id, categories.name, categories.slug')
            ->select('COUNT(products.id) AS product_count, MIN(products.price) AS min_price')
            ->join('products', "products.category_id = categories.id AND products.status = 'active'", 'left')
            ->groupBy('categories.id, categories.name, categories.slug')
            ->orderBy('categories.id', 'ASC')
            ->findAll();
    }

    /**
     * Daftar kategori untuk admin beserta jumlah semua produk (aktif & nonaktif).
     */
    public function adminList(): static
    {
        return $this->select('categories.*, COUNT(products.id) AS product_count')
            ->join('products', 'products.category_id = categories.id', 'left')
            ->groupBy('categories.id, categories.name, categories.slug, categories.description, categories.created_at')
            ->orderBy('categories.name', 'ASC');
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->where('slug', $slug)->first();
    }

    /**
     * Jumlah produk (aktif maupun nonaktif) yang memakai kategori ini.
     */
    public function countProducts(int $id): int
    {
        return $this->db->table('products')->where('category_id', $id)->countAllResults();
    }

    /**
     * Slug unik dari nama, contoh: "Rumah Tangga" => "rumah-tangga",
     * atau "rumah-tangga-2" jika sudah dipakai kategori lain.
     */
    public function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = url_title($name, '-', true) ?: 'kategori';
        $slug = $base;
        $i    = 2;

        while (true) {
            $builder = $this->db->table($this->table)->where('slug', $slug);

            if ($ignoreId !== null) {
                $builder->where('id !=', $ignoreId);
            }

            if ($builder->countAllResults() === 0) {
                return $slug;
            }

            $slug = $base . '-' . $i++;
        }
    }
}
