<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * AuthFilter (HOL Praktikum 6, Langkah 3)
 *
 * Pemakaian di Routes.php:
 *   ['filter' => 'auth']                 → wajib login, role apa saja
 *   ['filter' => 'auth:admin']           → hanya admin
 *   ['filter' => 'auth:admin,operator']  → admin atau operator
 */
class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // 1. Belum login → ke halaman login, simpan URL tujuan.
        if (! $session->get('logged_in')) {
            // Simpan halaman tujuan agar setelah login user dikembalikan ke sana.
            // Untuk form POST (mis. tambah ke keranjang) yang disimpan halaman asal form-nya.
            $session->set('redirect_url', $request->getMethod() === 'GET' ? current_url(true)->__toString() : previous_url());

            return redirect()->to('/login')
                ->with('error', 'Silakan masuk terlebih dahulu untuk membuka halaman tersebut.');
        }

        // 2. Sudah login tapi role tidak diizinkan → ditolak di server,
        //    walaupun URL diketik langsung (PRD bagian 13 – Otorisasi).
        if ($arguments !== null && $arguments !== []
            && ! in_array($session->get('role'), $arguments, true)) {
            return redirect()->to('/')
                ->with('error', 'Akun Anda tidak memiliki akses ke halaman tersebut.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada proses setelah request.
    }
}
