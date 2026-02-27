<?php

namespace App\Infrastructure\Gateways;

use App\Domain\Cep\CepGatewayInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class BrasilApiCepGateway implements CepGatewayInterface
{
    private Client $http;
    private string $baseUri;

    public function __construct(?Client $http = null)
    {
        $this->baseUri = rtrim(env('BRASILAPI_URL', 'https://brasilapi.com.br/api/cep/v1'), '/') . '/';
        $this->http    = $http ?? new Client();
    }

    public function lookup(string $cep): ?array
    {
        $cep = preg_replace('/\D/', '', $cep);

        if (strlen($cep) !== 8) {
            return null;
        }

        try {
            $response = $this->http->get($this->baseUri . $cep);
            $data     = json_decode($response->getBody()->getContents(), true);

            return [
                'cep'          => $data['cep'] ?? $cep,
                'street'       => $data['street'] ?? '',
                'neighborhood' => $data['neighborhood'] ?? '',
                'city'         => $data['city'] ?? '',
                'state'        => $data['state'] ?? '',
                'country'      => 'BR',
            ];
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                return null;
            }
            throw $e;
        }
    }
}
