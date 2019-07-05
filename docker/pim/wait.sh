#!/bin/bash

uri=$1
port=$2

echo "Waiting for $uri at $port"

while ! nc -z ${uri} ${port}; do
    sleep 0.5
done

echo "$uri started"