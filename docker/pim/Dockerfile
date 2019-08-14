######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

FROM akeneo/fpm:php-7.2
LABEL maintainer="josh.zamor@villagereach.org"
ARG AKENEO_URL=https://github.com/akeneo/pim-community-standard/archive/
ARG AKENEO_VER

RUN mkdir -p /srv/pim && \
    chown docker:docker /srv/pim && \
    apt-get update && \
    apt-get install netcat -y && \
    apt-get clean && \
    apt-get autoremove
USER docker
ADD --chown=docker:docker ${AKENEO_URL}${AKENEO_VER}.tar.gz /home/docker/pim.tar.gz
RUN tar xvzf /home/docker/pim.tar.gz -C /srv/pim --strip-components=1 && \
    cd /srv/pim && \
    php -d memory_limit=3G /usr/local/bin/composer update && \
    rm -rf var/cache/*
ADD --chown=docker:docker wait.sh /srv/pim/
ADD --chown=docker:docker start.sh /srv/pim/
ADD --chown=docker:docker parameters.yml /srv/pim/app/config/parameters.yml

CMD /srv/pim/start.sh
