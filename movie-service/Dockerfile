FROM php:8.2-cli

RUN apt-get clean && rm -rf /var/lib/apt/lists/* && \
    apt-get update --fix-missing && apt-get install -y --no-install-recommends \
    git curl libpng-dev libonig-dev libxml2-dev libpq-dev zip unzip && \
    rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd

RUN pecl install redis && docker-php-ext-enable redis

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --optimize-autoloader --no-scripts

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 8002

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8002"]
