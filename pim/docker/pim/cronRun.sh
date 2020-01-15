#!/bin/bash
######################################################################
# Copyright (c) 2020, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

FILE=/etc/cron.allow

if [ ! -f "$FILE" ]; then
    sudo touch "$FILE"
fi

echo "docker" >> /etc/cron.allow

sudo crontab /srv/pim/crontab
sudo service cron start

