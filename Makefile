
dev-up:
	docker-compose -f ./docker-compose.yml -f ./docker-compose.dev.yml up -d

dev-down:
	docker-compose -f ./docker-compose.yml -f ./docker-compose.dev.yml down -v
