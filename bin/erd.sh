#!/bin/bash

######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
ERD_DIR="$DIR/../build/erd"
CONF_DIR="$DIR/../conf"
PARAMS_FILE='parameters.yml.dist'

function cleanup {
    echo "Cleaning up container"
    docker rm pcmt_schemaspy
}
trap cleanup EXIT

function yq() {
    local retval=$(docker run --rm -i -v "$CONF_DIR:/workdir:ro" mikefarah/yq \
    yq \
    read "$PARAMS_FILE" \
    $@)
    echo "$retval"
}

echo "Reading from $CONF_DIR/$PARAMS_FILE"
db_host=$(yq parameters.database_host)
db_port=$(yq parameters.database_port)
db_name=$(yq parameters.database_name)
db_user=$(yq parameters.database_user)
db_pass=$(yq parameters.database_password)
cat <<END
    DB host ($db_host)
    DB port ($db_port)
    DB name ($db_name)
    DB user ($db_user)
    DB pass ($db_pass)
END

docker run \
    --network pcmt_akeneo \
    --name pcmt_schemaspy \
    schemaspy/schemaspy:snapshot \
        -t mysql \
        -host "$db_host" \
        -port "$db_port" \
        -db "$db_name" \
        -s "$db_name" \
        -u "$db_user" \
        -p "$db_pass" \
        -norows \
        -hq 

echo "Copying to $ERD_DIR"
rm -Rf $ERD_DIR
mkdir -p $ERD_DIR
docker cp pcmt_schemaspy:/output/. $ERD_DIR/