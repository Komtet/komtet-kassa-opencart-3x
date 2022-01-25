FROM php:5.6.38-apache as php5
RUN docker-php-ext-install mysqli

WORKDIR /var/www/html
COPY php .

FROM php:7.3-apache as php7
RUN docker-php-ext-install mysqli

RUN apt-get update && apt-get install -y libzip-dev \
    && docker-php-ext-install zip
RUN apt-get update && apt-get install -y libpng-dev
RUN apt-get install -y \
    libwebp-dev \
    libjpeg62-turbo-dev \
    libpng-dev libxpm-dev \
    libfreetype6-dev
RUN docker-php-ext-configure gd \
    --with-gd \
    --with-webp-dir \
    --with-jpeg-dir \
    --with-png-dir \
    --with-zlib-dir \
    --with-xpm-dir \
    --with-freetype-dir
RUN docker-php-ext-install gd

WORKDIR /var/www/html
COPY php .
