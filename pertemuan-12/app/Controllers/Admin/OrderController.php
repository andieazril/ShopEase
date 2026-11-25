<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Controllers\OrderController as CustomerOrderController;
use App\Models\OrderModel;
use App\Models\PaymentModel;
use App\Models\ProductModel;
use App\Models\ShipmentModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Throwable;

/**
 * Admin\OrderController – pemrosesan pesanan.
 *
 * index, detail, proof, ship, complete : admin dan operator
 * verify, cancel                       : khusus admin
 *
 * Setiap perubahan status dicek di server: aksi hanya berjalan jika
 * status pesanan saat ini memang mengizinkannya.
 */
class OrderController extends BaseController
{
    protected $helpers = ['url', 'form', 'shopease'];

    public function index()
    {
        $model   = new OrderModel();
        $status  = (string) $this->request->getGet('status');
        $keyword = trim((string) $this->request->getGet('keyword'));

        $model->withDetails();

        if (in_array($status, OrderModel::STATUSES, true)) {
            $model->where('orders.order_status', $status);
        }

        if ($keyword !== '') {
            $model->groupStart()
                ->like('orders.order_number', $keyword)
                ->orLike('users.name', $keyword)
                ->orLike('users.email', $keyword)
                ->groupEnd();
        }

        $orders = $model->orderBy('orders.created_at', 'DESC')->paginate(10);

        return view('admin/orders/index', [
            'title'      => 'Pesanan – Admin ShopEase',
            'activeMenu' => 'admin-orders',
            'orders'     => $orders,
            'pager'      => $model->pager,
            'status'     => $status,
            'keyword'    => $keyword,
        ]);
    }

    public function detail(int $id)
    {
        $model = new OrderModel();
        $order = $this->findOrFail($id);
        $role  = session('role');

        return view('admin/orders/detail', [
            'title'      => 'Pesanan ' . $order['order_number'] . ' – Admin ShopEase',
            'activeMenu' => 'admin-orders',
            'order'      => $order,
            'items'      => $model->items($id),
            'address'    => $model->address($order['address_id'] !== null ? (int) $order['address_id'] : null),
            'verifier'   => $this->verifierName($id),
            'can'        => [
                'verify'   => $role === 'admin' && $order['order_status'] === 'waiting_verification' && $order['payment_status'] === 'submitted',
                'ship'     => $order['order_status'] === 'processing',
                'complete' => $order['order_status'] === 'shipped',
                'cancel'   => $role === 'admin' && in_array($order['order_status'], OrderModel::CANCELLABLE_STATUSES, true),
            ],
        ]);
    }

    public function proof(int $id)
    {
        $order = $this->findOrFail($id);

        return CustomerOrderController::proofResponse($this->response, $order['proof_file'] ?? null);
    }

    /**
     * Terima atau tolak bukti pembayaran (khusus admin).
     */
    public function verify(int $id)
    {
        $order    = $this->findOrFail($id);
        $decision = (string) $this->request->getPost('decision');

        if ($order['order_status'] !== 'waiting_verification' || $order['payment_status'] !== 'submitted') {
            return $this->back($id, 'error', 'Pembayaran pesanan ini tidak sedang menunggu verifikasi.');
        }

        if (! in_array($decision, ['approve', 'reject'], true)) {
            return $this->back($id, 'error', 'Pilihan verifikasi tidak valid.');
        }

        $approved = $decision === 'approve';
        $payment  = (new PaymentModel())->forOrder($id);

        $db = db_connect();
        $db->transStart();

        (new PaymentModel())->update($payment['id'], [
            'payment_status' => $approved ? 'verified' : 'rejected',
            'verified_by'    => session('user_id'),
            'verified_at'    => date('Y-m-d H:i:s'),
        ]);

        // Diterima → pesanan diproses. Ditolak → kembali menunggu pembayaran
        // agar customer bisa mengunggah bukti baru.
        (new OrderModel())->update($id, ['order_status' => $approved ? 'processing' : 'pending_payment']);

        $db->transComplete();

        return $this->back($id, 'success', $approved
            ? 'Pembayaran diterima. Pesanan masuk tahap diproses.'
            : 'Bukti pembayaran ditolak. Customer diminta mengunggah ulang.');
    }

    /**
     * Isi kurir dan nomor resi, lalu tandai pesanan dikirim.
     */
    public function ship(int $id)
    {
        $order = $this->findOrFail($id);

        if ($order['order_status'] !== 'processing') {
            return $this->back($id, 'error', 'Hanya pesanan berstatus Diproses yang bisa dikirim.');
        }

        $rules = [
            'courier'         => ['rules' => 'required|max_length[100]', 'errors' => ['required' => 'Nama kurir wajib diisi.', 'max_length' => 'Nama kurir maksimal 100 karakter.']],
            'tracking_number' => ['rules' => 'required|alpha_dash|max_length[100]', 'errors' => ['required' => 'Nomor resi wajib diisi.', 'alpha_dash' => 'Nomor resi hanya boleh huruf, angka, dan tanda hubung.', 'max_length' => 'Nomor resi maksimal 100 karakter.']],
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/admin/orders/' . $id)->withInput()->with('errors', $this->validator->getErrors());
        }

        $shipmentModel = new ShipmentModel();
        $data          = [
            'order_id'        => $id,
            'courier'         => trim((string) $this->request->getPost('courier')),
            'tracking_number' => trim((string) $this->request->getPost('tracking_number')),
            'shipment_status' => 'shipped',
            'shipped_at'      => date('Y-m-d H:i:s'),
        ];

        $db = db_connect();
        $db->transStart();

        $existing = $shipmentModel->forOrder($id);

        if ($existing !== null) {
            $shipmentModel->update($existing['id'], $data);
        } else {
            $shipmentModel->insert($data);
        }

        (new OrderModel())->update($id, ['order_status' => 'shipped']);

        $db->transComplete();

        return $this->back($id, 'success', 'Pesanan ditandai dikirim dengan resi ' . $data['tracking_number'] . '.');
    }

    /**
     * Tandai paket sudah diterima → pesanan selesai.
     */
    public function complete(int $id)
    {
        $order = $this->findOrFail($id);

        if ($order['order_status'] !== 'shipped') {
            return $this->back($id, 'error', 'Hanya pesanan berstatus Dikirim yang bisa diselesaikan.');
        }

        $shipmentModel = new ShipmentModel();
        $shipment      = $shipmentModel->forOrder($id);

        $db = db_connect();
        $db->transStart();

        $shipmentModel->update($shipment['id'], ['shipment_status' => 'delivered', 'delivered_at' => date('Y-m-d H:i:s')]);
        (new OrderModel())->update($id, ['order_status' => 'completed']);

        $db->transComplete();

        return $this->back($id, 'success', 'Pesanan selesai.');
    }

    /**
     * Batalkan pesanan yang belum dibayar/diverifikasi (khusus admin).
     * Stok dikembalikan. Pesanan tidak dihapus agar riwayatnya tetap ada.
     */
    public function cancel(int $id)
    {
        $order = $this->findOrFail($id);

        if (! in_array($order['order_status'], OrderModel::CANCELLABLE_STATUSES, true)) {
            return $this->back($id, 'error', 'Pesanan yang sudah dibayar dan diverifikasi tidak bisa dibatalkan.');
        }

        $orderModel   = new OrderModel();
        $productModel = new ProductModel();
        $db           = db_connect();

        $db->transBegin();

        try {
            foreach ($orderModel->items($id) as $item) {
                $productModel->increaseStock((int) $item['product_id'], (int) $item['quantity']);
            }

            $orderModel->update($id, ['order_status' => 'cancelled']);

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi gagal.');
            }

            $db->transCommit();
        } catch (Throwable $e) {
            $db->transRollback();
            log_message('error', 'Batal pesanan gagal: ' . $e->getMessage());

            return $this->back($id, 'error', 'Pesanan gagal dibatalkan. Tidak ada data yang berubah.');
        }

        return $this->back($id, 'success', 'Pesanan dibatalkan dan stok produk dikembalikan.');
    }

    // ------------------------------------------------------------------

    private function findOrFail(int $id): array
    {
        $order = (new OrderModel())->findWithDetails($id);

        if ($order === null) {
            throw PageNotFoundException::forPageNotFound('Pesanan tidak ditemukan.');
        }

        return $order;
    }

    private function verifierName(int $orderId): ?string
    {
        $row = db_connect()->table('payments')
            ->select('users.name')
            ->join('users', 'users.id = payments.verified_by')
            ->where('payments.order_id', $orderId)
            ->get()
            ->getRowArray();

        return $row['name'] ?? null;
    }

    private function back(int $id, string $type, string $message)
    {
        return redirect()->to('/admin/orders/' . $id)->with($type, $message);
    }
}
