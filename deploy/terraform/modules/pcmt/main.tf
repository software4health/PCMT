terraform {
  required_providers {
    aws = {
      version = ">= 3.1"
    }
  }
}

provider "aws" {
  alias = "compute"
}

provider "aws" {
  alias = "network"
}
