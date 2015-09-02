FROM ubuntu:14.04
MAINTAINER Andrey F. Kupreychik <foxel@quickfox.ru>

ENV DEBIAN_FRONTEND=noninteractive

RUN \
  apt-get update && \
  apt-get -y --no-install-recommends install \
    wget dnsutils supervisor \
    nginx php5-cli php5-curl php5-fpm php5-gd php5-mcrypt php5-mysql && \
  update-locale LANG=C.UTF-8 && \
  rm -rf /var/lib/apt/lists/*

RUN \
    mkdir /var/www && \
    wget --no-check-certificate -O- https://github.com/foxel/SimpleOne/archive/dev.tar.gz | \
        tar --strip-components=1 -xzC /var/www/ && \
    cd /var/www && \
    bash ./setup.sh && \
    chown -R www-data:www-data data logs

# config
RUN \
    rm -f /etc/php5/fpm/pool.d/* /etc/nginx/sites-enabled/* && \
    ln -s /etc/nginx/sites-available/simpleone.conf /etc/nginx/sites-enabled/simpleone.conf

ADD etc /etc
ADD sone.qfc.php /var/www/data/

EXPOSE 80
VOLUME /var/www/data

CMD ["supervisord", "-nc", "/etc/supervisor/supervisord.conf"]
