######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

.PHONY: all
all: build

.PHONY: build
build:
	pim/build-images.sh

.PHONY: dev-up
dev-up:
	bin/dev-up.sh

.PHONY: dev-stop
dev-stop:
	./ddev.sh stop

.PHONY: dev-clean
dev-clean:
	./ddev.sh down -v

.PHONY: dev-assets
dev-assets:
	bin/install-assets.sh

.PHONY: dev-fpm
dev-fpm:
	./ddev.sh exec fpm bash

