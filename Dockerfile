FROM php:8.2-apache as php8
RUN docker-php-ext-install mysqli

RUN apt-get update && apt-get install -y libzip-dev \
    && docker-php-ext-install zip

RUN apt-get update && apt-get install -y \
    libwebp-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libxpm-dev \
    libfreetype6-dev

RUN docker-php-ext-configure gd \
    --with-jpeg \
    --with-webp \
    --with-xpm \
    --with-freetype

RUN docker-php-ext-install gd

WORKDIR /var/www/html
COPY php .
