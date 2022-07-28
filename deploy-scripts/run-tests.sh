#!/bin/bash -eux
mysql -h 127.0.0.1 -u root -e 'CREATE DATABASE myopenmathdb;'
cp .env.example .env
cp ohm/api/src/configs/settings-prod.php ohm/api/src/configs/settings.php
php ./setupdb.php
composer migrate-ohm
composer seed-ohm
composer run-script test-mom
# composer run-script test-ohm
./vendor/bin/codecept run unit
cd ohm/lumenapi && composer test