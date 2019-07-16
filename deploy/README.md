# Terraform

### Destroy instance: 

```
cd <env>
terraform destroy -target module.pcmt.aws_instance.app
```

### Taint, so that app is re-deployed (destructive):

```
cd <env>
terraform taint module.pcmt.null_resource.deploy-docker
terraform apply
```
Note:  if the module can't be found durring `terraform taint` then run
`terraform state list` and copy the one that ends in `docker-deploy`, using
it in the `terraform taint` command.
