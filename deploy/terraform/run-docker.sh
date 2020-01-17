#!/bin/bash
######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

SSH_PRIV_KEY_PATH=${SSH_PRIV_KEY_PATH:-"$HOME/.ssh/id_rsa"}
HELPER_CONTAINER="pcmt-tf-helper"

function cleanup {
    echo "Cleaning up container and volumes..."
    docker rm "$HELPER_CONTAINER"
    docker volume rm pcmt-ssh-key
    docker volume rm secrets
}
trap cleanup EXIT

# copies file whose path is in param 1, into the deploy-secrets
# volume at the path of param 2.
cpFileFromEnvIntoSecrets() {
    filePath=$1
    volPath=$2
    if [[ -f "$filePath" && -r "$filePath" ]]; then
        docker cp "$filePath" "$HELPER_CONTAINER":"$volPath"
        echo "Secret set: $volPath"
    else
        echo "Secret not set: $volPath"
    fi
}

# check SSH key is available
if [ ! -r "$SSH_PRIV_KEY_PATH" -o ! -f "$SSH_PRIV_KEY_PATH" ]; then
    echo "SSH Key $SSH_PRIV_KEY_PATH not accessible"
    exit 1
fi

# setup ssh and secrets volumes
docker volume create pcmt-ssh-key
docker volume create secrets 
docker create --name "$HELPER_CONTAINER" \
    -v pcmt-ssh-key:/tmp/.ssh \
    -v secrets:/conf \
    busybox

# copy SSH into SSH volume
docker cp $SSH_PRIV_KEY_PATH "$HELPER_CONTAINER":/tmp/.ssh/id_rsa

# copy deploy secrets into secrets volume
cpFileFromEnvIntoSecrets "$PCMT_MYSQL_CREDS_CONF" "/conf/mysql-creds.env"
cpFileFromEnvIntoSecrets "$PCMT_S3_CREDS_CONF" "/conf/aws-s3-creds.env"
cpFileFromEnvIntoSecrets "$PCMT_FTP_GET_CREDS_CONF" "/conf/ftp-get-creds.env"
cpFileFromEnvIntoSecrets "$PCMT_SFTP_PRIVKEY_FILENAME" "/conf/sftp-privkey"

docker rm "$HELPER_CONTAINER"

docker run --rm \
    -e AWS_ACCESS_KEY_ID \
    -e AWS_SECRET_ACCESS_KEY \
    -e PCMT_PROFILE \
    -e PCMT_VER \
    -e PCMT_ASSET_URL \
    -e PCMT_SECRETS_VOLUME='secrets' \
    -v pcmt-ssh-key:/tmp/.ssh \
    -v "/var/run/docker.sock:/var/run/docker.sock" \
    pcmt/terraform "${@}"