<?php

namespace App\Controllers;

use App\Models\AddressModel;
use App\Models\CartModel;
use App\Models\OrderItemModel;
use App\Models\OrderModel;
use App\Models\PaymentModel;
use App\Models\ProductModel;
use DomainException;
use RuntimeException;
use Throwable;

/**
 * CheckoutController – mengubah isi keranjang menjadi pesanan.
 *
 * index() : pilih alamat, ringkasan pesanan, total
 * store() : buat pesanan dalam satu transaksi database
 *
 * Route dilindungi filter auth:customer.
 */
class CheckoutController extends BaseController
{
    protected $helpers = ['url', 'form', 'shopease'];

    public function index()
    {
        $userId = (int) session('user_id');
        $cart   = (new CartModel())->summary($userId);

        if ($cart['items'] === []) {
            return redirect()->to('/cart')->with('error', 'Keranjang masih kosong. Tambahkan produk dulu sebelum checkout.');
        }

        if ($cart['hasProblem']) {
            return redirect()->to('/cart')->with('error', 'Perbaiki item yang ditandai di keranjang sebelum checkout.');
        }

        return view('frontend/checkout', [
            'title'        => 'Checkout – ShopEase',
            'activeMenu'   => 'cart',
            'cart'         => $cart,
            'addresses'    => (new AddressModel())->forUser($userId),
            'shippingCost' => OrderModel::SHIPPING_COST,
        ]);
    }

    public function store()
    {
        $userId       = (int) session('user_id');
        $cartModel    = new CartModel();
        $addressModel = new AddressModel();

        // Keranjang dibaca ulang dari database. Jika sudah kosong (misalnya
        // tombol "Buat pesanan" ditekan dua kali), pesanan tidak dibuat lagi.
        $cart = $cartModel->summary($userId);

        if ($cart['items'] === []) {
            return redirect()->to('/orders')->with('error', 'Keranjang kosong. Jika Anda baru saja checkout, pesanan Anda ada di daftar ini.');
        }

        if ($cart['hasProblem']) {
            return redirect()->to('/cart')->with('error', 'Stok atau status produk berubah. Periksa kembali keranjang Anda.');
        }

        // ---- Validasi alamat ----
        $option = (string) $this->request->getPost('address_option');
        $notes  = trim((string) $this->request->getPost('notes'));

        if (mb_strlen($notes) > 500) {
            return redirect()->back()->withInput()->with('errors', ['notes' => 'Catatan maksimal 500 karakter.']);
        }

        $newAddress = null;
        $addressId  = null;

        if ($option === 'new') {
            $rules = [
                'recipient_name' => ['rules' => 'required|max_length[100]', 'errors' => ['required' => 'Nama penerima wajib diisi.']],
                'phone'          => ['rules' => 'required|regex_match[/^[0-9+\- ]{8,20}$/]', 'errors' => ['required' => 'Nomor telepon wajib diisi.', 'regex_match' => 'Nomor telepon hanya boleh angka, 8–20 digit.']],
                'address'        => ['rules' => 'required|max_length[500]', 'errors' => ['required' => 'Alamat wajib diisi.']],
                'city'           => ['rules' => 'required|max_length[100]', 'errors' => ['required' => 'Kota wajib diisi.']],
                'province'       => ['rules' => 'required|max_length[100]', 'errors' => ['required' => 'Provinsi wajib diisi.']],
                'postal_code'    => ['rules' => 'required|regex_match[/^[0-9]{5}$/]', 'errors' => ['required' => 'Kode pos wajib diisi.', 'regex_match' => 'Kode pos harus 5 angka.']],
            ];

            if (! $this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $newAddress = $this->request->getPost(['recipient_name', 'phone', 'address', 'city', 'province', 'postal_code']);
            $newAddress = array_map(static fn ($v) => trim((string) $v), $newAddress);
        } else {
            $address = $addressModel->findOwned((int) $option, $userId);

            if ($address === null) {
                return redirect()->back()->withInput()->with('errors', ['address_option' => 'Pilih alamat pengiriman atau isi alamat baru.']);
            }

            $addressId = (int) $address['id'];
        }

        // ---- Hitung ulang total di server ----
        $subtotal = 0.0;

        foreach ($cart['items'] as $item) {
            $subtotal += (float) $item['price'] * (int) $item['quantity'];
        }

        $shipping = (float) OrderModel::SHIPPING_COST;
        $total    = $subtotal + $shipping;

        // ---- Simpan pesanan dalam satu transaksi ----
        $db           = db_connect();
        $orderModel   = new OrderModel();
        $productModel = new ProductModel();

        $db->transBegin();

        try {
            if ($newAddress !== null) {
                $isFirst   = $addressModel->where('user_id', $userId)->countAllResults() === 0;
                $addressId = (int) $addressModel->insert($newAddress + ['user_id' => $userId, 'is_default' => $isFirst ? 1 : 0]);
            }

            $orderId = (int) $orderModel->insert([
                'user_id'       => $userId,
                'address_id'    => $addressId,
                'order_number'  => $orderModel->generateOrderNumber(),
                'subtotal'      => $subtotal,
                'shipping_cost' => $shipping,
                'total'         => $total,
                'order_status'  => 'pending_payment',
                'notes'         => $notes !== '' ? $notes : null,
            ]);

            $orderItems = new OrderItemModel();

            foreach ($cart['items'] as $item) {
                // Stok dikurangi saat pesanan dibuat. Query hanya berhasil jika
                // stok masih cukup, jadi dua checkout bersamaan tidak bisa
                // membuat stok minus.
                if (! $productModel->decreaseStock((int) $item['product_id'], (int) $item['quantity'])) {
                    throw new DomainException($item['name']);
                }

                $orderItems->insert([
                    'order_id'     => $orderId,
                    'product_id'   => $item['product_id'],
                    'product_name' => $item['name'],   // salinan nama saat transaksi
                    'price'        => $item['price'],  // salinan harga saat transaksi
                    'quantity'     => $item['quantity'],
                    'subtotal'     => (float) $item['price'] * (int) $item['quantity'],
                ]);
            }

            (new PaymentModel())->insert([
                'order_id'       => $orderId,
                'method'         => 'transfer_bank',
                'amount'         => $total,
                'payment_status' => 'unpaid',
            ]);

            $cartModel->clear($userId);

            if ($db->transStatus() === false) {
                throw new RuntimeException('Transaksi gagal.');
            }

            $db->transCommit();
        } catch (DomainException $e) {
            $db->transRollback();

            return redirect()->to('/cart')->with('error', 'Stok ' . $e->getMessage() . ' tidak mencukupi. Pesanan belum dibuat.');
        } catch (Throwable $e) {
            $db->transRollback();
            log_message('error', 'Checkout gagal: ' . $e->getMessage());

            return redirect()->to('/checkout')->with('error', 'Pesanan gagal dibuat. Tidak ada perubahan pada keranjang dan stok. Silakan coba lagi.');
        }

        session()->set('cart_count', 0);

        return redirect()->to('/orders/' . $orderId)
            ->with('success', 'Pesanan berhasil dibuat. Silakan transfer sesuai total, lalu unggah bukti pembayaran.');
    }
}

