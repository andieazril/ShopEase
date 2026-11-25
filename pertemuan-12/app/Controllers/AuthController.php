<?php

namespace App\Controllers;

use App\Models\CartModel;
use App\Models\UserModel;

/**
 * AuthController (HOL Praktikum 6)
 *
 * register() / store()  : form dan proses registrasi customer
 * login()    / attempt(): form dan proses login
 * logout()              : mengakhiri session
 */
class AuthController extends BaseController
{
    protected $helpers = ['url', 'form', 'shopease'];

    /**
     * Batas percobaan login per IP per menit.
     */
    private const MAX_LOGIN_ATTEMPTS = 5;

    // ------------------------------------------------------------------
    // Registrasi
    // ------------------------------------------------------------------

    public function register()
    {
        if (session()->get('logged_in')) {
            return redirect()->to($this->homeFor(session()->get('role')));
        }

        return view('auth/register', ['title' => 'Daftar – ShopEase']);
    }

    public function store()
    {
        $rules = [
            'name' => [
                'label'  => 'Nama',
                'rules'  => 'required|min_length[3]|max_length[100]',
                'errors' => [
                    'required'   => 'Nama wajib diisi.',
                    'min_length' => 'Nama minimal 3 karakter.',
                    'max_length' => 'Nama maksimal 100 karakter.',
                ],
            ],
            'email' => [
                'label'  => 'Email',
                'rules'  => 'required|valid_email|max_length[100]|is_unique[users.email]',
                'errors' => [
                    'required'    => 'Email wajib diisi.',
                    'valid_email' => 'Format email tidak valid, contoh: nama@email.com.',
                    'max_length'  => 'Email maksimal 100 karakter.',
                    'is_unique'   => 'Email ini sudah terdaftar. Silakan masuk atau gunakan email lain.',
                ],
            ],
            'password' => [
                'label'  => 'Password',
                'rules'  => 'required|min_length[8]|max_length[72]',
                'errors' => [
                    'required'   => 'Password wajib diisi.',
                    'min_length' => 'Password minimal 8 karakter.',
                    'max_length' => 'Password maksimal 72 karakter.',
                ],
            ],
            'password_confirm' => [
                'label'  => 'Konfirmasi password',
                'rules'  => 'required|matches[password]',
                'errors' => [
                    'required' => 'Ulangi password Anda.',
                    'matches'  => 'Konfirmasi password tidak sama dengan password.',
                ],
            ],
        ];

        // Email disamakan ke huruf kecil sebelum dicek unik.
        $post          = $this->request->getPost(['name', 'email', 'password', 'password_confirm']);
        $post['name']  = trim((string) $post['name']);
        $post['email'] = strtolower(trim((string) $post['email']));

        if (! $this->validateData($post, $rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Role selalu 'customer'; tidak pernah diambil dari input form.
        (new UserModel())->insert([
            'name'     => $post['name'],
            'email'    => $post['email'],
            'password' => $post['password'], // di-hash oleh UserModel::hashPassword()
            'role'     => 'customer',
        ]);

        return redirect()->to('/login')
            ->with('success', 'Akun berhasil dibuat. Silakan masuk dengan email dan password Anda.')
            ->with('email', $post['email']);
    }

    // ------------------------------------------------------------------
    // Login
    // ------------------------------------------------------------------

    public function login()
    {
        if (session()->get('logged_in')) {
            return redirect()->to($this->homeFor(session()->get('role')));
        }

        return view('auth/login', ['title' => 'Masuk – ShopEase']);
    }

    public function attempt()
    {
        // Batasi percobaan login agar password tidak bisa ditebak terus-menerus.
        $throttler = service('throttler');
        $key       = 'login-' . md5($this->request->getIPAddress());

        if ($throttler->check($key, self::MAX_LOGIN_ATTEMPTS, MINUTE) === false) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terlalu banyak percobaan masuk. Tunggu ' . $throttler->getTokenTime() . ' detik, lalu coba lagi.');
        }

        $rules = [
            'email'    => ['rules' => 'required|valid_email', 'errors' => ['required' => 'Email wajib diisi.', 'valid_email' => 'Format email tidak valid.']],
            'password' => ['rules' => 'required', 'errors' => ['required' => 'Password wajib diisi.']],
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $email    = (string) $this->request->getPost('email');
        $password = (string) $this->request->getPost('password');
        $user     = (new UserModel())->findByEmail($email);

        // Pesan sengaja sama untuk email tidak terdaftar dan password salah,
        // agar tidak membocorkan email mana yang terdaftar (PRD bagian 13).
        if ($user === null || ! password_verify($password, $user['password'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Email atau password salah.');
        }

        // Cegah session fixation: buat ID session baru setelah login berhasil.
        $session     = session();
        $redirectUrl = $session->get('redirect_url');

        $session->regenerate(true);
        $session->remove('redirect_url');
        $session->set([
            'user_id'   => $user['id'],
            'name'      => $user['name'],
            'email'     => $user['email'],
            'role'      => $user['role'],
            'logged_in' => true,
            'cart_count' => $user['role'] === 'customer' ? (new CartModel())->countItems((int) $user['id']) : 0,
        ]);

        // Kembali ke halaman yang tadi diminta (jika ada), atau sesuai role.
        $target = is_string($redirectUrl) && str_starts_with($redirectUrl, base_url())
            ? $redirectUrl
            : $this->homeFor($user['role']);

        return redirect()->to($target)
            ->with('success', 'Selamat datang, ' . $user['name'] . '.');
    }

    // ------------------------------------------------------------------
    // Logout
    // ------------------------------------------------------------------

    public function logout()
    {
        // Hapus data login, lalu ganti ID session (session lama tidak bisa dipakai lagi).
        // Tidak memakai destroy() supaya pesan "Anda sudah keluar" masih bisa ditampilkan.
        $session = session();
        $session->remove(['user_id', 'name', 'email', 'role', 'logged_in', 'cart_count', 'redirect_url']);
        $session->regenerate(true);

        return redirect()->to('/login')
            ->with('success', 'Anda sudah keluar.');
    }

    /**
     * Halaman awal setelah login, sesuai role.
     */
    private function homeFor(?string $role): string
    {
        return in_array($role, ['admin', 'operator'], true) ? '/admin/dashboard' : '/';
    }
}
