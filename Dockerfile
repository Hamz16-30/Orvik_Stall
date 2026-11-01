# syntax=docker/dockerfile:1

FROM composer:2 AS vendor
WORKDIR /app
COPY . .
RUN composer config --global platform.php 8.3.0 \\\n    && composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader

FROM node:20-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

FROM php:8.3-apache AS app
RUN docker-php-ext-install pdo_mysql opcache \
    && a2enmod rewrite headers

WORKDIR /var/www/html
COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build
COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/start.sh /usr/local/bin/orvik-start
RUN chmod +x /usr/local/bin/orvik-start \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs storage/app/private storage/app/public bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
EXPOSE 8080
CMD ["/usr/local/bin/orvik-start"]
