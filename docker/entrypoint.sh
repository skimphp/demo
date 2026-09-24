#!/bin/bash
set -e

cd /app

if [ ! -f "vendor/autoload.php" ]; then
    echo "Installing dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

exec "$@"
