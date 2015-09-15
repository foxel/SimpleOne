#!/bin/sh

if [ ! -e /data/sone.qfc.php ]; then
    cp /etc/simpleone/sone.qfc.php.sample /data/sone.qfc.php
fi

chown -R www-data:www-data /data /var/www/logs

exec supervisord -nc /etc/supervisor/supervisord.conf
