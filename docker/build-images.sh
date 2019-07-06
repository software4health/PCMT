#!/bin/sh

AKENEO_VER="v3.1.10"
IMG_PREFIX=$1

docker build -f pim/Dockerfile \
    -t $IMG_PREFIX/pim \
    --build-arg AKENEO_VER=${AKENEO_VER} \
    pim/
docker build -f httpd/Dockerfile -t $IMG_PREFIX/httpd httpd/