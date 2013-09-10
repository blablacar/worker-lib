#!/bin/bash

if [ -z ${RMQ_VER} ]; then
    RMQ_VER=${1}
fi

check_port_http_code() {
    http_code=`echo $(curl -s -o /dev/null -w "%{http_code}" "http://localhost:$1")`
    return `test $http_code = "$2"`
}

echo "Downloading RabbitMQ"
wget http://www.rabbitmq.com/releases/rabbitmq-server/v${RMQ_VER}/rabbitmq-server-generic-unix-${RMQ_VER}.tar.gz

echo "Untar archive"
tar -xzf rabbitmq-server-generic-unix-${RMQ_VER}.tar.gz

echo "Starting server"
./rabbitmq_server-${RMQ_VER}/sbin/rabbitmq-server

while ! check_port_http_code 15672 200; do
    echo -n "."
    sleep 2s
done

echo ""
echo "Server is up"
