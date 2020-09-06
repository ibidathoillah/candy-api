<?php

namespace GetCandy\Api\Core\Payments\Providers;

use GetCandy\Api\Core\Payments\PaymentResponse;

class Online extends AbstractProvider
{
    protected $name = 'Online';

    public function getName()
    {
        return $this->name;
    }

    protected function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function validate($token)
    {
        return true;
    }

    public function getClientToken()
    {
        return 'Online';
    }

    public function updateTransaction($transaction)
    {
        return true;
    }

    public function charge()
    {
        return new PaymentResponse(true);
    }

    public function refund($token, $amount, $description)
    {
        return true;
    }
}
