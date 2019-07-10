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

SSH_PRIV_KEY_PATH="${HOME}/.ssh/pcmt_id_rsa"
if [ ! -r "$SSH_PRIV_KEY_PATH" -a ! -f "$SSH_PRIV_KEY_PATH" ]; then
    echo "SSH Key $SSH_PRIV_KEY_PATH not accessible"
    exit 1
fi

TARGET_IP=$1

docker run -e AWS_ACCESS_KEY_ID \
    -e AWS_SECRET_ACCESS_KEY \
    -v $SSH_PRIV_KEY_PATH:/root/.ssh/id_rsa:ro \
    registry.gitlab.com/pcmt/pcmt/ansible ansible-playbook \
        -vvvv \
        -i inventory playbook.yml \
        -e ansible_ssh_user=ubuntu \
        --limit $TARGET_IP