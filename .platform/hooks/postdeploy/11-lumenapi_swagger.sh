#!/bin/bash -eux

echo "Generating swagger.json for Lumen API. (OHM question API)"
cd /var/app/current/ohm/lumenapi
/usr/bin/php /usr/bin/composer.phar swagger

