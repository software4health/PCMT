module "vpc" {
  source = "terraform-aws-modules/vpc/aws"

  name = "pcmt"
  cidr = "10.0.0.0/16"

  azs            = ["${var.aws-region}a", "${var.aws-region}b"]
  public_subnets = ["10.0.1.0/24", "10.0.2.0/24"]

  enable_nat_gateway = false
  enable_vpn_gateway = false

  tags = {
    BillTo = "${var.tag-bill-to}"
    Type   = "${var.tag-type}"
  }
}

resource "aws_security_group" "pcmt-web" {
  name        = "pcmt-web"
  description = "Allow http https ssh inbound, all outbound traffic"
  vpc_id      = "${module.vpc.vpc_id}"

  ingress {
    from_port   = 22
    to_port     = 22
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  ingress {
    from_port   = "80"
    to_port     = "80"
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  ingress {
    from_port   = "443"
    to_port     = "443"
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  egress {
    from_port   = 0
    to_port     = 65535
    protocol    = "udp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  egress {
    from_port   = 0
    to_port     = 65535
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = {
    Name   = "pcmt-web"
    BillTo = "${var.tag-bill-to}"
    Type   = "${var.tag-type}"
  }
}

resource "aws_route53_zone" "pcmt" {
  name = "${var.hosted-zone-domain-name}"

  tags = {
    BillTo = "${var.tag-bill-to}"
  }
}

resource "aws_route53_record" "main" {
  zone_id = "${aws_route53_zone.pcmt.zone_id}"
  name    = "${var.domain-name}"
  type    = "A"
  ttl     = 300
  records = ["${aws_instance.app.public_ip}"]
}