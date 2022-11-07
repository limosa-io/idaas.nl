FROM php:8.1-fpm-alpine

RUN \
    apk add --no-cache $PHPIZE_DEPS su-exec postgresql-dev tidyhtml tidyhtml-dev git libpng-dev libjpeg-turbo-dev freetype-dev tar libzip-dev zip && \
    pecl install xdebug && \
    docker-php-ext-configure pcntl --enable-pcntl && \
    NPROC=$(grep -c ^processor /proc/cpuinfo 2>/dev/null || 1) && \
    docker-php-ext-install -j${NPROC} gd && \
    docker-php-ext-enable xdebug
RUN docker-php-ext-install gd pdo pdo_pgsql tidy exif pcntl bcmath zip
