init: docker-down-clear \
	api-clear frontend-clear \
	docker-pull docker-build docker-up \
	api-init frontend-init
up: docker-up
down: docker-down
restart: down up
check: lint analyze test
lint: api-lint frontend-lint-style
analyze: api-analyze frontend-analyze
test: api-test frontend-test
api-test: unit-test functional-test
format: api-cs-fix frontend-format


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

api-init: api-permissions api-composer-install api-migrations api-fixtures

api-composer-install:
	docker compose run --rm api-php-cli composer install

api-migrations:
	docker compose run --rm api-php-cli bin/console doctrine:migrations:migrate --no-interaction

api-fixtures:
	docker compose run --rm api-php-cli bin/console doctrine:fixtures:load --no-interaction

api-permissions:
	docker run --rm -v ${PWD}/api:/app -w /app alpine chmod 777 var

api-lint:
	docker compose run --rm api-php-cli composer lint
	docker compose run --rm api-php-cli composer php-cs-fixer check

api-validate-schema:
	docker compose run --rm api-php-cli bin/console doctrine:schema:validate

api-cs-fix:
	docker compose run --rm api-php-cli composer php-cs-fixer fix

api-analyze:
	docker compose run --rm api-php-cli composer psalm

unit-test:
	docker compose run --rm api-php-cli composer test -- --testsuite=Unit

functional-test:
	docker compose run --rm api-php-cli composer test -- --testsuite=Functional

frontend-clear:
	docker run --rm -v ${PWD}/frontend:/app -w /app alpine sh -c 'rm -rf .next'

frontend-init: frontend-yarn-install frontend-ready

frontend-yarn-install:
	docker compose run --rm frontend-node-cli yarn install

frontend-ready:
	docker run --rm -v ${PWD}/frontend:/app -w /app alpine touch .ready

frontend-lint-style:
	docker compose run --rm frontend-node-cli yarn prettier-check

frontend-format:
	docker compose run --rm frontend-node-cli yarn prettier-fix

frontend-test:
	docker compose run --rm frontend-node-cli yarn test --watchAll=false

frontend-analyze:
	docker compose run --rm frontend-node-cli yarn lint

build: build-gateway build-frontend build-api

build-gateway:
	docker --log-level=debug build --pull --file=gateway/docker/production/nginx/Dockerfile --tag=${REGISTRY}/rtn-tests-gateway:${IMAGE_TAG} gateway/docker

build-frontend:
	docker --log-level=debug build --pull --file=frontend/docker/production/nginx/Dockerfile --tag=${REGISTRY}/rtn-tests-frontend:${IMAGE_TAG} frontend

build-api:
	docker --log-level=debug build --pull --file=api/docker/production/nginx/Dockerfile --tag=${REGISTRY}/rtn-tests-api:${IMAGE_TAG} api
	docker --log-level=debug build --pull --file=api/docker/production/php-fpm/Dockerfile --tag=${REGISTRY}/rtn-tests-api-php-fpm:${IMAGE_TAG} api
	docker --log-level=debug build --pull --file=api/docker/production/php-cli/Dockerfile --tag=${REGISTRY}/rtn-tests-api-php-cli:${IMAGE_TAG} api

try-build:
	REGISTRY=localhost IMAGE_TAG=0 make build


push: push-gateway push-frontend push-api

push-gateway:
	docker push ${REGISTRY}/rtn-tests-gateway:${IMAGE_TAG}

push-frontend:
	docker push ${REGISTRY}/rtn-tests-frontend:${IMAGE_TAG}

push-api:
	docker push ${REGISTRY}/rtn-tests-api:${IMAGE_TAG}
	docker push ${REGISTRY}/rtn-tests-api-php-fpm:${IMAGE_TAG}
	docker push ${REGISTRY}/rtn-tests-api-php-cli:${IMAGE_TAG}


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

	ssh ${HOST} -p ${PORT} 'cd site_${BUILD_NUMBER} && docker compose run --rm api-php-cli composer app migrations:migrate --no-interaction'

	ssh ${HOST} -p ${PORT} 'rm -f site'
	ssh ${HOST} -p ${PORT} 'ln -sr site_${BUILD_NUMBER} site'
	ssh ${HOST} -p ${PORT} 'docker image prune -af'
	ssh ${HOST} -p ${PORT} 'cd /home/deploy && ls -dt site_* | tail -n +4 | xargs rm -rf'


rollback:
	ssh ${HOST} -p ${PORT} 'cd site_${BUILD_NUMBER} && docker-compose -f docker-compose-production.yml pull'
	ssh ${HOST} -p ${PORT} 'cd site_${BUILD_NUMBER} && docker-compose -f docker-compose-production.yml up --build --remove-orphans -d'
	ssh ${HOST} -p ${PORT} 'rm -f site'
	ssh ${HOST} -p ${PORT} 'ln -sr site_${BUILD_NUMBER} site'
