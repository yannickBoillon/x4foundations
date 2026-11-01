FROM php:8.4-fpm-alpine

COPY --from=mlocati/php-extension-installer:latest /usr/bin/install-php-extensions /usr/local/bin/
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# apcu et amqp sont compilés depuis leurs sources GitHub plutôt que via PECL
RUN apk add --no-cache git unzip \
    && install-php-extensions pdo_pgsql intl zip opcache \
        krakjoe/apcu@v5.1.28 \
        php-amqp/php-amqp@v2.2.0

COPY --chmod=644 conf.d/app.ini $PHP_INI_DIR/conf.d/zz-app.ini
COPY --chmod=755 docker-entrypoint.sh /usr/local/bin/docker-entrypoint

ARG SYMFONY_VERSION=7.4.*
ENV SYMFONY_VERSION=${SYMFONY_VERSION} \
    HOME=/tmp \
    COMPOSER_HOME=/tmp/composer

WORKDIR /app

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]
