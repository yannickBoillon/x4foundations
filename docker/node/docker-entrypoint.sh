#!/bin/sh
set -e

if [ "$1" = 'npm' ]; then
    # Premier démarrage : le projet React (Vite + TypeScript) est généré dans /tmp,
    # puis copié d'un coup dans /app (bind mount ./frontend).
    if [ ! -f package.json ]; then
        BUILD_DIR=/tmp/react
        rm -rf "$BUILD_DIR"

        echo ">>> Création du projet React (Vite, template react-ts)..."
        # create-vite interprète le chemin relativement au répertoire courant
        (cd /tmp && npm create vite@latest react -- --template react-ts --no-interactive < /dev/null)

        echo ">>> Copie du projet dans /app..."
        cp -Rp "$BUILD_DIR/." /app/
        rm -rf "$BUILD_DIR"
        echo ">>> Projet React créé."
    fi

    if [ ! -d node_modules ]; then
        npm install --no-audit --no-fund
    fi
fi

exec "$@"
