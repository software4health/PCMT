#!/bin/bash
######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

set -e

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

PCMT_VER=$($DIR/pcmt-ver-sha.sh)

echo "$0 Pushing tagged as $PCMT_VER"
docker push pcmt/pcmt:$PCMT_VER
docker push pcmt/httpd:$PCMT_VER

GIT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
echo "$0 ...Branch detected: $GIT_BRANCH"
if [ "master" = "$GIT_BRANCH" ]; then
    PCMT_SEMVER=$($DIR/pcmt-semver.sh)
    echo "$0 ... Co-tagging as $PCMT_SEMVER"
    docker tag pcmt/pcmt:$PCMT_VER pcmt/pcmt:$PCMT_SEMVER
    docker tag pcmt/httpd:$PCMT_VER pcmt/httpd:$PCMT_SEMVER
    echo "$0 ... Pushing co-tags"
    docker push pcmt/pcmt:$PCMT_SEMVER
    docker push pcmt/httpd:$PCMT_SEMVER
fi