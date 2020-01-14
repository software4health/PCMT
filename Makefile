######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

.PHONY: all
all: build

.PHONY: build
build:
	./pim/build-images.sh

.PHONY: up
up:
	./bin/quick-up.sh

.PHONY: dev-erd
dev-erd:
	./bin/erd.sh

.PHONY: dev-up
dev-up:
	bin/dev-up.sh

.PHONY: dev-stop
dev-stop:
	./ddev.sh stop

.PHONY: dev-clean
dev-clean:
	rm -rf pim/vendor build/
	./ddev.sh down -v

.PHONY: dev-assets
dev-assets:
	bin/install-assets.sh

.PHONY: dev-cp-vendor
dev-cp-vendor:
	docker cp -L pcmt_fpm_1:/srv/pim/vendor ./pim/vendor

.PHONY: dev-cp-web
dev-cp-web:
    docker cp -L pcmt_fpm_1:/srv/pim/web pim/

.PHONY: dev-fpm
dev-fpm:
	./ddev.sh exec fpm bash

.PHONY: dev-db-restart
dev-db-restart:
	./ddev.sh exec fpm rm -rf var/cache
	./ddev.sh exec fpm bin/console --env=prod pim:install --force --symlink --clean

.PHONY: dev-db-restart-with-assets
dev-db-restart-with-assets:
	./ddev.sh exec fpm rm -rf var/cache
	./ddev.sh exec fpm bin/console --env=prod pim:install --force --symlink --clean
	bin/install-assets.sh

.PHONY: dev-cp-app
dev-cp-app:
	./ddev.sh exec fpm cp -avr /tmp/pcmt/app/. /srv/pim/app/

.PHONY: dev-cp-tmp
dev-cp-tmp:
	docker cp -L pcmt_fpm_1:/tmp ./pim/tmp

.PHONY: dev-test-ecs
dev-test-ecs:
	./ddev.sh run --rm --no-deps fpm /srv/pim/vendor/bin/ecs check src

.PHONY: dev-test-ecs-fix
dev-test-ecs-fix:
	./ddev.sh run --rm --no-deps fpm /srv/pim/vendor/bin/ecs check src --fix

.PHONY: dev-test-unit
dev-test-unit:
	bin/test.sh

.PHONY: terraform
terraform:
	cd deploy/terraform && ./build.sh

.PHONY: ansible
ansible:
	cd deploy/ansible && ./build.sh

.PHONY: cron
cron:
	cd deploy/cron && docker build -t pcmt/cron .

.PHONY: mysql-backup
mysql-backup: cron
	cd deploy/mysql-backup && docker build -t pcmt/mysql-backup .

