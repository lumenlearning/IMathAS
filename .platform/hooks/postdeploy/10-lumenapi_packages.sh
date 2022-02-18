#!/bin/bash -eux

export COMPOSER_HOME=/tmp/.composer

echo "Installing composer dependencies for Lumen API. (OHM question API)"
cd /var/app/current/ohm/lumenapi
/usr/bin/php /usr/bin/composer.phar install

