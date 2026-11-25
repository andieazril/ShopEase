<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

/**
 * Halaman awal admin/operator. Dipakai untuk menguji AuthFilter:
 * hanya bisa dibuka oleh role admin dan operator.
 */
class DashboardController extends BaseController
{
    protected $helpers = ['url', 'form', 'shopease'];

    public function index()
    {
        $userModel = new UserModel();

        $data = [
            'title'      => 'Dashboard – ShopEase',
            'activeMenu' => 'dashboard',
            'userCounts' => [
                'customer' => $userModel->where('role', 'customer')->countAllResults(),
                'operator' => $userModel->where('role', 'operator')->countAllResults(),
                'admin'    => $userModel->where('role', 'admin')->countAllResults(),
            ],
        ];

        return view('admin/dashboard', $data);
    }
}
