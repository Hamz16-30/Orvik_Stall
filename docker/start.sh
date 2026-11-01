#!/bin/sh
set -eu

: "${PORT:=8080}"

# Railway assigns the listening port at runtime.
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/*:80>/*:${PORT}>/" /etc/apache2/sites-available/000-default.conf

mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    storage/app/private \
    storage/app/public \
    bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache

# The public disk is backed by the mounted storage directory in production.
php artisan storage:link --force

exec apache2-foreground
