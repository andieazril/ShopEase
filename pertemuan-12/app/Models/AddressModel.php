<?php

namespace App\Models;

use CodeIgniter\Model;

class AddressModel extends Model
{
    protected $table         = 'addresses';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'user_id', 'recipient_name', 'phone', 'address',
        'city', 'province', 'postal_code', 'is_default',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function forUser(int $userId): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('is_default', 'DESC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    /**
     * Alamat berdasarkan id, hanya jika milik user tersebut.
     */
    public function findOwned(int $id, int $userId): ?array
    {
        return $this->where('id', $id)->where('user_id', $userId)->first();
    }
}
