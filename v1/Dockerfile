FROM php:8.0.6-apache

RUN apt-get update

# TOOLS
RUN apt-get install -y nano \
        curl \
        git

# DEPENDENCIES
RUN apt-get install zip -y \
        libzip-dev \
        zip

RUN docker-php-ext-configure zip
RUN docker-php-ext-install zip
RUN docker-php-ext-install pdo pdo_mysql
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
RUN apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev
RUN docker-php-ext-configure gd --with-jpeg=/usr/include/ --with-freetype=/usr/include/
RUN docker-php-ext-install gd
RUN apt-get install -y \
        libcurl4-openssl-dev \
        pkg-config \
        libssl-dev
RUN pecl install mongodb && \	
        docker-php-ext-enable mongodb
RUN pecl install redis && \
        docker-php-ext-enable redis
RUN apt-get install -y libxml2-dev
RUN docker-php-ext-install soap
RUN apt-get install -y libpq-dev
RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql
RUN docker-php-ext-install pdo_pgsql pgsql

RUN apt-get update && \
        apt-get install -y libmagickwand-dev

ARG IMAGICK_LAST_COMMIT='448c1cd0d58ba2838b9b6dff71c9b7e70a401b90'

RUN mkdir -p /usr/src/php/ext/imagick && \
    curl -fsSL https://github.com/Imagick/imagick/archive/${IMAGICK_LAST_COMMIT}.tar.gz | tar xvz -C /usr/src/php/ext/imagick --strip 1 && \
    docker-php-ext-install imagick

# RUN pecl install imagick
# RUN docker-php-ext-enable imagick

RUN a2enmod rewrite

# CREATE PROJECT
WORKDIR /var/www/html/v1
COPY . .
RUN rm -rf ./system/storage/vendor/*
RUN rm -rf ./composer.lock
RUN curl -s https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer
RUN composer update

# CHANGE OWNERSHIP
RUN chown -R www-data repository

EXPOSE 80