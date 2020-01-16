#!/bin/bash
######################################################################
# Copyright (c) 2020, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

. /set-env.sh
umask 0000
chown docker:docker $SFTP_LOCAL_DIR

files=$(cat /list-files.ftpbatch | envsubst | /ftp-cmd.sh)
files=$(echo "$files" | sed '/^sftp>/d')

(
  echo cd $SFTP_REMOTE_DIR
  echo -mkdir "$SFTP_REMOTE_ARCHIVE_DIR"
  for file in "$files"; do
    echo get "$file"
    echo !chown docker:docker "$file"
    echo !mv "$file" "$SFTP_LOCAL_DIR/$file"
    #echo rename "$file" "$SFTP_REMOTE_ARCHIVE_DIR/$file"
  done
) > /get-files.ftppatch 

cd "/tmp/ftp-get"
cat /get-files.ftppatch | /ftp-cmd.sh
