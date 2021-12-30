FROM webdevops/php-nginx-dev:7.4
COPY ./nginx/fastcgi.conf /opt/docker/etc/nginx/conf.d/

RUN apt update && \
    apt install libyaml-0-2 libyaml-dev && \
    pecl install yaml-2.2.1  && \
    docker-php-ext-enable yaml
