FROM php:8.2-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        autoconf \
        dpkg-dev \
        file \
        g++ \
        gcc \
        git \
        libc-dev \
        libonig-dev \
        libxml2-dev \
        libzip-dev \
        make \
        pkg-config \
        re2c \
        unzip \
    && pecl install mongodb \
    && pecl install pcov \
    && docker-php-ext-enable mongodb \
    && docker-php-ext-enable pcov \
    && echo "pcov.enabled=1" > /usr/local/etc/php/conf.d/pcov.ini \
    && echo "pcov.directory=/var/www/html/app" >> /usr/local/etc/php/conf.d/pcov.ini \
    && docker-php-ext-install pdo pdo_mysql mbstring zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-progress --prefer-dist --no-scripts

COPY . .

RUN php artisan package:discover --ansi

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
