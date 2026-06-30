# Production image for Render — PHP 8.3 (Filament 3.3 / openspout require 8.3+).
FROM php:8.3-cli-alpine

# PHP extensions (install-php-extensions also pulls in the needed system libs).
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin/
RUN install-php-extensions pdo_pgsql intl gd zip bcmath mbstring opcache pcntl

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Laravel runtime config
ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    PHP_CLI_SERVER_WORKERS=4

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Render provides $PORT; the entrypoint runs migrations and serves the app on it.
CMD ["/usr/local/bin/entrypoint.sh"]
