<?php
namespace Domain\Balances\Exceptions;

use Exception;

class InsufficientBalanceException extends Exception
{
    protected $message = 'There is not sufficient balance';
}
