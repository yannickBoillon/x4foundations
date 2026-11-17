#!/bin/sh
set -e

if [ "$1" = 'npm' ] && [ ! -d node_modules ]; then
    npm install --no-audit --no-fund
fi

exec "$@"
