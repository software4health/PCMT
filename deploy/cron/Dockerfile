FROM alpine:3.11

RUN apk add bash

ADD entrypoint.sh /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]
CMD ["/usr/sbin/crond", "-f"]

ONBUILD ADD cmd-to-run.sh /cmd-to-run.sh
ONBUILD RUN chmod u+x /cmd-to-run.sh