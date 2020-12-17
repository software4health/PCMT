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
./ddev.sh exec -T fpm /srv/pim/vendor/bin/behat --colors --suite=selenium-permissions
./ddev.sh exec -T fpm /srv/pim/vendor/bin/behat --colors --suite=selenium-rules

# to run specific test, comment above lines and uncomment below:
#./ddev.sh exec -T fpm /srv/pim/vendor/bin/behat src/PcmtDraftBundle/FunctionalTests/features/selenium/approve_draft.feature
#./ddev.sh exec -T fpm /srv/pim/vendor/bin/behat --colors src/PcmtCoreBundle/FunctionalTests/features/selenium/create_reference_data_county_code.feature
#./ddev.sh exec -T fpm /srv/pim/vendor/bin/behat --colors src/PcmtCoreBundle/FunctionalTests/features/selenium/create_reference_data_language_code.feature
#./ddev.sh exec -T fpm /srv/pim/vendor/bin/behat --colors src/PcmtDraftBundle/FunctionalTests/features/selenium/approve_draft.feature
#./ddev.sh exec -T fpm /srv/pim/vendor/bin/behat --colors src/PcmtDraftBundle/FunctionalTests/features/selenium/bulk_approve.feature
#./ddev.sh exec -T fpm /srv/pim/vendor/bin/behat --colors src/PcmtRulesBundle/FunctionalTests/features/selenium/05_run_rule.feature
#./ddev.sh exec -T fpm /srv/pim/vendor/bin/behat --colors src/PcmtCoreBundle/FunctionalTests/features/selenium/create_family.feature
#./ddev.sh exec -T fpm /srv/pim/vendor/bin/behat --colors src/PcmtCoreBundle/FunctionalTests/features/selenium/edit_family.feature
