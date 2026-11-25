<?php

namespace App\Controllers;

use App\Models\OrderModel;
use App\Models\PaymentModel;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * OrderController – pesanan milik customer.
 *
 * index()             : riwayat pesanan
 * detail($id)         : detail pesanan, instruksi bayar, status pengiriman
 * uploadPayment($id)  : unggah bukti transfer
 * proof($id)          : tampilkan bukti transfer yang sudah diunggah
 *
 * Route dilindungi filter auth:customer. Customer hanya bisa membuka
 * pesanan miliknya sendiri; pesanan orang lain dianggap tidak ada (404).
 */
class OrderController extends BaseController
{
    protected $helpers = ['url', 'form', 'shopease'];

    /**
     * Rekening tujuan transfer (data contoh).
     */
    private const BANK_ACCOUNT = [
        'bank'   => 'Bank Contoh',
        'number' => '1234567890',
        'holder' => 'ShopEase',
    ];

    public function index()
    {
        $orderModel = new OrderModel();
        $orders     = $orderModel->withDetails()
            ->where('orders.user_id', session('user_id'))
            ->orderBy('orders.created_at', 'DESC')
            ->paginate(10);

        return view('frontend/orders/index', [
            'title'      => 'Pesanan saya – ShopEase',
            'activeMenu' => 'orders',
            'orders'     => $orders,
            'pager'      => $orderModel->pager,
        ]);
    }

    public function detail(int $id)
    {
        $orderModel = new OrderModel();
        $order      = $this->findOwnedOrFail($id);

        return view('frontend/orders/detail', [
            'title'      => 'Pesanan ' . $order['order_number'] . ' – ShopEase',
            'activeMenu' => 'orders',
            'order'      => $order,
            'items'      => $orderModel->items($id),
            'address'    => $orderModel->address($order['address_id'] !== null ? (int) $order['address_id'] : null),
            'canUpload'  => $this->canUploadPayment($order),
            'bank'       => self::BANK_ACCOUNT,
        ]);
    }

    public function uploadPayment(int $id)
    {
        $order = $this->findOwnedOrFail($id);

        if (! $this->canUploadPayment($order)) {
            return redirect()->to('/orders/' . $id)->with('error', 'Bukti pembayaran tidak bisa diunggah untuk pesanan dengan status ini.');
        }

        $rules = [
            'proof' => [
                'rules'  => 'uploaded[proof]|max_size[proof,2048]|is_image[proof]|mime_in[proof,image/jpeg,image/png,image/webp]|ext_in[proof,jpg,jpeg,png,webp]',
                'errors' => [
                    'uploaded' => 'Pilih file bukti pembayaran.',
                    'max_size' => 'Ukuran file maksimal 2 MB.',
                    'is_image' => 'File harus berupa gambar.',
                    'mime_in'  => 'Format file harus JPG, PNG, atau WEBP.',
                    'ext_in'   => 'Format file harus JPG, PNG, atau WEBP.',
                ],
            ],
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/orders/' . $id)->with('errors', $this->validator->getErrors());
        }

        $file = $this->request->getFile('proof');

        // Nama file dibuat acak oleh sistem, disimpan di writable/ (bukan public/).
        $fileName = $file->getRandomName();
        $file->move(PaymentModel::proofPath(), $fileName);

        $paymentModel = new PaymentModel();
        $payment      = $paymentModel->forOrder($id);

        $db = db_connect();
        $db->transStart();

        $paymentModel->update($payment['id'], [
            'proof_file'     => $fileName,
            'payment_status' => 'submitted',
            'verified_by'    => null,
            'verified_at'    => null,
        ]);
        (new OrderModel())->update($id, ['order_status' => 'waiting_verification']);

        $db->transComplete();

        return redirect()->to('/orders/' . $id)
            ->with('success', 'Bukti pembayaran terkirim. Admin akan memeriksanya pada jam layanan.');
    }

    public function proof(int $id)
    {
        $order = $this->findOwnedOrFail($id);

        return self::proofResponse($this->response, $order['proof_file'] ?? null);
    }

    /**
     * Kirim file bukti bayar ke browser. Dipakai juga oleh halaman admin.
     */
    public static function proofResponse($response, ?string $fileName)
    {
        $path = PaymentModel::proofPath() . basename((string) $fileName);

        if ($fileName === null || $fileName === '' || ! is_file($path)) {
            throw PageNotFoundException::forPageNotFound('Bukti pembayaran tidak ditemukan.');
        }

        return $response
            ->setHeader('Content-Type', mime_content_type($path) ?: 'application/octet-stream')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('Content-Disposition', 'inline; filename="' . basename($path) . '"')
            ->setBody((string) file_get_contents($path));
    }

    private function findOwnedOrFail(int $id): array
    {
        $order = (new OrderModel())->findOwned($id, (int) session('user_id'));

        if ($order === null) {
            throw PageNotFoundException::forPageNotFound('Pesanan tidak ditemukan.');
        }

        return $order;
    }

    /**
     * Bukti boleh diunggah selama pesanan menunggu pembayaran
     * (belum pernah mengirim bukti, atau bukti sebelumnya ditolak).
     */
    private function canUploadPayment(array $order): bool
    {
        return $order['order_status'] === 'pending_payment'
            && in_array($order['payment_status'], ['unpaid', 'rejected'], true);
    }
}
