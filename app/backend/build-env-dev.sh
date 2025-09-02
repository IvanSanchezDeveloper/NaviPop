#!/bin/bash
set -e
echo "Loading fixtures..."
php bin/console doctrine:fixtures:load --no-interaction
