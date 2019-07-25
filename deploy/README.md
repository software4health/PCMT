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


## Not used

- Not using `aws_key_pair` as using this resource in TF results in the key-pair
  either forcing the key-pair to be recreated if not manually imported, and
  will also break between different environments where taking one down will
  try to remove the key pair.  Not using until this [bug][aws_key_pair_bug] is 
  resolved.

[aws_key_pair_bug]: https://github.com/terraform-providers/terraform-provider-aws/issues/1092