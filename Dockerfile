FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql zip

RUN echo "upload_max_filesize=50M" >> /usr/local/etc/php/conf.d/uploads.ini
RUN echo "post_max_size=60M" >> /usr/local/etc/php/conf.d/uploads.ini
RUN echo "memory_limit=512M" >> /usr/local/etc/php/conf.d/uploads.ini

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer


WORKDIR /var/www
