#!/bin/sh -e

conf=/usr/local/etc/php-fpm.d/www.conf
tmpl="${conf}.tmpl"

rm -rf /usr/local/etc/php-fpm.d/*.conf
envsubst <"${tmpl}" >"${conf}"

exec docker-php-entrypoint "$@"
