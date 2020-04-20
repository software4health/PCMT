#!/bin/bash
######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

echo "STARTING SELENIUM TESTS..."

./ddev.sh exec -T -d fpm bin/console --env=test akeneo:batch:job-queue-consumer-daemon

./ddev.sh exec -T fpm /srv/pim/vendor/bin/behat --suite=selenium-core
./ddev.sh exec -T fpm /srv/pim/vendor/bin/behat --suite=selenium-drafts
