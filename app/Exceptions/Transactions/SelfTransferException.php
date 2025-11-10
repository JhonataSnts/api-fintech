<?php

namespace App\Exceptions\Transactions;

use Exception;

class SelfTransferException extends Exception
{
    protected $message = 'Não é possível transferir para si mesmo';
}