#!/bin/bash

if [ -z ${RMQ_VER} ]; then
    RMQ_VER=${1}
fi

echo "Downloading RabbitMQ"
wget http://www.rabbitmq.com/releases/rabbitmq-server/v${RMQ_VER}/rabbitmq-server-generic-unix-${RMQ_VER}.tar.gz

echo "Untar archive"
tar -xzf rabbitmq-server-generic-unix-${RMQ_VER}.tar.gz

echo "Starting server"
./rabbitmq_server-${RMQ_VER}/sbin/rabbitmq-server &

sleep 5s
