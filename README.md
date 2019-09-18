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
1. `PCMT_PROFILE=dev docker-compose up -d`
1. Browse to `localhost`
1. Login with `admin` / `admin`.

To stop & cleanup:  `docker-compose down -v`.

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
