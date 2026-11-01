#!/bin/sh
set -e

if [ "$1" = 'php-fpm' ]; then
    # Premier démarrage : le projet est entièrement construit dans /tmp
    # (système de fichiers du container), puis copié d'un coup dans /app.
    if [ ! -f composer.json ]; then
        BUILD_DIR=/tmp/symfony
        rm -rf "$BUILD_DIR"

        echo ">>> Création du projet Symfony ${SYMFONY_VERSION}..."
        composer create-project "symfony/skeleton:${SYMFONY_VERSION}" "$BUILD_DIR" \
            --prefer-dist --no-progress --no-interaction

        # Fichiers de config/code fournis avec la stack (messenger, elasticsearch, /health)
        [ -d config ] && cp -R config/. "$BUILD_DIR/config/"
        [ -d src ] && cp -R src/. "$BUILD_DIR/src/"

        cd "$BUILD_DIR"
        # La stack Docker est déjà gérée par compose.yaml
        composer config --json extra.symfony.docker false
        composer config --no-plugins allow-plugins.php-http/discovery true

        echo ">>> Installation des dépendances (Doctrine, Messenger/AMQP, Elasticsearch)..."
        composer require --no-interaction --no-progress \
            symfony/orm-pack \
            symfony/messenger \
            symfony/amqp-messenger \
            symfony/doctrine-messenger \
            symfony/serializer-pack \
            symfony/validator \
            symfony/http-client \
            nyholm/psr7 \
            "elasticsearch/elasticsearch:^8.18"

        composer require --dev --no-interaction --no-progress \
            symfony/maker-bundle

        echo ">>> Copie du projet dans /app..."
        # Le cache contient des chemins absolus vers /tmp/symfony : on le régénère
        rm -rf "$BUILD_DIR/var/cache" "$BUILD_DIR/var/log"
        cd /app
        cp -Rp "$BUILD_DIR/." /app/
        rm -rf "$BUILD_DIR"
        echo ">>> Projet Symfony créé."
    fi

    if [ ! -f vendor/autoload_runtime.php ]; then
        composer install --prefer-dist --no-progress --no-interaction
    fi

    if [ -d migrations ] && ls migrations/*.php > /dev/null 2>&1; then
        php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
    fi
fi

exec docker-php-entrypoint "$@"
