#!/bin/bash
set -e
composer install
echo "Creating and migrating database..."
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate --no-interaction
exec symfony serve --allow-http --allow-all-ip --no-tls --port=8000