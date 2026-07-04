FROM php:8.3-fpm

# System dependencies required to build common PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions Symfony/Doctrine typically need
RUN docker-php-ext-install pdo pdo_mysql intl zip opcache

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www