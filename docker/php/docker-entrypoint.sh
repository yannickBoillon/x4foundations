#!/bin/sh
set -e

if [ "$1" = 'php-fpm' ]; then
    if [ ! -f vendor/autoload_runtime.php ]; then
        composer install --prefer-dist --no-progress --no-interaction
    fi

    if ls migrations/*.php > /dev/null 2>&1; then
        php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
    fi
fi

exec docker-php-entrypoint "$@"
