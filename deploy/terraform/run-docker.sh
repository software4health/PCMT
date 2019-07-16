#!/bin/bash

docker run --rm \
    -e AWS_ACCESS_KEY_ID \
    -e AWS_SECRET_ACCESS_KEY \
    -v "/var/run/docker.sock:/var/run/docker.sock" \
    pcmt/terraform "${@}"