# syntax=docker/dockerfile:1

############################################
# Étape 1 — Dépendances PHP (vendor)
############################################
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock symfony.lock ./
# Installe sans scripts (pas de kernel encore présent) ni dev
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --no-progress

############################################
# Étape 2 — Build des assets front (Encore)
############################################
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY webpack.config.js .env ./
COPY assets ./assets
RUN npm run build

############################################
# Étape 3 — Image Apache + mod_php finale
############################################
FROM php:8.4-apache AS app

# Extensions PHP requises
RUN apt-get update && apt-get install -y --no-install-recommends \
        libicu-dev libzip-dev libonig-dev \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql intl opcache zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Apache : mod_rewrite + headers, DocumentRoot sur public/
RUN a2enmod rewrite headers
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Config PHP de production
RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.preload=/var/www/html/config/preload.php'; \
        echo 'opcache.preload_user=www-data'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'realpath_cache_size=4096K'; \
        echo 'realpath_cache_ttl=600'; \
        echo 'memory_limit=256M'; \
    } > /usr/local/etc/php/conf.d/zz-app.ini

WORKDIR /var/www/html

# Code applicatif + dépendances + assets compilés
COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

# Entrypoint : attend la BDD, génère les clés JWT, migre, préchauffe le cache
COPY docker/php-entrypoint.sh /usr/local/bin/php-entrypoint
RUN chmod +x /usr/local/bin/php-entrypoint \
    && mkdir -p var config/jwt \
    && chown -R www-data:www-data var public/build config/jwt

ENV APP_ENV=prod
ENTRYPOINT ["php-entrypoint"]
CMD ["apache2-foreground"]
