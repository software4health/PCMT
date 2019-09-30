#!/bin/bash
######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

set -e

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

PCMT_VER=$($DIR/pcmt-ver-sha.sh)

docker push pcmt/pcmt:$PCMT_VER
docker push pcmt/httpd:$PCMT_VER

GIT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
if [ "master" = "$GIT_BRANCH" ]; then
    PCMT_SEMVER=$($DIR/pcmt-semver.sh)
    docker tag pcmt/pcmt:$PCMT_VER pcmt/pcmt:$PCMT_SEMVER
    docker tag pcmt/httpd:$PCMT_VER pcmt/httpd:$PCMT_SEMVER
    docker push pcmt/pcmt:$PCMT_SEMVER
    docker push pcmt/httpd:$PCMT_SEMVER
fi