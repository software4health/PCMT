#!/bin/sh

SSH_KEY_PATH=$1

if [ -z "$SSH_KEY_PATH" ]; then
    echo SSH Key path not given
    exit 1
fi

if [ ! -r "$SSH_KEY_PATH" -o ! -f "$SSH_KEY_PATH" ]; then
    echo SSH Key not accessible
    exit 1
fi

docker run -it --rm \
    -e AWS_ACCESS_KEY_ID \
    -e AWS_SECRET_ACCESS_KEY \
    -v "$SSH_KEY_PATH:/root/.ssh/id_rsa:ro" \
    pcmt/ansible bash