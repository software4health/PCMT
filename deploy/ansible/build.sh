#!/bin/sh

PCMT_REG=${1:-"registry.gitlab.com/pcmt/pcmt"}

docker build -t $PCMT_REG/ansible:latest .