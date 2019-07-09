FROM williamyeh/ansible:ubuntu18.04-onbuild

RUN apt-get update && \
    apt-get install python-pip -y && \
    pip install -r requirements/ansible.pip && \
    ansible-galaxy install -p vendor/roles -r requirements/galaxy.yml && \
    apt-get clean && \
    apt-get autoremove