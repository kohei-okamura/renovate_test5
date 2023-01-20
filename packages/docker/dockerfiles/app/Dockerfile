#
# app-base
#
FROM php:8.1.6-fpm-buster AS app-base

RUN set -eux; \
    apt-get update; \
    ### Install Runtime Dependencies
    apt-get install -y --no-install-recommends \
        libfreetype6 \
        libjpeg62-turbo \
        libpng16-16 \
        libzip4 \
        locales \
        zlib1g \
        wkhtmltopdf \
        ; \
    ### Install Build Dependencies
    savedAptMark="$(apt-mark showmanual)"; \
    apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        libjpeg-dev \
        libpng-dev \
        libzip-dev \
        zlib1g-dev \
        ; \
    ### Install PHP Extensions
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-install -j$(nproc) \
        gd \
        opcache \
        pcntl \
        pdo_mysql \
        zip \
        ; \
    yes "" | pecl install apcu; docker-php-ext-enable apcu; \
    yes "" | pecl install redis; docker-php-ext-enable redis; \
    ### Setup Locales
    locale-gen ja_JP.UTF-8; \
    localedef -f UTF-8 -i ja_JP ja_JP; \
    ### Cleanup
    apt-mark auto '.*' > /dev/null; \
    [ -z "$savedAptMark" ] || apt-mark manual $savedAptMark; \
    apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false; \
    apt-get clean; \
    rm -rf /var/lib/apt/lists/*

COPY app/assets/app-base/ /usr/

ENV LANG ja_JP.UTF-8
ENV LANGUAGE ja_JP:jp
ENV LC_ALL ja_JP.UTF-8

WORKDIR /app


#
# app-cli-base
#
FROM app-base AS app-cli-base

COPY app/assets/app-cli-base/ /usr/local/

WORKDIR /app

ENTRYPOINT ["docker-php-entrypoint"]

CMD ["php"]


#
# app-cli-dev
#
FROM app-cli-base AS app-cli-dev

COPY app/assets/app-cli-dev/ /usr/local/

RUN set -eux; \
    yes "" | pecl install xdebug; docker-php-ext-enable xdebug


#
# app-cli-build
#
FROM app-cli-base AS app-cli-build

COPY --from=composer:latest /usr/bin/composer /usr/bin/

COPY .work/app/composer.* /app/
RUN composer install --no-dev --no-autoloader --no-progress --no-scripts

COPY .work/app/artisan /app/
COPY .work/app/server /app/server
RUN composer dump-autoload --no-dev --optimize


#
# app-cli-sandbox
#
FROM app-cli-base AS app-cli-sandbox

RUN mv /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini

COPY --from=app-cli-build --chown=www-data:www-data /app /app/
COPY --from=app-cli-build --chown=www-data:www-data /app /app/


#
# app-cli-staging
#
FROM app-cli-base AS app-cli-staging

RUN mv /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini

COPY --from=app-cli-build --chown=www-data:www-data /app /app/


#
# app-cli-prod
#
FROM app-cli-base AS app-cli-prod

RUN mv /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini

COPY --from=app-cli-build --chown=www-data:www-data /app /app/


#
# app-server-base
#
FROM app-base AS app-server-base

ENV FCGI_CONNECT=/var/run/php-fpm/php-fpm.sock
ENV PHP_FPM_LISTEN=/var/run/php-fpm/php-fpm.sock
ENV PHP_FPM_LISTEN_OWNER=www-data
ENV PHP_FPM_LISTEN_GROUP=www-data
ENV PHP_FPM_USER=www-data
ENV PHP_FPM_GROUP=www-data
ENV PHP_FPM_PM=dynamic
ENV PHP_FPM_PM_MAX_CHILDREN=5
ENV PHP_FPM_PM_START_SERVERS=2
ENV PHP_FPM_PM_MIN_SPARE_SERVERS=1
ENV PHP_FPM_PM_MAX_SPARE_SERVERS=3
ENV PHP_FPM_PM_PROCESS_IDLE_TIMEOUT=10s
ENV PHP_FPM_PM_MAX_REQUESTS=0
ENV PHP_FPM_EVENTS_MECHANISM=epoll

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        gettext-base \
        libfcgi-bin \
        ; \
    apt-get clean; \
    rm -rf /var/lib/apt/lists/*

COPY app/assets/app-server-base/ /usr/local/

HEALTHCHECK --start-period=10s CMD php-fpm-healthcheck.sh

VOLUME /var/run/php-fpm

ENTRYPOINT ["entrypoint.sh"]

CMD ["php-fpm"]


#
# app-server-dev
#
FROM app-server-base AS app-server-dev

COPY app/assets/app-server-dev/ /usr/local/

RUN set -eux; \
    yes "" | pecl install xdebug; docker-php-ext-enable xdebug


#
# app-server-build
#
FROM app-server-base AS app-server-build

COPY --from=composer:latest /usr/bin/composer /usr/bin/

COPY .work/app/composer.* /app/
RUN composer install --no-dev --no-autoloader --no-progress --no-scripts

COPY .work/app/artisan /app/
COPY .work/app/server /app/server
RUN composer dump-autoload --no-dev --optimize


#
# app-server-sandbox
#
FROM app-server-base AS app-server-sandbox

RUN mv /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini

COPY --from=app-server-build --chown=www-data:www-data /app /app/


#
# app-server-staging
#
FROM app-server-base AS app-server-staging

RUN mv /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini

COPY --from=app-server-build --chown=www-data:www-data /app /app/


#
# app-server-prod
#
FROM app-server-base AS app-server-prod

RUN mv /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini

COPY --from=app-server-build --chown=www-data:www-data /app /app/
