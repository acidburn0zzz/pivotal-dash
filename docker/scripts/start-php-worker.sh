#!/bin/bash

cat << EOF > /home/packet/pivotal-dash/app/config/parameters.yml
# This is all being overwritten when the container starts because symfony has a bug where it will not override ENV variables properly. 
parameters:
   mailer_transport: smtp
   mailer_host: 127.0.0.1
   mailer_user: null
   mailer_password: null
   locale: en
   secret: $SYMFONY__SECRET 
   pivotal.endpoint: $SYMFONY__PIVOTAL__ENDPOINT
   pivotal.token: $SYMFONY__PIVOTAL__TOKEN
   pivotal.account: $SYMFONY__PIVOTAL__ACCOUNT
EOF

php5-fpm
