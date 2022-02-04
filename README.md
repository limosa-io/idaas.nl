![](https://github.com/arietimmerman/idaas.nl/workflows/CI/badge.svg)

# idaas.nl

This is the core of [idaas.nl](https://www.idaas.nl/): (not) yet another identity platform.

Idaas.nl has similaries with Auth0, Okta, Oracle IDCS Azure AD B2C, Ping Identity, ForgeRock, OneLogin, Keykloak, Gluu and other products.

Idaas.nl is a true multi-tenant solution. It is design to scale out and provides great flexibility.
## Demo environment

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
