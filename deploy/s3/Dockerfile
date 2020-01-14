FROM pcmt/cron:latest

RUN apk -v --update add \
        libmagic \
        python \
        py-pip \
        && \
    pip install --upgrade s3cmd python-magic && \
    apk -v --purge del py-pip && \
    rm /var/cache/apk/*