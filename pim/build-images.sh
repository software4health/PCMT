#!/bin/bash
######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

AKENEO_VER="v3.2.5"

# Path of this script
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

# path of .env file
ENV_PATH="$DIR/../.env"

# Load .env file and PCMT_VER, in format for env
DOT_ENV=$(grep -viE '^(PCMT_VER|#)' $ENV_PATH | xargs) 
PCMT_SEMVER=$("$DIR/../bin/pcmt-semver.sh")
PCMT_VER=$("$DIR/../bin/pcmt-ver-sha.sh")
BUILD_ENV="$DOT_ENV PCMT_VER=$PCMT_VER"

env $BUILD_ENV docker-compose -f "$DIR/docker-compose.yml" build \
    --build-arg AKENEO_VER=${AKENEO_VER} \
    --build-arg PCMT_SEMVER=${PCMT_SEMVER}