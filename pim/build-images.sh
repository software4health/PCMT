#!/bin/sh
######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

AKENEO_VER="v3.2.5"

docker-compose build --build-arg AKENEO_VER=${AKENEO_VER}