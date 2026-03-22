FROM php:8.5-fpm

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libonig-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install \
        gd \
        pdo \
        pdo_mysql \
        mbstring \
        zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Prépare le dossier uploads avec les bonnes permissions
RUN mkdir -p /var/www/html/public/assets/uploads/products \
    && chown -R www-data:www-data /var/www/html/public/assets \
    && chmod -R 775 /var/www/html/public/assets
