terraform {
  backend "s3" {
    bucket = "pcmt-terraform-states"
    key    = "pcmt-network-dev.tf"
    region = "eu-west-1"
  }
}

provider "aws" {
  region = "${var.aws-region}"
}

module "pcmt-network-dev" {
  source = "../modules/pcmt-network"

  aws-region              = "${var.aws-region}"
  tag-type                = "${var.tag-type}"
  tag-bill-to             = "${var.tag-bill-to}"
  hosted-zone-domain-name = "${var.hosted-zone-domain-name}"
}
