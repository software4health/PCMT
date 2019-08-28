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
1. `./dev.sh`
1. Wait for environment to start, it'll look like:
      ```
      fpm_1            | Fixtures fixtures_product_csv has been successfully executed.
      fpm_1            |
      fpm_1            | Delete jobs for fixtures.
      fpm_1            | Versioning is already up to date.
      fpm_1            | Consumer name: "e1f04492-5ced-4b95-948c-fd245cf3fd97"
      ```

1. In another shell: `docker-compose run --rm node yarn webpack`
1. Browse to `localhost`
1. Login with `admin` / `admin`.

Notes:
- As local files in `pim/` are updated, the containers will reflect it.
- `./ddev.sh exec fpm bash` will start a shell with access to Akeneo tools such 
    as `bin/console`.

---
Copyright (c) 2019, VillageReach.  Licensed CC BY-SA 4.0:  https://creativecommons.org/licenses/by-sa/4.0/
