#!/bin/sh

docker run -it --rm -e AWS_ACCESS_KEY_ID -e AWS_SECRET_ACCESS_KEY -v ~/.ssh/pcmt_id_rsa:/root/.ssh/id_rsa:ro registry.gitlab.com/pcmt/pcmt/ansible bash