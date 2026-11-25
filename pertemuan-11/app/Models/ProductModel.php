<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table         = 'products';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'category_id', 'name', 'slug', 'description',
        'price', 'stock', 'image', 'status',
    ];

    /**
     * Pilihan urutan di katalog: kunci URL => [kolom, arah].
     */
    public const SORTS = [
        'terbaru'  => ['products.created_at', 'DESC'],
        'termurah' => ['products.price', 'ASC'],
        'termahal' => ['products.price', 'DESC'],
        'nama'     => ['products.name', 'ASC'],
    ];

    /**
     * Query dasar: hanya produk aktif (PRD BR-002) + nama & slug kategori.
     */
    public function active(): static
    {
        return $this->select('products.*, categories.name AS category_name, categories.slug AS category_slug')
            ->join('categories', 'categories.id = products.category_id')
            ->where('products.status', 'active');
    }

    public function countActive(): int
    {
        return $this->where('status', 'active')->countAllResults();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getLatest(int $limit = 8): array
    {
        return $this->active()
            ->orderBy('products.created_at', 'DESC')
            ->orderBy('products.id', 'DESC')
            ->findAll($limit);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getBestSellers(int $limit = 4): array
    {
        return $this->active()
            ->where('products.sold >', 0)
            ->orderBy('products.sold', 'DESC')
            ->orderBy('products.id', 'ASC')
            ->findAll($limit);
    }

    /**
     * Filter katalog (HOL Praktikum 7): pencarian nama, kategori, dan urutan.
     * Mengembalikan model agar bisa langsung dipanggil ->paginate().
     */
    public function filter(?string $keyword, ?int $categoryId, string $sort): static
    {
        $this->active();

        if ($keyword !== null && $keyword !== '') {
            $this->groupStart()
                ->like('products.name', $keyword)
                ->orLike('products.description', $keyword)
                ->groupEnd();
        }

        if ($categoryId !== null) {
            $this->where('products.category_id', $categoryId);
        }

        [$column, $direction] = self::SORTS[$sort] ?? self::SORTS['terbaru'];

        return $this->orderBy($column, $direction)->orderBy('products.id', 'DESC');
    }

    /**
     * Satu produk aktif berdasarkan id, atau null jika tidak ada / nonaktif.
     */
    public function findActive(int $id): ?array
    {
        return $this->active()->where('products.id', $id)->first();
    }

    /**
     * Produk lain di kategori yang sama.
     *
     * @return list<array<string, mixed>>
     */
    public function getRelated(array $product, int $limit = 4): array
    {
        return $this->active()
            ->where('products.category_id', $product['category_id'])
            ->where('products.id !=', $product['id'])
            ->orderBy('products.created_at', 'DESC')
            ->findAll($limit);
    }
}
