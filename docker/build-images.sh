#!/bin/sh

AKENEO_VER="v3.1.10"
PCMT_REG=${1:-"pcmt"}

docker build -f pim/Dockerfile \
    -t $PCMT_REG/pcmt \
    --build-arg AKENEO_VER=${AKENEO_VER} \
    pim/
docker build -f httpd/Dockerfile -t $PCMT_REG/httpd httpd/