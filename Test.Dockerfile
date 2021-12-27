FROM php:7.3.6-fpm-alpine

RUN \
    apk add --no-cache $PHPIZE_DEPS su-exec tidyhtml tidyhtml-dev git libpng-dev libjpeg-turbo-dev freetype-dev tar && \
    pecl install xdebug-2.7.2 && \
    docker-php-ext-configure pcntl --enable-pcntl && \
    docker-php-ext-configure gd \
    --with-gd \
    --with-freetype-dir=/usr/include/ \
    --with-png-dir=/usr/include/ \
    --with-jpeg-dir=/usr/include/ && \
    NPROC=$(grep -c ^processor /proc/cpuinfo 2>/dev/null || 1) && \
    docker-php-ext-install -j${NPROC} gd && \
    docker-php-ext-enable xdebug && \
    docker-php-ext-install pdo pdo_mysql tidy exif pcntl bcmath && \
    # cat /tmp/ca-dev.pem >> /etc/ssl/certs/ca-certificates.crt && \
    cd /usr/bin && \
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --quiet && \
    rm composer-setup.php
