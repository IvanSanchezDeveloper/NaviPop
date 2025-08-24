#!/bin/bash
set -e

composer install --no-interaction

echo "Creating and migrating database..."
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate --no-interaction

exec "$@"
