#!/bin/sh

AKENEO_VER="v3.1.10"

docker build -f pim/Dockerfile \
    -t pcmt/pim \
    --build-arg AKENEO_VER=${AKENEO_VER} \
    pim/
docker build -f httpd/Dockerfile -t pcmt/httpd httpd/