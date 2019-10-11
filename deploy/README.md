# Infrastructure as Code

This stack includes:
- Terraform for provisioning cloud resources (compute, network, etc)
- Ansible for configuration management (installing docker, PCMT, etc)
- Docker for using Terraform and Ansible (recommended).

## Requirements

- Docker (19.09+)
- Docker-Compose (1.23.2+)
- Terraform (optional, recommended use is through Docker)
- Ansible (optional, recommended use is through Docker)

## Settings

The following environment variables are available:

- `AWS_ACCESS_KEY_ID`: The AWS key for Terraform to use.
- `AWS_SECRET_ACCESS_KEY`: The AWS key for Terraform to use.
- `SSH_PRIV_KEY_PATH`: path to the SSH key that Ansible will need to configure
  the instance.
- `PCMR_PROFILE`: Profile as documented in PCMT's `settings.env`.
- `PCMT_VER`: The version of PCMT to deploy, this version will need to be
  tagged in Docker Hub.
- `PCMT_ASSET_URL`: The full, public, URL where a commit's SHA may be appended
  to retrieve the deployment descriptors for the specified version.


## Quick Start

The recommended useage is through running docker containers which hold PCMT's
Terraform and Ansible configurations.  A helper script `run-docker.sh` is
included to streamline this interaction.

The command's format is: `./run-docker.sh <env-name> <terraform command>`

__Example__: show the plan for the `cd-test` environment:
```bash
export AWS_ACCESS_KEY_ID=<insert>
export AWS_SECRET_ACCESS_KEY=<insert>
export SSH_PRIV_KEY_PATH=$HOME/.ssh/id_rsa
export PCMT_PROFILE=production

./run-docker.sh cd-test plan
```

__Example__: create/update the `cd-test` env, and clear the database:

```bash
export PCMT_VER=0.1.0-snapshot
export PCMT_ASSET_URL=https://gitlab.com/pcmt/pcmt/raw/<commit-sha>"
PCMT_PROFILE=dev ./run-docker.sh cd-test apply -auto-approve
```

__Example__: destroy the `cd-test` environment

```bash
SSH_PRIV_KEY_PATH=$HOME/.ssh/id_rsa ./run-docker.sh cd-test destroy -auto-approve
```

---
Copyright (c) 2019, VillageReach.  Licensed CC BY-SA 4.0:  https://creativecommons.org/licenses/by-sa/4.0/
