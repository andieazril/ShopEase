<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CategoryModel;
use App\Models\ProductModel;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Admin\ProductController – manajemen produk.
 *
 * index, updateStock          : admin dan operator
 * create, store, edit, update,
 * delete                      : khusus admin
 */
class ProductController extends BaseController
{
    protected $helpers = ['url', 'form', 'shopease'];

    private const UPLOAD_DIR = 'uploads/products/';

    public function index()
    {
        $model      = new ProductModel();
        $keyword    = trim((string) $this->request->getGet('keyword'));
        $categoryId = (int) $this->request->getGet('category') ?: null;
        $status     = (string) $this->request->getGet('status');

        $products = $model->adminList($keyword, $categoryId, $status)->paginate(10);

        return view('admin/products/index', [
            'title'      => 'Produk – Admin ShopEase',
            'activeMenu' => 'admin-products',
            'products'   => $products,
            'pager'      => $model->pager,
            'categories' => (new CategoryModel())->orderBy('name')->findAll(),
            'filters'    => ['keyword' => $keyword, 'category' => $categoryId, 'status' => $status],
            'isAdmin'    => session('role') === 'admin',
        ]);
    }

    public function create()
    {
        return $this->form(null);
    }

    public function store()
    {
        if (! $this->validate($this->rules(true))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $model = new ProductModel();
        $data  = $this->formData();

        $data['slug']  = $model->uniqueSlug($data['name']);
        $data['image'] = $this->saveImage();

        $model->insert($data);

        return redirect()->to('/admin/products')->with('success', 'Produk "' . $data['name'] . '" ditambahkan.');
    }

    public function edit(int $id)
    {
        return $this->form($this->findOrFail($id));
    }

    public function update(int $id)
    {
        $product = $this->findOrFail($id);

        if (! $this->validate($this->rules(false))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $model = new ProductModel();
        $data  = $this->formData();

        $data['slug'] = $model->uniqueSlug($data['name'], $id);

        $newImage = $this->saveImage();

        if ($newImage !== null) {
            $data['image'] = $newImage;
            $this->deleteImage($product['image']);
        }

        $model->update($id, $data);

        return redirect()->to('/admin/products')->with('success', 'Produk "' . $data['name'] . '" diperbarui.');
    }

    /**
     * Produk yang pernah dipesan atau ada di keranjang tidak dihapus permanen,
     * hanya dinonaktifkan agar riwayat transaksi tetap utuh.
     */
    public function delete(int $id)
    {
        $product = $this->findOrFail($id);
        $model   = new ProductModel();

        if ($model->isReferenced($id)) {
            $model->update($id, ['status' => 'inactive']);

            return redirect()->to('/admin/products')->with(
                'success',
                'Produk "' . $product['name'] . '" sudah pernah dipesan atau ada di keranjang, jadi dinonaktifkan (tidak dihapus).',
            );
        }

        $model->delete($id);
        $this->deleteImage($product['image']);

        return redirect()->to('/admin/products')->with('success', 'Produk "' . $product['name'] . '" dihapus.');
    }

    /**
     * Ubah stok saja (admin dan operator).
     */
    public function updateStock(int $id)
    {
        $product = $this->findOrFail($id);

        $rules = [
            'stock' => [
                'rules'  => 'required|is_natural|less_than_equal_to[1000000]',
                'errors' => [
                    'required'            => 'Stok wajib diisi.',
                    'is_natural'          => 'Stok harus bilangan bulat 0 atau lebih.',
                    'less_than_equal_to'  => 'Stok terlalu besar.',
                ],
            ],
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->with('error', $product['name'] . ': ' . implode(' ', $this->validator->getErrors()));
        }

        $stock = (int) $this->request->getPost('stock');
        (new ProductModel())->update($id, ['stock' => $stock]);

        return redirect()->back()->with('success', 'Stok ' . $product['name'] . ' diubah menjadi ' . $stock . '.');
    }

    // ------------------------------------------------------------------

    private function form(?array $product)
    {
        return view('admin/products/form', [
            'title'      => ($product === null ? 'Tambah' : 'Ubah') . ' produk – Admin ShopEase',
            'activeMenu' => 'admin-products',
            'product'    => $product,
            'categories' => (new CategoryModel())->orderBy('name')->findAll(),
        ]);
    }

    private function findOrFail(int $id): array
    {
        $product = (new ProductModel())->find($id);

        if ($product === null) {
            throw PageNotFoundException::forPageNotFound('Produk tidak ditemukan.');
        }

        return $product;
    }

    private function formData(): array
    {
        return [
            'category_id' => (int) $this->request->getPost('category_id'),
            'name'        => trim((string) $this->request->getPost('name')),
            'description' => trim((string) $this->request->getPost('description')) ?: null,
            'price'       => (float) $this->request->getPost('price'),
            'stock'       => (int) $this->request->getPost('stock'),
            'status'      => (string) $this->request->getPost('status'),
        ];
    }

    private function rules(bool $isCreate): array
    {
        $image = $this->request->getFile('image');
        $hasImage = $image !== null && $image->getError() !== UPLOAD_ERR_NO_FILE;

        $rules = [
            'category_id' => [
                'rules'  => 'required|is_natural_no_zero|is_not_unique[categories.id]',
                'errors' => ['required' => 'Pilih kategori.', 'is_natural_no_zero' => 'Pilih kategori.', 'is_not_unique' => 'Kategori tidak valid.'],
            ],
            'name' => [
                'rules'  => 'required|min_length[3]|max_length[150]',
                'errors' => ['required' => 'Nama produk wajib diisi.', 'min_length' => 'Nama produk minimal 3 karakter.', 'max_length' => 'Nama produk maksimal 150 karakter.'],
            ],
            'description' => [
                'rules'  => 'permit_empty|max_length[2000]',
                'errors' => ['max_length' => 'Deskripsi maksimal 2000 karakter.'],
            ],
            'price' => [
                'rules'  => 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[9999999999]',
                'errors' => ['required' => 'Harga wajib diisi.', 'numeric' => 'Harga harus berupa angka.', 'greater_than_equal_to' => 'Harga tidak boleh negatif.', 'less_than_equal_to' => 'Harga terlalu besar.'],
            ],
            'stock' => [
                'rules'  => 'required|is_natural|less_than_equal_to[1000000]',
                'errors' => ['required' => 'Stok wajib diisi.', 'is_natural' => 'Stok harus bilangan bulat 0 atau lebih.', 'less_than_equal_to' => 'Stok terlalu besar.'],
            ],
            'status' => [
                'rules'  => 'required|in_list[active,inactive]',
                'errors' => ['required' => 'Pilih status.', 'in_list' => 'Status tidak valid.'],
            ],
        ];

        // Gambar wajib saat tambah produk, opsional saat ubah.
        if ($isCreate || $hasImage) {
            $rules['image'] = [
                'rules'  => 'uploaded[image]|max_size[image,2048]|is_image[image]|mime_in[image,image/jpeg,image/png,image/webp]|ext_in[image,jpg,jpeg,png,webp]',
                'errors' => [
                    'uploaded' => 'Pilih gambar produk.',
                    'max_size' => 'Ukuran gambar maksimal 2 MB.',
                    'is_image' => 'File harus berupa gambar.',
                    'mime_in'  => 'Format gambar harus JPG, PNG, atau WEBP.',
                    'ext_in'   => 'Format gambar harus JPG, PNG, atau WEBP.',
                ],
            ];
        }

        return $rules;
    }

    /**
     * Simpan gambar dengan nama acak buatan sistem. Mengembalikan nama file,
     * atau null jika tidak ada gambar yang diunggah.
     */
    private function saveImage(): ?string
    {
        $image = $this->request->getFile('image');

        if ($image === null || ! $image->isValid() || $image->hasMoved()) {
            return null;
        }

        $name = $image->getRandomName();
        $image->move(FCPATH . self::UPLOAD_DIR, $name);

        return $name;
    }

    private function deleteImage(?string $fileName): void
    {
        if ($fileName === null || $fileName === '') {
            return;
        }

        $path = FCPATH . self::UPLOAD_DIR . basename($fileName);

        if (is_file($path)) {
            unlink($path);
        }
    }
}
