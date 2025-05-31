#!/bin/bash
set -e

# Install PHP dependencies
composer install

# Start Symfony server
exec symfony serve --allow-http --allow-all-ip --no-tls --port=8000
