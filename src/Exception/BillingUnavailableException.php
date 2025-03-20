<?php

namespace App\Exception;

class BillingUnavailableException extends \Exception
{
    public function __construct($message = "Сервис временно недоступен", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
