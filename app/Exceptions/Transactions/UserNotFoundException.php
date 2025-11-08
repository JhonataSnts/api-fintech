<?php

namespace App\Exceptions\Transactions;

use Exception;

class UserNotFoundException extends Exception
{
    protected $message = 'Usuário não encontrado';
}