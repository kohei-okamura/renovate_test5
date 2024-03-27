#
# web-resources
#
FROM nginxinc/nginx-unprivileged:1.25.4 AS web-resources

USER root
RUN echo "************" && find /usr/share -name "zoneinfo"

# see https://github.com/jauderho/dockerfiles/tree/main/nginx-distroless
RUN mkdir -p /opt/var/cache/nginx && \
    cp -a --parents /usr/lib/nginx /opt && \
    cp -a --parents /usr/share/nginx /opt && \
    cp -a --parents /var/log/nginx /opt && \
    cp -aL --parents /var/run /opt && \
    cp -a --parents /etc/nginx /opt && \
    cp -a --parents /etc/passwd /opt && \
    cp -a --parents /etc/group /opt && \
    cp -a --parents /usr/sbin/nginx /opt && \
    cp -a --parents /usr/sbin/nginx-debug /opt && \
    cp -a --parents /lib/$(uname -m)-linux-gnu/ld-* /opt && \
    cp -a --parents /lib/$(uname -m)-linux-gnu/libpcre.so.* /opt && \
    cp -a --parents /lib/$(uname -m)-linux-gnu/libz.so.* /opt && \
    cp -a --parents /lib/$(uname -m)-linux-gnu/libc* /opt && \
    cp -a --parents /lib/$(uname -m)-linux-gnu/libdl* /opt && \
    cp -a --parents /lib/$(uname -m)-linux-gnu/libpthread* /opt && \
    cp -a --parents /lib/$(uname -m)-linux-gnu/libcrypt* /opt && \
    cp -a --parents /usr/lib/$(uname -m)-linux-gnu/libssl.so.* /opt && \
    cp -a --parents /usr/lib/$(uname -m)-linux-gnu/libcrypto.so.* /opt && \
    cp /usr/share/zoneinfo/Asia/Tokyo /opt/etc/localtime && \
    rm -rf /opt/etc/nginx/conf.d/default.conf

#
# web-modules
#
FROM nginx:1.25.4 AS web-modules

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        build-essential \
        libpcre3-dev \
        libssl-dev \
        wget \
        zlib1g-dev \
        ; \
    cd /usr/src; \
    wget http://nginx.org/download/nginx-1.20.1.tar.gz -O nginx.tar.gz; \
    wget https://github.com/simpl/ngx_devel_kit/archive/v0.3.1.tar.gz -O ngx_devel_kit.tar.gz; \
    wget https://github.com/openresty/set-misc-nginx-module/archive/v0.32.tar.gz -O ngx_set_misc.tar.gz; \
    rm -rf nginx set-misc-nginx-module; \
    mkdir -p nginx modules; \
    tar -zxC /usr/src/nginx -f nginx.tar.gz; \
    tar -xzC /usr/src/modules -f ngx_devel_kit.tar.gz; \
    tar -xzC /usr/src/modules -f ngx_set_misc.tar.gz; \
    cd /usr/src/nginx/nginx-1.20.1; \
    CONFIGURE_OPTIONS=$(nginx -V 2>&1 | sed -n -e 's/^.*arguments: //p'); \
    sh -c "./configure --with-compat $CONFIGURE_OPTIONS --add-dynamic-module=/usr/src/modules/*" \
    make; \
    make install; \
    mkdir /opt/nginx; \
    mv /etc/nginx/modules/ndk_http_module.so /opt/nginx; \
    mv /etc/nginx/modules/ngx_http_set_misc_module.so /opt/nginx; \
    apt-get clean; \
    rm -rf /var/lib/apt/lists/*

#
# web-base
#
FROM gcr.io/distroless/base-debian10 AS web-base

COPY --from=web-resources /opt /
COPY --from=web-modules /opt/nginx/*.so /etc/nginx/modules/
COPY web/assets/web-base/nginx/ /etc/nginx/

USER www-data
EXPOSE 55080 55443

CMD ["nginx"]


#
# web-dev
#
FROM web-base AS web-dev

COPY web/assets/web-dev/nginx/ /etc/nginx/


#
# web-dev
#
FROM web-base AS web-dev

COPY web/assets/web-dev/nginx/ /etc/nginx/


#
# web-sandbox
#
FROM web-base AS web-sandbox

COPY web/assets/web-sandbox/nginx/ /etc/nginx/
COPY .work/web/public/ /usr/share/nginx/html/


#
# web-staging
#
FROM web-base AS web-staging

COPY web/assets/web-staging/nginx/ /etc/nginx/
COPY .work/web/public/ /usr/share/nginx/html/


#
# web-prod
#
FROM web-base AS web-prod

COPY web/assets/web-prod/nginx/ /etc/nginx/
COPY .work/web/public/ /usr/share/nginx/html/
