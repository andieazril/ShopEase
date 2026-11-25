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

    public function findBySlug(string $slug): ?array
    {
        return $this->where('slug', $slug)->first();
    }
}
