#!/bin/sh

PCMT_REG=${1:-"pcmt"}

docker build -t $PCMT_REG/ansible:latest .