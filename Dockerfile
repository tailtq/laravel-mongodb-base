FROM kun391/phpup:3.0

RUN apk add --update redis

RUN apk add --update curl \
    nodejs \
    nodejs-npm

RUN node -v \
    npm -v

RUN npm install -g yarn
RUN npm install -g laravel-echo-server
RUN npm install -g socket.io
RUN npm install -g socket.io-client
RUN npm install
RUN supervisorctl reload

#CMD ["redis-server"]
#
#EXPOSE 6379
