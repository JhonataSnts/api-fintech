<?php

namespace App\Exceptions\Transactions;

use Exception;

class InsufficientFundsException extends Exception
{
    protected $message = 'Saldo insuficiente';
}