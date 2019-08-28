#!/bin/bash
######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

./ddev.sh exec fpm rm -rf var/cache/*
./ddev.sh exec fpm bin/console --env=prod pim:installer:assets --clean
./ddev.sh run --rm node yarn run less
./ddev.sh run --rm node yarn run webpack 
