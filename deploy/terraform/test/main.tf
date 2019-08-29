######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

terraform {
  backend "s3" {
    bucket = "pcmt-terraform-states"
    key    = "pcmt-test.tf"
    region = "eu-west-1"
  }
}

provider "aws" {
  region = "${var.aws-region}"
}

data "terraform_remote_state" "pcmt-network-dev" {
  backend = "s3"
  config = {
    bucket = "pcmt-terraform-states"
    key    = "pcmt-network-dev.tf"
    region = "eu-west-1"
  }
}

module "test" {
  source = "../modules/pcmt"

  aws-region              = "${var.aws-region}"
  tag-name                = "${var.tag-name}"
  tag-type                = "${var.tag-type}"
  tag-bill-to             = "${var.tag-bill-to}"
  root-volume-size        = "${var.root-volume-size}"
  instance-type           = "${var.instance-type}"
  app-deploy-group        = "${var.app-deploy-group}"
  hosted-zone-domain-name = "${var.hosted-zone-domain-name}"
  domain-name             = "${var.domain-name}"
  subnet-id               = "${data.terraform_remote_state.pcmt-network-dev.outputs.vpc-subnet-id}"
  security-group-id       = "${data.terraform_remote_state.pcmt-network-dev.outputs.security-group-id}"
  route53-zone-id         = "${data.terraform_remote_state.pcmt-network-dev.outputs.route53-zone-id}"
}
