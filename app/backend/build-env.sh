#!/bin/bash

set -e
composer install
exec symfony serve --allow-http --allow-all-ip --no-tls --port=8000
