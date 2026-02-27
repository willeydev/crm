<?php

namespace Tests\Unit\Infrastructure;

use App\Infrastructure\Gateways\BrasilApiCepGateway;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Tests\Unit\UnitTestCase;

class BrasilApiCepGatewayTest extends UnitTestCase
{
    private function makeGateway(array $responses): BrasilApiCepGateway
    {
        $mock    = new MockHandler($responses);
        $handler = HandlerStack::create($mock);
        $client  = new Client(['handler' => $handler]);

        return new BrasilApiCepGateway($client);
    }

    public function test_returns_address_data_for_valid_cep(): void
    {
        $payload = json_encode([
            'cep'          => '01310100',
            'street'       => 'Avenida Paulista',
            'neighborhood' => 'Bela Vista',
            'city'         => 'São Paulo',
            'state'        => 'SP',
        ]);

        $gateway = $this->makeGateway([new Response(200, [], $payload)]);
        $result  = $gateway->lookup('01310100');

        $this->assertNotNull($result);
        $this->assertEquals('SP', $result['state']);
        $this->assertEquals('São Paulo', $result['city']);
        $this->assertEquals('BR', $result['country']);
    }

    public function test_returns_null_for_invalid_cep_length(): void
    {
        $gateway = $this->makeGateway([]);
        $result  = $gateway->lookup('1234');

        $this->assertNull($result);
    }

    public function test_returns_null_when_cep_not_found(): void
    {
        $mock = new MockHandler([
            new ClientException(
                '404 Not Found',
                new Request('GET', '/00000000'),
                new Response(404, [], '{"message":"CEP 00000000 não encontrado"}')
            ),
        ]);

        $gateway = new BrasilApiCepGateway(new Client(['handler' => HandlerStack::create($mock)]));
        $result  = $gateway->lookup('00000000');

        $this->assertNull($result);
    }

    public function test_strips_non_numeric_chars_from_cep(): void
    {
        $payload = json_encode([
            'cep'          => '01310100',
            'street'       => 'Av. Paulista',
            'neighborhood' => 'Bela Vista',
            'city'         => 'São Paulo',
            'state'        => 'SP',
        ]);

        $gateway = $this->makeGateway([new Response(200, [], $payload)]);
        $result  = $gateway->lookup('01310-100');

        $this->assertNotNull($result);
        $this->assertEquals('SP', $result['state']);
    }
}
