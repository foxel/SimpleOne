FROM ubuntu:14.04
MAINTAINER Andrey F. Kupreychik <foxel@quickfox.ru>

ENV DEBIAN_FRONTEND=noninteractive

RUN \
  apt-get update && \
  apt-get -y --no-install-recommends install \
    wget curl ca-certificates dnsutils supervisor cron \
    nginx php5-cli php5-curl php5-fpm php5-gd php5-mcrypt php5-mysql && \
  update-locale LANG=C.UTF-8 && \
  rm -rf /var/lib/apt/lists/*

COPY core /var/www/core
COPY db /var/www/db
COPY lib /var/www/lib
COPY plugins /var/www/plugins
COPY static /var/www/static
COPY composer.json cron.php index.php ping.php setup.sh  /var/www/

ADD data/sone.qfc.php.sample /etc/simpleone/

RUN \
    mkdir /data && \
    cd /var/www && \
    ln -s /data ./data && \
    bash ./setup.sh && \
    rm setup.sh && \
    mkdir ./logs

# config
RUN \
    rm -f /etc/php5/fpm/pool.d/* /etc/nginx/sites-enabled/* && \
    ln -s /etc/nginx/sites-available/simpleone.conf /etc/nginx/sites-enabled/simpleone.conf

ADD docker/ /

EXPOSE 80

VOLUME /data

CMD ["/bin/start.sh"]
