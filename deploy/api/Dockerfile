FROM romeoz/docker-phpfpm:7.2
MAINTAINER romeOz <serggalka@gmail.com>

ENV OS_LOCALE="en_US.UTF-8"
RUN apt-get update --fix-missing && apt-get install -y locales && locale-gen ${OS_LOCALE}
ENV LANG=${OS_LOCALE} \
    LANGUAGE=${OS_LOCALE} \
    LC_ALL=${OS_LOCALE} \
    NGINX_CONF_DIR=/etc/nginx

COPY ./deploy/api/supervisord.conf /etc/supervisor/conf.d/
COPY ./app /var/www/app/

RUN	\
    # install swoole
    apt-get install  -y --force-yes libpcre3-dev php-pear php7.2-dev && \
    pecl install swoole && \
    pecl install hprose && \
    echo "extension=swoole.so" >> /etc/php/7.2/fpm/php.ini && \
    echo "extension=hprose.so" >> /etc/php/7.2/fpm/php.ini && \
    echo "curl.cainfo=/var/www/app/keys/cacert.pem" >> /etc/php/7.2/fpm/php.ini


RUN echo 'extension=swoole.so' >> /etc/php/7.2/cli/php.ini
RUN echo "extension=hprose.so" >> /etc/php/7.2/cli/php.ini
RUN echo "curl.cainfo=/var/www/app/keys/cacert.pem" >> /etc/php/7.2/cli/php.ini

RUN phpenmod swoole
RUN phpenmod hprose

RUN apt-get install -y nginx \
    # && apt-get install -y php-bcmath \
    # php7.2-bcmath
    && rm -rf ${NGINX_CONF_DIR}/sites-enabled/* ${NGINX_CONF_DIR}/sites-available/* \
    # Install supervisor
    && apt-get install -y supervisor && mkdir -p /var/log/supervisor \
    && chown www-data:www-data /var/www/app/ -Rf \
    # Cleaning
    && apt-get purge -y --auto-remove $BUILD_DEPS \
    && apt-get autoremove -y && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    # Forward request and error logs to docker log collector
    && ln -sf /dev/stdout /var/log/nginx/access.log \
    && ln -sf /dev/stderr /var/log/nginx/error.log 
# && cd app && composer install

COPY ./deploy/api/lm_cert/* /var/www/lm_cert/*
COPY ./deploy/api/nginx.conf ${NGINX_CONF_DIR}/nginx.conf
COPY ./deploy/api/app.conf ${NGINX_CONF_DIR}/sites-enabled/app.conf
COPY ./deploy/api/www.conf /etc/php/7.2/fpm/pool.d/www.conf

WORKDIR /var/www/app/

EXPOSE 80 443

CMD ["/usr/bin/supervisord"]
