# Production image for CryptoZing — the same recipe published for self-hosters.
# Build from the repo root: docker build -f docker/production/Dockerfile .

# ---- PHP dependencies -------------------------------------------------------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts \
    --no-autoloader --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev

# ---- Frontend assets --------------------------------------------------------
FROM node:20-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

# ---- Runtime Node dependencies (address derivation) -------------------------
FROM node:20-alpine AS nodedeps
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --omit=dev

# ---- Runtime ----------------------------------------------------------------
FROM php:8.4-fpm-alpine

# nodejs: HdWallet shells out to node_scripts/derive-address.cjs
# fcgi: FPM ping healthcheck
RUN apk add --no-cache nodejs icu-libs fcgi \
    && apk add --no-cache --virtual .build $PHPIZE_DEPS icu-dev \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql bcmath intl opcache pcntl \
    && apk del .build

# Run as uid 1000 to match the deploy user that owns the storage bind mount.
RUN adduser -D -u 1000 app

WORKDIR /var/www/html

COPY --chown=app:app . .
COPY --from=vendor --chown=app:app /app/vendor vendor
COPY --from=assets --chown=app:app /app/public/build public/build
COPY --from=nodedeps --chown=app:app /app/node_modules node_modules

COPY docker/production/php.ini /usr/local/etc/php/conf.d/zz-prod.ini
COPY docker/production/php-fpm-pool.conf /usr/local/etc/php-fpm.d/zz-prod.conf
COPY --chmod=755 docker/production/entrypoint.sh /usr/local/bin/entrypoint.sh

# /shared/public pre-owned by app so the named volume inherits uid 1000 on
# first mount — without this, the entrypoint can't populate a fresh volume.
RUN mkdir -p storage bootstrap/cache /shared/public \
    && chown -R app:app storage bootstrap/cache /shared/public

USER app
EXPOSE 9000

HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
    CMD REQUEST_METHOD=GET SCRIPT_NAME=/fpm-ping SCRIPT_FILENAME=/fpm-ping \
        cgi-fcgi -bind -connect 127.0.0.1:9000 || exit 1

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
