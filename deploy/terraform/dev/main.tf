terraform {
  backend "s3" {
    bucket = "pcmt-terraform-states"
    key    = "pcmt-dev.tf"
    region = "eu-west-1"
  }
}

provider "aws" {
  region = "${var.aws-region}"
}

module "pcmt" {
  source = "../modules/pcmt"

  aws-region              = "${var.aws-region}"
  tag-name                = "${var.tag-name}"
  tag-type                = "${var.tag-type}"
  tag-bill-to             = "${var.tag-bill-to}"
  root-volume-size        = "${var.root-volume-size}"
  instance-type           = "${var.instance-type}"
  hosted-zone-domain-name = "${var.hosted-zone-domain-name}"
  domain-name             = "${var.domain-name}"
}
