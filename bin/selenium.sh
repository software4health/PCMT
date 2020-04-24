#!/bin/bash
######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

echo "STARTING SELENIUM TESTS..."

./ddev.sh exec -T -d fpm bin/console --env=test akeneo:batch:job-queue-consumer-daemon

./ddev.sh exec -T fpm /srv/pim/vendor/bin/behat --colors --suite=selenium-core
./ddev.sh exec -T fpm /srv/pim/vendor/bin/behat --colors --suite=selenium-drafts

# to run specific test, comment above lines and uncomment below:
#./ddev.sh exec -T fpm /srv/pim/vendor/bin/behat src/PcmtDraftBundle/FunctionalTests/features/selenium/approve_draft.feature
