<?php

declare(strict_types=1);

namespace BeycanPress\CryptoPay\MemberDash\Models;

use BeycanPress\CryptoPay\Models\AbstractTransaction;

class TransactionsPro extends AbstractTransaction
{
    public string $addon = 'memberdash';

    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct('memberdash_transaction');
    }
}
