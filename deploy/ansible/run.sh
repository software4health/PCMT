#!/bin/sh

docker run --rm -e AWS_ACCESS_KEY_ID -e AWS_SECRET_ACCESS_KEY -v ~/.ssh/pcmt_id_rsa:/root/.ssh/id_rsa:ro pcmt/ansible sh -c 'ansible-playbook -v -i inventory playbook.yml -e ansible_ssh_user=ubuntu --limit 34.245.182.49'