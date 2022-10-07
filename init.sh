#!/bin/sh

eval $(minikube docker-env)
minikube start --mount-string="$(pwd):/var/www/html" --mount
env $(cat .env.demo | xargs) docker-compose build laravel.test
env $(cat .env.demo | xargs) docker-compose build node.test
minikube kubectl apply -- -f ./kubernetes/
