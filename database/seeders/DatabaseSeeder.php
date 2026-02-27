<?php

namespace Database\Seeders;

use App\Domain\Address\Models\Address;
use App\Domain\Customer\Models\Customer;
use App\Domain\User\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::create([
            'name'     => 'Admin',
            'email'    => 'admin@crm.com',
            'password' => Hash::make('password'),
        ]);

        $customers = [
            ['name' => 'João Silva',    'email' => 'joao.silva@email.com',    'phone' => '11987654321', 'document' => '123.456.789-00'],
            ['name' => 'Maria Souza',   'email' => 'maria.souza@email.com',   'phone' => '21976543210', 'document' => '234.567.890-11'],
            ['name' => 'Carlos Mendes', 'email' => 'carlos.mendes@email.com', 'phone' => '31965432109', 'document' => '345.678.901-22'],
            ['name' => 'Ana Oliveira',  'email' => 'ana.oliveira@email.com',  'phone' => '41954321098', 'document' => '456.789.012-33'],
            ['name' => 'Pedro Costa',   'email' => 'pedro.costa@email.com',   'phone' => '51943210987', 'document' => '567.890.123-44'],
        ];

        $addresses = [
            [
                ['cep' => '01310100', 'street' => 'Avenida Paulista',       'number' => '1000', 'complement' => 'Apto 101', 'neighborhood' => 'Bela Vista',   'city' => 'São Paulo',       'state' => 'SP', 'country' => 'BR'],
                ['cep' => '01310200', 'street' => 'Rua da Consolação',      'number' => '200',  'complement' => null,       'neighborhood' => 'Consolação',   'city' => 'São Paulo',       'state' => 'SP', 'country' => 'BR'],
            ],
            [
                ['cep' => '20040020', 'street' => 'Avenida Rio Branco',     'number' => '45',   'complement' => 'Sala 3',   'neighborhood' => 'Centro',       'city' => 'Rio de Janeiro',  'state' => 'RJ', 'country' => 'BR'],
                ['cep' => '22410003', 'street' => 'Rua Visconde de Pirajá', 'number' => '330',  'complement' => null,       'neighborhood' => 'Ipanema',      'city' => 'Rio de Janeiro',  'state' => 'RJ', 'country' => 'BR'],
            ],
            [
                ['cep' => '30112000', 'street' => 'Avenida Afonso Pena',    'number' => '1500', 'complement' => 'Bloco B',  'neighborhood' => 'Centro',       'city' => 'Belo Horizonte',  'state' => 'MG', 'country' => 'BR'],
                ['cep' => '30310110', 'street' => 'Rua da Bahia',           'number' => '88',   'complement' => null,       'neighborhood' => 'Lourdes',      'city' => 'Belo Horizonte',  'state' => 'MG', 'country' => 'BR'],
            ],
            [
                ['cep' => '80010010', 'street' => 'Rua XV de Novembro',     'number' => '700',  'complement' => 'Cobertura','neighborhood' => 'Centro',       'city' => 'Curitiba',        'state' => 'PR', 'country' => 'BR'],
                ['cep' => '80250000', 'street' => 'Alameda Dr. Carlos de Carvalho', 'number' => '12', 'complement' => null, 'neighborhood' => 'Batel',       'city' => 'Curitiba',        'state' => 'PR', 'country' => 'BR'],
            ],
            [
                ['cep' => '90010150', 'street' => 'Rua dos Andradas',       'number' => '1200', 'complement' => 'Apto 5',   'neighborhood' => 'Centro Histórico', 'city' => 'Porto Alegre', 'state' => 'RS', 'country' => 'BR'],
                ['cep' => '90570020', 'street' => 'Avenida Independência',  'number' => '955',  'complement' => null,       'neighborhood' => 'Independência', 'city' => 'Porto Alegre',  'state' => 'RS', 'country' => 'BR'],
            ],
        ];

        foreach ($customers as $index => $customerData) {
            $customer = Customer::create(array_merge($customerData, ['user_id' => $user->id]));

            foreach ($addresses[$index] as $addressData) {
                Address::create(array_merge($addressData, ['customer_id' => $customer->id]));
            }
        }
    }
}
