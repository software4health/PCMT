FROM hashicorp/terraform:light
RUN apk add docker bash

ADD script/docker-entrypoint.sh /docker-entrypoint.sh
ADD . /app
WORKDIR /app

ENTRYPOINT ["/docker-entrypoint.sh"]