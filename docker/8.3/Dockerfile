FROM composer:2.6.5 AS composer
FROM php:8.3-fpm

LABEL maintainer="Rayan Levert <rayanlevert@msn.com>"

# Installing needed packages
RUN apt update && apt install git zip

# Enabling xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Creates /app directory
RUN mkdir /app

# Volumes
VOLUME ["/app"]

# Composer
COPY --from=composer /usr/bin/composer /usr/local/bin/composer

CMD ["php-fpm"]