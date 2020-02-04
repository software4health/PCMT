# Product Catalog Management Tool (PCMT)

PCMT helps organizations tame their multitudes of Product definitions, 
Catalogs, and coding schemes that exist when a collaboration of many groups
are procuring, using and sharing product data.

More specifically the PCMT is aimed at global health stakeholders to ease
their interoperability and master data challenges.

## License and Copyright

PCMT is an open and freely available community project stewarded by 
VillageReach.  PCMT code and documentation is copyright VillageReach and
licensed under the [NP-OSL v3][np-osl] and the [CC BY-SA 4.0][cc-by-sa].

- [PCMT License & Copyright][pcmt-license]
- [Architecture Decision](./doc/arch/adr-006.md)
- [License How-to](./doc/license-howto.md)
- TODO: Contribution guide

PCMT includes a derivative work of Akeneo PIM Community Edition which is
copyrighted and licensed from Akeneo SAS:

- [Akeneo PIM CE License & Copyright][akeneo-license]
- [Akeneo PIM Source Code][akeneo-source]

[np-osl]: https://opensource.org/licenses/NPOSL-3.0
[cc-by-sa]: https://creativecommons.org/licenses/by-sa/4.0/
[pcmt-license]: ./COPYRIGHT.md
[akeneo-license]: https://github.com/akeneo/pim-community-standard/blob/master/LICENCE.txt
[akeneo-source]: https://github.com/akeneo/pim-community-standard

## Quick Start

1. Clone Repository
1. `make up`
1. Browse to `localhost`
1. Login with `admin` / `Admin123`.

To stop & cleanup:  `make dev-clean`.

## Development

1. Clone Repository
1. `make` to build images
1. `make dev-up` to run containers. 
1. Wait for environment to start, it'll look like:
      ```
      SUCCESS: PCMT Dev now available at http://localhost
      ```

1. Browse to `localhost`
1. Login with `admin` / `admin`.

Notes:
- As local files in `pim/` are updated, the containers will reflect it.
- Run `make dev-assets` to regenerate web assets (e.g. less, js, etc).
- To see the vendor dependencies, run `make dev-cp-vendor` after `make dev-up`,
    which will copy these files from the container to `pim/vendor`.  These files
    are simply copied, not synchronized so don't edit them directly.
- `make dev-fpm` will start a shell with access to Akeneo tools such 
    as `bin/console`.

### Commands

PCMT adds a number of commands that a developer may use.  Unless otherwise 
noted these commands are meant to be run in the `pcmt` container / `fpm` 
service.

Example:

```shell
make dev-fpm
bin/console <command>
```

### Configuration

PCMT secrets and traefik configuration are primarly configured through 
configuration files found in the `conf/` directory.  Examples can be found there
with the extension `.dist`.  If you'd like to change these defaults you should
copy the `.dist` file to a similarly named file in `conf/` without the `.dist`.

For example: `cp conf/parameters.yml.dist conf/parameters.yml`

Then you may point PCMT to the new configuration file by setting an environment
variable.

Example:

```shell
cp conf/paramters.yml.dist conf/parameters.yml
# edit conf/parameters.yml ...
export PCMT_SECRET_CONF=conf/parameters.yml
make up
```

The following environment variables point to their respective configuration
files:

* `PCMT_SECRET_CONF`: Points to a file with secrets used to configure Akeneo.
* `PCMT_TRAEFIK_CONF`: Points to a file with traefik static configuration.

MySQL and ElasticSearch are both configured through their respective docker
container defaults, for now.

#### SSL

The `reverse-proxy` container is responsible for TLS termination using 
[Traefik][traefik].  This repository includes a default static configuration
that's configurable via the file referenced in `$PCMT_TRAEFIK_CONF`, and a 
dynamic configuration that is based on the docker provider, a default 
configuration is included in [docker-compose.tls.yml](docker-compose.tls.yml).

The default configuration could be used by:

```shell
PCMT_PROFILE=dev docker-compose -f docker-compose.yml \
    -f docker-compose.tls.yml \
    up
```

It's recommended that:
- Make a copy of [conf/traefik.toml.dist](conf/traefik.toml.dist) as
  `conf/traefik.toml` and change:
    - Set the `email` field to a valid email.
    - Remove the line `caServer = "https://acme-staging-v02.api.letsencrypt.org/directory"`.
- Set a publicly available hostname with `PCMT_HOSTNAME` when launching, e.g.
  `PCMT_HOSTNAME=pcmt.villagereach.org docker-compose -f docker-compose.yml -f docker-compose.tls.yml up`
  as this will be used to get a certificate with [LetsEncrypt][letsencrypt].

Note that if you re-launch and change `PCMT_HOSTNAME` that you may need to
remove the existing certs in the docker volume `traefikdata`.

[traefik]: https://docs.traefik.io
[letsencrypt]: https://letsencrypt.org

#### Reference Data

`bin/console pcmt:handler:download_reference_data` - Downloads the latest  
reference data from the Internet and stores them alongside the source code.
Run this to update these codes and commit to source control.

`bin/console pcmt:handler:import_reference_data` - Imports the reference data
downloaded in the previous command into the database, potentially overwriting  
any referencedata already there.  Use this in development or testing contexts  
so that the reference data is available in the UI, but beware of running this  
in production.

---
Copyright (c) 2019, VillageReach.  Licensed CC BY-SA 4.0:  https://creativecommons.org/licenses/by-sa/4.0/

## Production

This section covers the additional services added with `docker-compose.tls.yml`
and `docker-compose.prod.yml`.

### Backup and Restore

Restore:

1. With a working instance deployed.
1. Download the backup desired from the instance's S3 bucket.
1. Transfer the backup to the instance.
1. SSH to instance
1. Load the backup into the running database with:
   ```shell
   docker exec -i pcmt_mysql_1 \
   sh -c 'exec mysql -uroot -p"$MYSQL_ROOT_PASSWORD" akeneo_pim' \
   < <(gzip -d -c name-of-backup.sql.zip)
   ```

