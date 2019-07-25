resource "aws_route53_record" "main" {
  zone_id = "${var.route53-zone-id}"
  name    = "${var.domain-name}"
  type    = "A"
  ttl     = 300
  records = ["${aws_instance.app.public_ip}"]
}