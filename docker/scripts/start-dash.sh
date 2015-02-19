#!/bin/bash

echo "fastcgi_param PIVOTAL_API_TOKEN $PIVOTAL_API_TOKEN;" >> /etc/nginx/fastcgi_params
echo "fastcgi_param PIVOTAL_API_URL $PIVOTAL_API_URL;" >> /etc/nginx/fastcgi_params
echo "fastcgi_param GITHUB_API_TOKEN $GITHUB_API_TOKEN;" >> /etc/nginx/fastcgi_params
echo "fastcgi_param GITHUB_API_URL $GITHUB_API_URL;" >> /etc/nginx/fastcgi_params

service php5-fpm start

nginx
