FROM php:8.3-fpm

# Dependencias del sistema
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    curl \
    unzip \
    git \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Composer desde imagen oficial
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Alinear UID/GID de www-data con el usuario WSL (1000)
# para que los archivos creados por el contenedor sean editables desde el host
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

USER www-data
