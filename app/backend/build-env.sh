#!/bin/bash
set -e
composer install
echo "Creating and migrating database..."
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate --no-interaction
php -S 0.0.0.0:${PORT:-8000} -t public

