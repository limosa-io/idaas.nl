
minikube:
	minikube start --mount-string="$(pwd):/var/www/html" --mount --extra-config=apiserver.service-node-port-range=80-30000
	env $(cat .env.demo | xargs) docker-compose build laravel.test node.test
	minikube kubectl apply -- -f ./kubernetes/

// Open a SOCKS5 proxy
ssh -i ~/.minikube/machines/minikube/id_rsa -D 8080 docker@$(minikube ip)