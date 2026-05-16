init: docker-down-clear api-clear docker-pull docker-build docker-up api-init
up: docker-up
down: docker-down
restart: down up
check: lint analyze test
lint: api-lint
analyze: api-analyze
test: api-test
api-test: unit-test functional-test

docker-up:
	docker compose up -d

docker-down:
	docker compose down --remove-orphans

docker-down-clear:
	docker compose down -v --remove-orphans

docker-pull:
	docker compose pull

api-clear:
	docker run --rm -v ${PWD}/api:/app -w /app alpine sh -c 'rm -rf var/*'

docker-build:
	docker compose build

api-init: api-composer-install api-permissions

api-composer-install:
	docker compose run --rm api-php-cli composer install

api-permissions:
	docker run --rm -v ${PWD}/api:/app -w /app alpine chmod 777 var

api-lint:
	docker compose run --rm api-php-cli composer lint
	docker compose run --rm api-php-cli composer php-cs-fixer check

api-cs-fix:
	docker compose run --rm api-php-cli composer php-cs-fixer fix

api-analyze:
	docker compose run --rm api-php-cli composer psalm

unit-test:
	docker compose run --rm api-php-cli composer test -- --testsuite=Unit

functional-test:
	docker compose run --rm api-php-cli composer test -- --testsuite=Functional

build: build-gateway build-frontend build-api

build-gateway:
	docker --log-level=debug build --pull --file=gateway/docker/production/nginx/Dockerfile --tag=${REGISTRY}/symfony-gateway:${IMAGE_TAG} gateway/docker

build-frontend:
	docker --log-level=debug build --pull --file=frontend/docker/production/nginx/Dockerfile --tag=${REGISTRY}/symfony-frontend:${IMAGE_TAG} frontend

build-api:
	docker --log-level=debug build --pull --file=api/docker/production/nginx/Dockerfile --tag=${REGISTRY}/symfony-api:${IMAGE_TAG} api
	docker --log-level=debug build --pull --file=api/docker/production/php-fpm/Dockerfile --tag=${REGISTRY}/symfony-api-php-fpm:${IMAGE_TAG} api
	docker --log-level=debug build --pull --file=api/docker/production/php-cli/Dockerfile --tag=${REGISTRY}/symfony-api-php-cli:${IMAGE_TAG} api

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
	docker push ${REGISTRY}/symfony-api-php-cli:${IMAGE_TAG}


ifneq ("$(wildcard .env.production)","")
    include .env.production
    export
endif

deploy:
	ssh ${HOST} -p ${PORT} 'rm -rf site_${BUILD_NUMBER}'
	ssh ${HOST} -p ${PORT} 'mkdir site_${BUILD_NUMBER}'

	scp -P ${PORT} docker-compose-production.yml ${HOST}:site_${BUILD_NUMBER}/docker-compose.yml

	ssh ${HOST} -p ${PORT} 'echo "${REGISTRY_PASSWORD}" | docker login ${REGISTRY} -u "${REGISTRY_USER}" --password-stdin'

	envsubst < .env.template > .env.local
	scp -P ${PORT} .env.local ${HOST}:site_${BUILD_NUMBER}/.env
	rm .env.local

	ssh ${HOST} -p ${PORT} 'cd site_${BUILD_NUMBER} && docker compose pull'
	ssh ${HOST} -p ${PORT} 'cd site_${BUILD_NUMBER} && docker compose up --build --remove-orphans -d'

	ssh ${HOST} -p ${PORT} 'cd site_${BUILD_NUMBER} && docker compose run --rm api-php-cli wait-for-it mysql:3306 -t 60'
	ssh ${HOST} -p ${PORT} 'cd site_${BUILD_NUMBER} && docker compose run --rm api-php-cli composer app migrations:migrate -- --no-interaction'

	ssh ${HOST} -p ${PORT} 'rm -f site'
	ssh ${HOST} -p ${PORT} 'ln -sr site_${BUILD_NUMBER} site'
	ssh ${HOST} -p ${PORT} 'docker image prune -af'
	ssh ${HOST} -p ${PORT} 'cd /home/deploy && ls -dt site_* | tail -n +4 | xargs rm -rf'


rollback:
	ssh ${HOST} -p ${PORT} 'cd site_${BUILD_NUMBER} && docker-compose -f docker-compose-production.yml pull'
	ssh ${HOST} -p ${PORT} 'cd site_${BUILD_NUMBER} && docker-compose -f docker-compose-production.yml up --build --remove-orphans -d'
	ssh ${HOST} -p ${PORT} 'rm -f site'
	ssh ${HOST} -p ${PORT} 'ln -sr site_${BUILD_NUMBER} site'
