FROM node:10-slim AS node
USER node
COPY --from=pcmt/pim --chown=node:node /srv/pim /srv/pim
WORKDIR /srv/pim
RUN yarn install && \
    yarn run less && \
    yarn run webpack-dev

FROM httpd:2.4
MAINTAINER Josh Zamor (josh.zamor@villagereach.org)
COPY --from=node --chown=1000:1000 /srv/pim /srv/pim
COPY --from=node --chown=root:www-data /srv/pim/docker/httpd.conf /usr/local/apache2/conf/httpd.conf
COPY --from=node --chown=root:www-data /srv/pim/docker/akeneo.conf /usr/local/apache2/conf/vhost.conf