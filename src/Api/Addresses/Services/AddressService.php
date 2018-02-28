<?php

namespace GetCandy\Api\Addresses\Services;

use GetCandy\Api\Scaffold\BaseService;
use GetCandy\Api\Addresses\Models\Address;

class AddressService extends BaseService
{
    public function __construct()
    {
        $this->model = new Address;
    }

    /**
     * Checks whether an address already exists
     *
     * @param string $user
     * @param array $details
     * @param string $type
     * 
     * @return boolean
     */
    public function exists($user, array $details, $type = 'billing')
    {
        // Fill it to make sure we only query against columns we actually have
        $address = new Address;
        $query = $this->model->where('user_id', '=', $user->id);
        foreach ($address->fill($details)->toArray() as $column => $value) {
            $query->where($column, '=', $value);
        }
        return $query->where($type, '=', true)->exists();
    }

    public function addAddress($user, $data, $type)
    {
        $data[$type] = true;
        return $this->create($user, $data);
    }

    /**
     * Create a new address
     *
     * @param User $user
     * @param array $data
     * 
     * @return Address
     */
    public function create($user, array $data)
    {
        $address = new Address;
        $address->fill($data);
        $address->user()->associate($user);
        $address->save();
        return $address;
    }
}
