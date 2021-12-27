#!/bin/sh
cd /app
composer install -n
php /app/artisan migrate --force
php artisan queue:work --tries=3 --timeout=30 & 