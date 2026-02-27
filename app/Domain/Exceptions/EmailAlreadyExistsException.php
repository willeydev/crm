<?php

namespace App\Domain\Exceptions;

class EmailAlreadyExistsException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('E-mail já cadastrado.');
    }
}
