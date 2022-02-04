![](https://github.com/arietimmerman/idaas.nl/workflows/CI/badge.svg)

# Idaas.nl

This is the core of idaas.nl
## Installation

~~~
composer install
~~~

## Development

~~~
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v $(pwd):/var/www/html \
    -w /var/www/html \
    laravelsail/php81-composer:latest \
    composer install --ignore-platform-reqs
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan tenant:master login youremail@example.com
~~~
