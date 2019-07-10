resource "aws_instance" "app" {
  ami                    = "${data.aws_ami.ubuntu-latest.id}"
  instance_type          = "${var.instance-type}"
  key_name               = "${aws_key_pair.ec2.id}"
  subnet_id              = "${module.vpc.public_subnets[0]}"
  vpc_security_group_ids = ["${aws_security_group.pcmt-web.id}"]

  root_block_device {
    volume_type           = "gp2"
    volume_size           = "${var.root-volume-size}"
    delete_on_termination = "true"
  }

  tags = {
    Name   = "${var.tag-name}"
    BillTo = "${var.tag-bill-to}"
    Type   = "${var.tag-type}"
    DeployGroup = "${var.app-deploy-group}"
  }

  volume_tags = {
    BillTo = "${var.tag-bill-to}"
    Type   = "${var.tag-type}"
  }
}

data "aws_ami" "ubuntu-latest" {
  most_recent = true
  owners = ["099720109477"]

  filter {
    name = "name"
    values = ["ubuntu/images/hvm-ssd/ubuntu-bionic-*"]
  }

  filter {
    name = "architecture"
    values = ["x86_64"]
  }
}

resource "null_resource" "deploy-docker" {
  count = 0
  depends_on = ["aws_instance.app"]

  connection {
    user = "ubuntu"
    host = "${aws_instance.app.public_ip}"
  }

  provisioner "remote-exec" {
    inline = ["ls"]

    connection {
      type = "ssh"
      user = "ubuntu"
    }
  }

  provisioner "local-exec" {
    command = "docker run registry.gitlab.com/pcmt/pcmt/ansible ansible-playbook-wrapper -vvvv -i inventory docker.yml -e ansible_ssh_user=ubuntu --limit ${aws_instance.app.public_ip}"
  }
}