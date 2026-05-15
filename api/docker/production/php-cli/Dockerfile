FROM php:8.4-cli-alpine AS builder

RUN apk add --no-cache git unzip

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN addgroup -g 1000 app && adduser -u 1000 -G app -s /bin/sh -D app

WORKDIR /app

COPY ./composer.json ./composer.lock ./

RUN composer install --no-dev --prefer-dist --no-progress --no-suggest --optimize-autoloader --ignore-platform-req=ext-intl \
     && rm -rf /root/.composer/cache \

COPY ./ ./

RUN composer dump-autoload --optimize --classmap-authoritative \
    && rm -rf /root/.composer/cache

### CLI ###

FROM php:8.1-cli-alpine

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN apk add --no-cache bash coreutils git unzip postgresql-client
RUN install-php-extensions intl pdo_pgsql opcache zip apcu

RUN mv $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini

COPY ./docker/common/php/conf.d /usr/local/etc/php/conf.d
COPY ./docker/production/php/conf.d /usr/local/etc/php/conf.d

COPY ./docker/common/wait-for-it.sh /usr/local/bin/wait-for-it
RUN chmod 555 /usr/local/bin/wait-for-it

RUN addgroup -g 1000 app && adduser -u 1000 -G app -s /bin/bash -D app

WORKDIR /app

COPY --from=builder --chown=app:app /app ./

USER app



