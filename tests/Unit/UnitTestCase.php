<?php

namespace Tests\Unit;

use Laravel\Lumen\Testing\TestCase as BaseTestCase;
use Mockery;

/**
 * Base para testes unitários.
 * Inicializa a aplicação (env, facades) mas NÃO roda migrations.
 */
abstract class UnitTestCase extends BaseTestCase
{
    public function createApplication(): \Laravel\Lumen\Application
    {
        return require __DIR__ . '/../../bootstrap/app.php';
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
