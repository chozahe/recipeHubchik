# syntax=docker/dockerfile:1

FROM php:8.5-cli AS composer_stage

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock symfony.lock ./
COPY bin ./bin
COPY config ./config
COPY migrations ./migrations
COPY public ./public
COPY src ./src
COPY templates ./templates
COPY .env ./

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

FROM node:22-alpine AS node_stage

WORKDIR /app

COPY package.json package-lock.json webpack.config.js postcss.config.mjs ./
COPY assets ./assets
COPY public ./public
COPY --from=composer_stage /app/vendor ./vendor

RUN npm ci \
    && npm run build

FROM php:8.5-cli AS runtime

RUN apt-get update \
    && apt-get install -y --no-install-recommends bash libpq-dev libicu-dev postgresql-client \
    && docker-php-ext-install intl pdo_pgsql \
    && apt-mark manual libicu76 libpq5 \
    && apt-get purge -y --auto-remove libpq-dev libicu-dev \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

ENV APP_ENV=prod

COPY --from=composer_stage /app ./
COPY --from=node_stage /app/public/build ./public/build
COPY docker/app/entrypoint.sh /usr/local/bin/recipehub-entrypoint

RUN chmod +x /usr/local/bin/recipehub-entrypoint \
    && mkdir -p var/cache var/log /var/log/recipehub \
    && chown -R www-data:www-data var /var/log/recipehub

EXPOSE 8080

ENTRYPOINT ["recipehub-entrypoint"]
CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]
