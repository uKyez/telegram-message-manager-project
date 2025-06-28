FROM php:8.2-fpm-alpine

# Instalar dependências do sistema
RUN apk add --no-cache \
    nginx \
    postgresql-client \
    build-base \
    autoconf \
    libzip-dev \
    libpng-dev \
    jpeg-dev \
    libwebp-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    icu-dev \
    git \
    curl \
    supervisor

# Instalar extensões PHP
RUN docker-php-ext-install -j$(nproc) pdo_pgsql bcmath gd exif pcntl zip opcache

# Instalar Composer
COPY --from=composer/composer:latest-bin /composer /usr/bin/composer

# Configurar Nginx
COPY ./.docker/nginx/default.conf /etc/nginx/http.d/default.conf

# Configurar PHP-FPM
COPY ./.docker/php/php.ini /usr/local/etc/php/conf.d/php.ini

# Configurar Supervisor
COPY ./.docker/supervisor/supervisord.conf /etc/supervisord.conf

# Definir diretório de trabalho
WORKDIR /var/www/html

# Copiar arquivos da aplicação
COPY . .

# Instalar dependências do Composer
RUN composer install --no-dev --optimize-autoloader

# Otimizar Laravel
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

# Definir permissões
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expor porta
EXPOSE 8000

# Comando de inicialização
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]

