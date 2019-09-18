#!/usr/bin/env bash
######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
echo "Installing PCMT Assets(Web only): "
source $DIR/../ddev.sh exec fpm rm -rf var/cache/*
source $DIR/../ddev.sh exec fpm bin/console --env=prod pim:installer:assets --clean
source $DIR/../ddev.sh exec fpm bin/console --env=prod pcmt:handler:import_reference_data
source $DIR/../ddev.sh run --rm node yarn run webpack
