#!/bin/sh

if [ -z "$1" ]; then
    echo IP Argument Missing
    exit 1
fi

if [ -z "$AWS_ACCESS_KEY_ID" ]; then
    echo Environment variable AWS_ACCESS_KEY_ID Missing
    exit 1
fi

if [ -z "$AWS_SECRET_ACCESS_KEY" ]; then
    echo Environment variable AWS_SECRET_ACCESS_KEY Missing
    exit 1
fi

TARGET_IP=$1

docker run --rm \
    -e AWS_ACCESS_KEY_ID \
    -e AWS_SECRET_ACCESS_KEY \
    -v pcmt-ssh-key:/tmp/.ssh \
    pcmt/ansible ansible-playbook \
        -v \
        -i inventory playbook.yml \
        -e ansible_ssh_user=ubuntu \
        --limit $TARGET_IP