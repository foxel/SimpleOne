FROM ubuntu:24.04

LABEL maintainer="Andrey F. Kupreychik <foxel@quickfox.ru>"

ENV DEBIAN_FRONTEND='noninteractive' \
    LANG='C.UTF-8'

RUN \
  apt-get update && \
  apt-get -y --no-install-recommends install \
    wget curl ca-certificates supervisor cron gpg && \
  (wget -qO- 'https://keyserver.ubuntu.com/pks/lookup?op=get&search=0x71daeaab4ad4cab6' | gpg --dearmor -o /usr/share/keyrings/ondrej-php.gpg) && \
  echo "deb [signed-by=/usr/share/keyrings/ondrej-php.gpg] https://ppa.launchpadcontent.net/ondrej/php/ubuntu noble main" > /etc/apt/sources.list.d/php.list && \
  apt-get update && \
  apt-get -y --no-install-recommends install \
    nginx php7.4-cli php7.4-curl php7.4-fpm php7.4-gd \
    php7.4-mysql php7.4-xml php7.4-mbstring \
    php7.4-readline php7.4-mcrypt make default-jre-headless unzip && \
  rm -f /etc/php/7.4/fpm/pool.d/* /etc/nginx/sites-enabled/* && \
  rm -rf /etc/logrotate.d/* && \
  rm -rf /var/lib/apt/lists/*

COPY composer.json setup.sh /var/www/
RUN \
    cd /var/www && \
    bash ./setup.sh && \
    rm setup.sh composer.json

COPY core /var/www/core
COPY db /var/www/db
COPY lib /var/www/lib
COPY plugins /var/www/plugins
COPY static /var/www/static
COPY cron.php index.php ping.php Makefile /var/www/
COPY data/sone.qfc.php.sample /etc/simpleone/

RUN \
    mkdir /data && \
    cd /var/www && \
    ln -s /data ./data && \
    mkdir ./logs

# config
ADD docker/ /

RUN \
    chmod 0600 /etc/crontab && \
    chmod 0644 /etc/logrotate.d/* && \
    ln -s /etc/nginx/sites-available/simpleone.conf /etc/nginx/sites-enabled/simpleone.conf

EXPOSE 80

WORKDIR /var/www

VOLUME /data /var/www/logs /var/log

CMD ["/bin/start.sh"]