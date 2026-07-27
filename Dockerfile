FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    nginx supervisor curl git unzip nodejs npm \
    oniguruma-dev libpng-dev libjpeg-turbo-dev freetype-dev icu-dev libzip-dev
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath opcache intl zip gd

RUN apk add --no-cache autoconf g++ make \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del autoconf g++ make

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction
RUN npm ci && npm run build
RUN php artisan livewire:publish --assets || true
RUN php artisan storage:link || true && mkdir -p storage/app/livewire-tmp && chmod -R 775 storage/app/livewire-tmp

RUN chown -R www-data:www-data storage bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache

COPY docker/nginx.conf.template /etc/nginx/nginx.conf.template
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
