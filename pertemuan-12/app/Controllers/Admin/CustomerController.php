<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

/**
 * Admin\CustomerController – data pelanggan (khusus admin, hanya baca).
 */
class CustomerController extends BaseController
{
    protected $helpers = ['url', 'form', 'shopease'];

    public function index()
    {
        $model   = new UserModel();
        $keyword = trim((string) $this->request->getGet('keyword'));

        $model->select('users.id, users.name, users.email, users.created_at')
            ->select('COUNT(orders.id) AS order_count')
            ->select("COALESCE(SUM(CASE WHEN orders.order_status IN ('processing','shipped','completed') THEN orders.total ELSE 0 END), 0) AS total_spent")
            ->join('orders', 'orders.user_id = users.id', 'left')
            ->where('users.role', 'customer');

        if ($keyword !== '') {
            $model->groupStart()->like('users.name', $keyword)->orLike('users.email', $keyword)->groupEnd();
        }

        $customers = $model->groupBy('users.id, users.name, users.email, users.created_at')
            ->orderBy('users.created_at', 'DESC')
            ->paginate(10);

        return view('admin/customers/index', [
            'title'      => 'Pelanggan – Admin ShopEase',
            'activeMenu' => 'admin-customers',
            'customers'  => $customers,
            'pager'      => $model->pager,
            'keyword'    => $keyword,
        ]);
    }
}
