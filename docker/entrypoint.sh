#!/bin/sh
set -e

echo "Aguardando PostgreSQL..."
until php -r "
    try {
        new PDO(
            'pgsql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'),
            getenv('DB_USERNAME'),
            getenv('DB_PASSWORD')
        );
        exit(0);
    } catch (Exception \$e) {
        exit(1);
    }
" 2>/dev/null; do
    echo "  PostgreSQL não disponível, tentando novamente em 2s..."
    sleep 2
done

echo "PostgreSQL pronto!"

echo "Rodando migrations..."
php artisan migrate --force

echo "Iniciando servidor Lumen em 0.0.0.0:8000..."
php -S 0.0.0.0:8000 -t public
