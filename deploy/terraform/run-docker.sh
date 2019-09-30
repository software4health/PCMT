#!/bin/sh
######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

SSH_PRIV_KEY_PATH=${SSH_PRIV_KEY_PATH:-"$HOME/.ssh/id_rsa"}
if [ ! -r "$SSH_PRIV_KEY_PATH" -o ! -f "$SSH_PRIV_KEY_PATH" ]; then
    echo "SSH Key $SSH_PRIV_KEY_PATH not accessible"
    exit 1
fi

docker volume create pcmt-ssh-key
docker create --name helper -v pcmt-ssh-key:/tmp/.ssh docker
docker cp $SSH_PRIV_KEY_PATH helper:/tmp/.ssh/id_rsa
docker rm helper

docker run --rm \
    -e AWS_ACCESS_KEY_ID \
    -e AWS_SECRET_ACCESS_KEY \
    -e PCMT_PROFILE \
    -e PCMT_VER \
    -e PCMT_ASSET_URL \
    -v pcmt-ssh-key:/tmp/.ssh \
    -v "/var/run/docker.sock:/var/run/docker.sock" \
    pcmt/terraform "${@}"
docker volume rm pcmt-ssh-key