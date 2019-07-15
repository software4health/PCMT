# Terraform

### Destroy instance: 

```
cd <env>
terraform destroy -target module.pcmt.aws_instance.app
```

### Taint, so that app is re-deployed (destructive):

```
cd <env>
terraform state list #(copy the one that ends in 'docker-deploy')
terraform taint module.pcmt.null_resource.deploy-docker #or the module listed above
terraform apply
```
