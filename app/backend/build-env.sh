#!/bin/bash
set -e
composer install
echo "Creating and migrating dev database..."
php bin/console doctrine:database:create --if-not-exists --env=dev
php bin/console doctrine:migrations:migrate --no-interaction --env=dev
echo "Creating and migrating test database..."x
php bin/console doctrine:database:create --if-not-exists --env=test
php bin/console doctrine:migrations:migrate --no-interaction --env=test
exec symfony serve --allow-http --allow-all-ip --no-tls --port=8000
