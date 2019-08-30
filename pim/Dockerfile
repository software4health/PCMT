######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

#--- Akeneo base ---
FROM akeneo/fpm:php-7.2 as akeneo
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
    rm /home/docker/pim.tar.gz


#--- PCMT PIM ---
FROM akeneo as pim
LABEL maintainer="josh.zamor@villagereach.org"
COPY --from=akeneo --chown=docker:docker /srv/pim /srv/pim
ADD --chown=docker:docker composer.* /srv/pim/
ADD --chown=docker:docker app/ /srv/pim/app/
ADD --chown=docker:docker src/ /srv/pim/src/
ADD --chown=docker:docker docker/pim/wait.sh /srv/pim/
ADD --chown=docker:docker docker/pim/start.sh /srv/pim/
ADD --chown=docker:docker docker/pim/cpFromTmp.sh /srv/pim/
ADD --chown=docker:docker docker/pim/parameters.yml /srv/pim/app/config/

USER docker
WORKDIR /srv/pim    
RUN php -d memory_limit=3G /usr/local/bin/composer install && \
    rm -rf var/cache/*
CMD /srv/pim/start.sh

#--- Node ---
FROM pim
FROM node:10-slim AS node
USER node
COPY --from=pim --chown=node:node /srv/pim /srv/pim
WORKDIR /srv/pim
RUN yarn install && \
    yarn run less && \
    yarn run webpack-dev

#--- Httpd ---
FROM httpd:2.4 as httpd
LABEL maintainer="josh.zamor@villagereach.org"
COPY --from=node --chown=1000:1000 /srv/pim /srv/pim
COPY --from=node --chown=root:www-data /srv/pim/docker/httpd.conf /usr/local/apache2/conf/httpd.conf
COPY --from=node --chown=root:www-data /srv/pim/docker/akeneo.conf /usr/local/apache2/conf/vhost.conf
