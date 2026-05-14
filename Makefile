init: docker-down-clear docker-pull docker-build docker-up
up: docker-up
down: docker-down
restart: down up

docker-up:
	docker compose up -d

docker-down:
	docker compose down --remove-orphans

docker-down-clear:
	docker-compose down -v --remove-orphans


docker-pull:
	docker-compose pull

docker-build:
	docker-compose build

build: build-gateway build-frontend build-api

build-gateway:
	docker --log-level=debug build --pull --file=gateway/docker/production/nginx/Dockerfile --tag=${REGISTRY}/symfony-gateway:${IMAGE_TAG} gateway/docker

build-frontend:
	docker --log-level=debug build --pull --file=frontend/docker/production/nginx/Dockerfile --tag=${REGISTRY}/symfony-frontend:${IMAGE_TAG} frontend

build-api:
	docker --log-level=debug build --pull --file=api/docker/production/php-fpm/Dockerfile --tag=${REGISTRY}/symfony-api-php-fpm:${IMAGE_TAG} api
	docker --log-level=debug build --pull --file=api/docker/production/nginx/Dockerfile --tag=${REGISTRY}/symfony-api:${IMAGE_TAG} api

try-build:
	REGISTRY=localhost IMAGE_TAG=0 make build


push: push-gateway push-frontend push-api

push-gateway:
	docker push ${REGISTRY}/symfony-gateway:${IMAGE_TAG}

push-frontend:
	docker push ${REGISTRY}/symfony-frontend:${IMAGE_TAG}

push-api:
	docker push ${REGISTRY}/symfony-api:${IMAGE_TAG}
	docker push ${REGISTRY}/symfony-api-php-fpm:${IMAGE_TAG}
