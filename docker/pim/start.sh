#!/bin/bash

./wait.sh mysql 3306
./wait.sh elasticsearch 9200

bin/console --env=prod pim:install --force --symlink --clean

sudo php-fpm7.2 -F