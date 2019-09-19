#!/bin/bash
######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

AKENEO_VER="v3.2.5"
ENV_PATH="$DIR/../.env"

# Path of this script
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

# Load .env file, in format for env
DOT_ENV=$(grep -v '^#' $ENV_PATH | xargs) 

env $DOT_ENV docker-compose -f "$DIR/docker-compose.yml" build \
    --build-arg AKENEO_VER=${AKENEO_VER}