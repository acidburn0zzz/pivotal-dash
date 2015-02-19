FROM packethost/php5
MAINTAINER Sam Tresler "quay@packethost.net"

USER root
ENV GH_ROOT /home/packet/github
ENV DOCROOT /home/packet/pivotal-dash

# Copy our build context to a temporary location.
ADD ./ $GH_ROOT/

WORKDIR $GH_ROOT

ADD docker/config/default.packet.net /etc/nginx/sites-available/
ADD docker/config/parameters.yml $DOCROOT/app/config/
ADD docker/config/htpasswd /etc/nginx/

RUN \
  mv $GH_ROOT/docker/scripts/start-php-worker.sh /usr/bin/start-php-worker && \
  chmod 0700 /usr/bin/start-php-worker && \
  rm /etc/nginx/sites-enabled/default && \
  ln -s /etc/nginx/sites-available/default.packet.net /etc/nginx/sites-enabled/default && \
  apt-get update && \
  apt-get install php5-memcache && \
  rsync -av --exclude-from docker/config/excludes.txt $GH_ROOT/ $DOCROOT/ && \
  echo "$(date) <br />" >> $DOCROOT/web/build_info && \
  chown -R packet:packet $DOCROOT && \
  echo "<a href=\"https://github.com/packethost/pivotal-dash/commit/$(git rev-parse --short HEAD)\">$(git rev-parse --short HEAD)</a> <br />" >> $DOCROOT/web/build_info && \
  echo "$(git branch | grep \*) <br />" >> $DOCROOT/web/build_info 

WORKDIR $DOCROOT

RUN \
  curl -sS https://getcomposer.org/installer | php && \
  mv composer.phar /usr/local/bin/composer && \
  su -c "composer update -d $DOCROOT" packet

CMD ["nginx"]

# Expose ports.
EXPOSE 80
EXPOSE 443
