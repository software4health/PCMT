#!/bin/sh
######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

# Usage:  inteded to be used from Terraform's local provisioner.

if [ -z "$1" ]; then
    echo IP Argument Missing
    exit 1
fi
TARGET_IP=$1

if [ -z "$2" ]; then
    echo Domain Name Missing
    exit 1
fi
HOSTNAME=$2

if [ -z "$AWS_ACCESS_KEY_ID" ]; then
    echo Environment variable AWS_ACCESS_KEY_ID Missing
    exit 1
fi

if [ -z "$AWS_SECRET_ACCESS_KEY" ]; then
    echo Environment variable AWS_SECRET_ACCESS_KEY Missing
    exit 1
fi


docker run --rm \
    -e AWS_ACCESS_KEY_ID \
    -e AWS_SECRET_ACCESS_KEY \
    -e PCMT_PROFILE \
    -e PCMT_VER \
    -e PCMT_ASSET_URL \
    -v pcmt-ssh-key:/tmp/.ssh \
    pcmt/ansible ansible-playbook \
        --limit "$TARGET_IP" \
        -v \
        -i inventory playbook.yml \
        -e ansible_ssh_user=ubuntu \
        -e pcmt_hostname="$HOSTNAME"