<?php

namespace App\Exception;

class AccessDeniedException extends \Exception
{
    public function __construct($message = "Доступ запрещён", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
