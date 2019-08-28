#!/bin/bash
######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

if [ -d "/tmp/pcmt" ]; then
    echo Found /tmp/pcmt, copying into pim...
    cp -rfv /tmp/pcmt/* /srv/pim/
    composer update
else
    echo /tmp/pcmt not found, ignoring.
fi