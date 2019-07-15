#!/bin/bash

function finish {
    pkill -f job-queue-consumer-daemon
}
trap finish EXIT

./wait.sh mysql 3306
./wait.sh elasticsearch 9200

bin/console --env=prod pim:install --force --symlink --clean
bin/console --env=prod akeneo:batch:job-queue-consumer-daemon &

sudo php-fpm7.2 -F