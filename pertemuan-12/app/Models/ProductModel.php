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

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

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
     * Query dasar: produk + nama & slug kategori.
     */
    public function withCategory(): static
    {
        return $this->select('products.*, categories.name AS category_name, categories.slug AS category_slug')
            ->join('categories', 'categories.id = products.category_id');
    }

    /**
     * Hanya produk aktif. Produk nonaktif tidak tampil dan tidak bisa dibeli.
     */
    public function active(): static
    {
        return $this->withCategory()->where('products.status', 'active');
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
     * Produk aktif terlaris, dihitung dari order_items pada pesanan yang
     * sudah dibayar dan diproses.
     *
     * @return list<array<string, mixed>>
     */
    public function getBestSellers(int $limit = 4): array
    {
        $sales = $this->db->table('order_items')
            ->select('order_items.product_id, SUM(order_items.quantity) AS total_sold')
            ->join('orders', 'orders.id = order_items.order_id')
            ->whereIn('orders.order_status', OrderModel::SOLD_STATUSES)
            ->groupBy('order_items.product_id')
            ->getCompiledSelect();

        return $this->active()
            ->select('sales.total_sold')
            ->join("({$sales}) AS sales", 'sales.product_id = products.id', 'inner', false)
            ->orderBy('sales.total_sold', 'DESC')
            ->orderBy('products.id', 'ASC')
            ->findAll($limit);
    }

    /**
     * Filter katalog: pencarian nama/deskripsi, kategori, dan urutan.
     * Mengembalikan model agar bisa langsung dipanggil ->paginate().
     */
    public function filter(?string $keyword, ?int $categoryId, string $sort): static
    {
        $this->active();
        $this->applySearch($keyword, $categoryId);

        [$column, $direction] = self::SORTS[$sort] ?? self::SORTS['terbaru'];

        return $this->orderBy($column, $direction)->orderBy('products.id', 'DESC');
    }

    /**
     * Daftar produk di halaman admin (termasuk nonaktif) dengan pencarian
     * dan filter kategori/status.
     */
    public function adminList(?string $keyword, ?int $categoryId, ?string $status): static
    {
        $this->withCategory();
        $this->applySearch($keyword, $categoryId);

        if (in_array($status, ['active', 'inactive'], true)) {
            $this->where('products.status', $status);
        }

        return $this->orderBy('products.id', 'DESC');
    }

    private function applySearch(?string $keyword, ?int $categoryId): void
    {
        if ($keyword !== null && $keyword !== '') {
            $this->groupStart()
                ->like('products.name', $keyword)
                ->orLike('products.description', $keyword)
                ->groupEnd();
        }

        if ($categoryId !== null) {
            $this->where('products.category_id', $categoryId);
        }
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

    /**
     * Apakah produk pernah dipesan atau sedang ada di keranjang.
     * Produk seperti ini tidak boleh dihapus permanen, hanya dinonaktifkan.
     */
    public function isReferenced(int $id): bool
    {
        return $this->db->table('order_items')->where('product_id', $id)->countAllResults() > 0
            || $this->db->table('cart_items')->where('product_id', $id)->countAllResults() > 0;
    }

    /**
     * Kurangi stok hanya jika stoknya masih cukup (atomic).
     * Mengembalikan false jika stok tidak cukup, jadi tidak pernah minus.
     */
    public function decreaseStock(int $id, int $quantity): bool
    {
        $this->db->table('products')
            ->set('stock', 'stock - ' . (int) $quantity, false)
            ->where('id', $id)
            ->where('stock >=', $quantity)
            ->where('status', 'active')
            ->update();

        return $this->db->affectedRows() === 1;
    }

    /**
     * Slug unik dari nama produk, contoh: "Kaos Katun Basic" => "kaos-katun-basic".
     */
    public function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = url_title($name, '-', true) ?: 'produk';
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

    public function increaseStock(int $id, int $quantity): void
    {
        $this->db->table('products')
            ->set('stock', 'stock + ' . (int) $quantity, false)
            ->where('id', $id)
            ->update();
    }
}
