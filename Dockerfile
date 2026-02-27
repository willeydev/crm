FROM php:8.2-cli-alpine

# Instala dependências do sistema e extensões PHP
RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    curl \
    && docker-php-ext-install pdo pdo_pgsql zip

# Instala Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copia dependências primeiro (cache de camadas)
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copia o restante do projeto
COPY . .

# Permissões de escrita para storage
RUN mkdir -p storage/logs && chmod -R 775 storage

EXPOSE 8000

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
