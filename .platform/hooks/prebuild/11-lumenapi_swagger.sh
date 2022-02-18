#!/bin/bash -eux

echo "Generating swagger.json for Lumen API. (OHM question API)"
cd ohm/lumenapi
/usr/bin/php /usr/bin/composer.phar swagger

