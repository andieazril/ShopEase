<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CategoryModel;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Admin\CategoryController – CRUD kategori produk (khusus admin).
 */
class CategoryController extends BaseController
{
    protected $helpers = ['url', 'form', 'shopease'];

    public function index()
    {
        $model      = new CategoryModel();
        $categories = $model->adminList()->paginate(10);

        return view('admin/categories/index', [
            'title'      => 'Kategori – Admin ShopEase',
            'activeMenu' => 'admin-categories',
            'categories' => $categories,
            'pager'      => $model->pager,
        ]);
    }

    public function create()
    {
        return view('admin/categories/form', [
            'title'      => 'Tambah kategori – Admin ShopEase',
            'activeMenu' => 'admin-categories',
            'category'   => null,
        ]);
    }

    public function store()
    {
        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $model = new CategoryModel();
        $name  = trim((string) $this->request->getPost('name'));

        $model->insert([
            'name'        => $name,
            'slug'        => $model->uniqueSlug($name),
            'description' => trim((string) $this->request->getPost('description')) ?: null,
        ]);

        return redirect()->to('/admin/categories')->with('success', 'Kategori "' . $name . '" ditambahkan.');
    }

    public function edit(int $id)
    {
        return view('admin/categories/form', [
            'title'      => 'Ubah kategori – Admin ShopEase',
            'activeMenu' => 'admin-categories',
            'category'   => $this->findOrFail($id),
        ]);
    }

    public function update(int $id)
    {
        $this->findOrFail($id);

        if (! $this->validate($this->rules($id))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $model = new CategoryModel();
        $name  = trim((string) $this->request->getPost('name'));

        $model->update($id, [
            'name'        => $name,
            'slug'        => $model->uniqueSlug($name, $id),
            'description' => trim((string) $this->request->getPost('description')) ?: null,
        ]);

        return redirect()->to('/admin/categories')->with('success', 'Kategori "' . $name . '" diperbarui.');
    }

    public function delete(int $id)
    {
        $category = $this->findOrFail($id);
        $model    = new CategoryModel();
        $used     = $model->countProducts($id);

        // Kategori yang masih dipakai produk tidak boleh dihapus,
        // supaya produk tidak kehilangan kategorinya.
        if ($used > 0) {
            return redirect()->to('/admin/categories')->with(
                'error',
                'Kategori "' . $category['name'] . '" masih dipakai ' . $used . ' produk. Pindahkan produk tersebut ke kategori lain dulu.',
            );
        }

        $model->delete($id);

        return redirect()->to('/admin/categories')->with('success', 'Kategori "' . $category['name'] . '" dihapus.');
    }

    private function findOrFail(int $id): array
    {
        $category = (new CategoryModel())->find($id);

        if ($category === null) {
            throw PageNotFoundException::forPageNotFound('Kategori tidak ditemukan.');
        }

        return $category;
    }

    private function rules(?int $id = null): array
    {
        return [
            'name' => [
                'rules'  => 'required|min_length[3]|max_length[100]|is_unique[categories.name,id,' . ($id ?? 0) . ']',
                'errors' => [
                    'required'   => 'Nama kategori wajib diisi.',
                    'min_length' => 'Nama kategori minimal 3 karakter.',
                    'max_length' => 'Nama kategori maksimal 100 karakter.',
                    'is_unique'  => 'Nama kategori sudah dipakai.',
                ],
            ],
            'description' => [
                'rules'  => 'permit_empty|max_length[1000]',
                'errors' => ['max_length' => 'Deskripsi maksimal 1000 karakter.'],
            ],
        ];
    }
}
