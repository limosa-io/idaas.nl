![](https://github.com/arietimmerman/idaas.nl/workflows/CI/badge.svg)

# idaas.nl

This is the core of [idaas.nl](https://www.idaas.nl/): (not) yet another identity platform.

Idaas.nl has similaries with Auth0, Okta, Oracle IDCS Azure AD B2C, Ping Identity, ForgeRock, OneLogin, Keykloak, Gluu and other products.

Idaas.nl is a true multi-tenant solution. It is design to scale out and provides great flexibility.
## Demo environment

Run the commands listed below and access your tenant via http://login.notidaas.nl

_Note:_ `notidaas.nl` is a special domain. This domain and all subdomains point to localhost.

~~~
source .env.demo
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

## Kubernetes

An example Kubernetes configuration can be found in `./kubernetes/`.

In order to start the Kubernetes cluster in minikube, run the following.

~~~
# Start minikube and 
minikube start --mount-string="$(pwd):/var/www/html" --mount --extra-config=apiserver.service-node-port-range=80-30000
# Build the docker images
env $(cat .env.demo | xargs) docker-compose build laravel.test node.test
# Apply the configuration
minikube kubectl apply -- -f ./kubernetes/
# Expose all services
minikube service --all
# Open a Socks5 proxy
ssh -i ~/.minikube/machines/minikube/id_rsa -D 8080 docker@$(minikube ip)
~~~

Use whatever method to open a shell in a pod of the `idaas` service and run the following.

~~~
php artisan migrate
php artisan tenant:master login youremail@example.com
~~~

Now configure your browser to use `localhost:8080` as a SOCKS5 proxy and browse to `login.notidaas.nl`.
