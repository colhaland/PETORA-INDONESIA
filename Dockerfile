FROM php:8.4-fpm-bookworm AS php-base

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        libfreetype6 \
        libicu-dev \
        libicu72 \
        libjpeg62-turbo-dev \
        libjpeg62-turbo \
        libonig-dev \
        libonig5 \
        libpng-dev \
        libpng16-16 \
        libpq-dev \
        libpq5 \
        libzip-dev \
        libzip4 \
        unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" bcmath gd intl mbstring opcache pcntl pdo_pgsql zip \
    && apt-get purge -y --auto-remove \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libpq-dev \
        libzip-dev \
        unzip \
    && rm -rf /var/lib/apt/lists/*

FROM php-base AS vendor

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

FROM node:22-bookworm-slim AS assets

WORKDIR /var/www/html
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

FROM php-base AS app

WORKDIR /var/www/html
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=vendor /var/www/html/vendor ./vendor
COPY --from=assets /var/www/html/public/build ./public/build
COPY . .
RUN composer dump-autoload --no-dev --classmap-authoritative --no-interaction --no-scripts \
    && APP_ENV=local php artisan package:discover --ansi \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

COPY docker/php/zz-production.ini /usr/local/etc/php/conf.d/zz-production.ini
COPY docker/php/healthcheck.sh /usr/local/bin/petora-healthcheck
RUN chmod 0555 /usr/local/bin/petora-healthcheck \
    && apt-get update \
    && apt-get install -y --no-install-recommends curl \
    && rm -rf /var/lib/apt/lists/*

USER www-data
EXPOSE 9000
HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 CMD ["petora-healthcheck"]
CMD ["php-fpm"]

FROM nginx:1.27-alpine AS web

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=app /var/www/html/public /var/www/html/public
EXPOSE 80
HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 CMD ["wget", "-q", "--spider", "http://127.0.0.1/up"]

FROM postgres:16 AS backup

RUN apt-get update \
    && apt-get install -y --no-install-recommends gnupg \
    && mkdir -p /backups \
    && chown postgres:postgres /backups \
    && rm -rf /var/lib/apt/lists/*

COPY scripts/backup-postgres.sh /usr/local/bin/backup-postgres.sh
RUN chmod 0555 /usr/local/bin/backup-postgres.sh
