#!/bin/sh

# set pm.max_children to number of processing units
sed "/pm.max_children/cpm.max_children = `nproc`" -i /etc/php5/fpm/pool.d/simpleone.conf

if [ ! -e /data/sone.qfc.php ]; then
    cp /etc/simpleone/sone.qfc.php.sample /data/sone.qfc.php
fi

chown -R www-data:www-data /data /var/www/logs

exec supervisord -nc /etc/supervisor/supervisord.conf
