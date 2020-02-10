# Environments

The following environments are for the PCMT's use to test changes, showcase
work and provide demos.

## Login

Every environment should have the following logins:

| User name | Password |
|-----------|----------|
| admin     | Admin123 |

## CD Environments

These may be operated from the [Environments][gitlab-env] screen.

| Name                           | When it changes              | Purpose                      |
|--------------------------------|------------------------------|------------------------------|
| [demo][demo]                   | Nightly (3am UTC)            | 1.0.0 public demo            |
| [test][test]                   | Every change to `master`     | CD/Test the latest           |
| [showcase][show]               | Manually, at sprint showcase | Sprint Showcases             |
| [dev][dev]                     | Manually as desired          | Experimentation              |
| [beta][beta]                   | Manually as desired          | 1.0.0-beta demo              |
| [rwanda-demo][rwanda-demo]     | Manually as desired          | 1.0.0 demo for Ethiopia      |
| [rwanda-beta][rwanda-beta]     | Manually as desired          | 1.0.0-beta demo for Rwanda   |
| [ethiopia-demo][ethiopia-demo] | Manually as desired          | 1.0.0 demo for Ethiopia      |
| [ethiopia-beta][ethiopia-beta] | Manually as desired          | 1.0.0-beta demo for Ethiopia |

[gitlab-env]: https://gitlab.com/pcmt/pcmt/environments
[demo]: https://demo.productcatalog.io
[test]: https://test.pcmt.villagereach.org
[show]: https://showcase.pcmt.villagereach.org
[dev]: http://dev.pcmt.villagereach.org
[beta]: http://beta.pcmt.villagereach.org
[rwanda-demo]: https://rwanda-demo.productcatalog.io
[rwanda-beta]: http://rwanda-beta.pcmt.villagereach.org
[ethiopia-demo]: https://ethiopia-demo.productcatalog.io
[ethiopia-beta]: http://ethiopia-beta.pcmt.villagereach.org