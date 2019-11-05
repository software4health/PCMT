# Environments

The following environments are for the PCMT's use to test changes, showcase
work and provide demos.

## Login

Every environment should have the following logins:

| User name | Password |
|-----------|----------|
| admin     | admin    |

## CD Environments

These may be operated from the [Environments][gitlab-env] screen.

| Name                       | When it changes              | Purpose                    |
|----------------------------|------------------------------|----------------------------|
| [test][test]               | Every change to `master`     | CD/Test the latest         |
| [showcase][show]           | Manually, at sprint showcase | Sprint Showcases           |
| [dev][dev]                 | Manually as desired          | Experimentation            |
| [beta][beta]               | Manually as desired          | 1.0.0-beta demo            |
| [rwanda-beta][rwanda-beta] | Manually as desired          | 1.0.0-beta demo for Rwanda |

[gitlab-env]: https://gitlab.com/pcmt/pcmt/environments
[test]: http://test.pcmt.villagereach.org
[show]: http://showcase.pcmt.villagereach.org
[dev]: http://dev.pcmt.villagereach.org
[beta]: http://beta.pcmt.villagereach.org
[rwanda-beta]: http://rwanda-beta.pcmt.villagereach.org