output "vpc-subnet-id" {
  value       = module.pcmt-network-dev.vpc-subnet-id
  description = "Id of the first subnet of the VPC"
}

output "security-group-id" {
  value       = module.pcmt-network-dev.security-group-id
  description = "Id of the security group"
}

output "route53-zone-id" {
  value       = module.pcmt-network-dev.route53-zone-id
  description = "Id of the dns zone"
}