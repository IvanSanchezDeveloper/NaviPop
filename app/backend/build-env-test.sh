#!/bin/bash
set -e
echo "Creating and migrating test database..."
php bin/console doctrine:database:create --if-not-exists --env=test
php bin/console doctrine:migrations:migrate --no-interaction --env=test