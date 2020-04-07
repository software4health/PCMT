#!/bin/bash
######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

./ddev.sh -f docker-compose.test.yml run --rm fpm /srv/pim/vendor/bin/behat --suite=selenium-core
./ddev.sh -f docker-compose.test.yml run --rm fpm /srv/pim/vendor/bin/behat --suite=selenium-drafts
