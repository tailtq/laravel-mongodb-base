FROM kun391/phpup:3.0

RUN apk --update add --virtual build-dependencies build-base openssl-dev autoconf \
      && pecl install mongodb \
      && docker-php-ext-enable mongodb \
      && apk del build-dependencies build-base openssl-dev autoconf \
      && apk add --no-cache $PHPIZE_DEPS \
          && pecl install xdebug \
          && docker-php-ext-enable xdebug

RUN apk add --update redis

RUN apk add --update curl \
    nodejs \
    nodejs-npm

RUN apk add --upgrade nghttp2-libs

RUN node -v \
    npm -v

RUN npm install -g yarn
RUN npm install -g laravel-echo-server
RUN npm install -g socket.io
RUN npm install -g socket.io-client
RUN npm install
RUN supervisord -c /etc/supervisor/supervisord.conf

RUN chown -R root:root /var/www
#USER www-data

RUN chmod -R 777 /var/www

#CMD ["redis-server"]
#
#EXPOSE 6379
