FROM node:22-bookworm-slim AS frontend

WORKDIR /app
COPY package.json package-lock.json vite.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm ci && npm run build

FROM composer:2 AS php-dependencies

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist --no-scripts

FROM dunglas/frankenphp:1-php8.4-bookworm AS application

RUN install-php-extensions pdo_pgsql intl zip opcache pcntl

WORKDIR /app
COPY . .
COPY --from=php-dependencies /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build
COPY docker/Caddyfile /etc/frankenphp/Caddyfile

RUN php artisan package:discover --ansi \
    && php artisan filament:upgrade \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

ENV APP_ENV=production
ENV APP_DEBUG=false
ENV SERVER_NAME=:80

EXPOSE 80 443 443/udp

CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]
