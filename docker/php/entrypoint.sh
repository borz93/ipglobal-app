#!/bin/bash

echo "Init entrypoint.sh"

# Install Composer dependencies
composer install --no-interaction --optimize-autoloader

# Run database migrations
php bin/console doctrine:migrations:migrate --no-interaction

# Clear cache
php bin/console cache:clear

# Start PHP-FPM
exec php-fpm