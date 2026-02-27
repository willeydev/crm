<?php

namespace App\Domain\Cep;

interface CepGatewayInterface
{
    public function lookup(string $cep): ?array;
}
