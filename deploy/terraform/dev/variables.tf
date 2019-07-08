variable "aws-region" {
  type        = string
  description = "AWS Region to use"
}

variable "tag-name" {
  type        = string
  description = "Name of the environment"
}

variable "tag-type" {
  type        = string
  description = "Type of deployment, dev, staging, producting, buildinf, etc"
}

variable "tag-bill-to" {
  type        = string
  description = "Which project to bill the provisioned resources"
  default     = "PCMT"
}

variable "root-volume-size" {
  type        = number
  description = "Size in GB of root volume"
  default     = "10"
}

variable "instance-type" {
  type        = string
  description = "Size in GB of root volume"
  default     = "t3a.small"
}

variable "ami" {
  type        = string
  description = "AWS AMI to use for compute"
  default     = "ami-0c46f9f09e3a8c2b5"
}

variable "hosted-zone-domain-name" {
  type        = string
  description = "AWS Route53 Hosted Zone Domain Name"
  default     = "pcmt.villagereach.org"
}

variable "domain-name" {
  type        = string
  description = "AWS Route53 Domain Name"
}