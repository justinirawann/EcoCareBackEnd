FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git curl unzip libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath gd

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN COMPOSER_ALLOW_SUPERUSER=1 composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist

RUN php artisan migrate --force && php artisan db:seed --force

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 8000

CMD bash -c "php artisan key:generate --force \
 && php artisan config:clear \
 && php artisan cache:clear \
 && php artisan storage:link \
 && php artisan migrate --force \
 && php artisan db:seed --force \
 && php artisan serve --host=0.0.0.0 --port=8000"


