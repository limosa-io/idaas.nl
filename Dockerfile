FROM webdevops/php-nginx-dev:7.4
COPY ./nginx/fastcgi.conf /opt/docker/etc/nginx/conf.d/

RUN apt update && \
    apt install libyaml-dev && \
    pecl install yaml-2.2.1  && \
    docker-php-ext-enable yaml
