FROM composer:2.8.4 AS composer
FROM php:8.4-cli

LABEL maintainer="Rayan Levert <rayanlevert@msn.com>"

# Installing packages needed
RUN apt-get update -y && \
    apt-get install -y \
    git \
    zip

# Enabling xdebug and PDO MySQL
RUN pecl install xdebug && docker-php-ext-install pdo_mysql && docker-php-ext-enable xdebug pdo_mysql

# Creates directory /app
RUN mkdir /app

# Volumes
VOLUME ["/app"]

# Composer
COPY --from=composer /usr/bin/composer /usr/local/bin/composer

CMD ["php"]