#!/bin/bash
######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

function cleanup() {
    docker-compose -f docker-compose.yml -f docker-compose.dev.yml down -v
}
trap cleanup EXIT

PCMT_PROFILE=dev docker-compose -f docker-compose.yml \
    -f docker-compose.dev.yml \
    up