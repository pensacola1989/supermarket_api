# PHP 7 with Supervisor for running Workers
FROM php:cli

MAINTAINER Carlos Buenosvinos <carlos.buenosvinos@gmail.com>

RUN docker-php-ext-install -j$(nproc) bcmath
RUN docker-php-ext-install pdo pdo_mysql

RUN apt-get update && apt-get install -y supervisor && apt-get clean
RUN mkdir -p /var/log/supervisor /usr/src/app

COPY ./deploy/queue/supervisord.conf /etc/supervisor/supervisord.conf

VOLUME ["/etc/supervisor/conf.d", "/usr/src/app"]

EXPOSE 9001

CMD ["/usr/bin/supervisord"]