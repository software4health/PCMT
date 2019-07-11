#!/bin/sh

SSH_KEY_PATH=$1
TARGET_IP=$2

if [ -z "$SSH_KEY_PATH" ]; then
    echo SSH Key path not given
    exit 1
fi

if [ ! -r "$SSH_KEY_PATH" -o ! -f "$SSH_KEY_PATH" ]; then
    echo SSH Key not accessible
    exit 1
fi

if [ -z "$TARGET_IP" ]; then
    echo Target IP not given
    exit 1
fi

docker run --rm \
    -e AWS_ACCESS_KEY_ID \
    -e AWS_SECRET_ACCESS_KEY \
    -v "$SSH_KEY_PATH:/root/.ssh/id_rsa:ro" \
    pcmt/ansible \
    sh -c "ansible-playbook -v -i inventory playbook.yml -e ansible_ssh_user=ubuntu --limit ${TARGET_IP}"