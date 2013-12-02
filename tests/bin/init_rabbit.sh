#!/bin/sh

echo "# Preparing vhost"
rabbitmqctl delete_vhost /
rabbitmqctl add_vhost /
rabbitmqctl set_permissions -p / guest ".*" ".*" ".*"

echo "# Enable rabbitmq_management plugin"
rabbitmq-plugins enable rabbitmq_management

if ! type "rabbitmqadmin" > /dev/null
then
    echo "# Installing rabbitmqadmin"
    curl -XGET http://127.0.0.1:15672/cli/rabbitmqadmin > /usr/local/bin/rabbitmqadmin
    chmod +x /usr/local/bin/rabbitmqadmin
fi

echo "# Declaring mapping"
rabbitmqadmin declare exchange name=blablacar_worker_exchange_test type=direct auto_delete=false durable=true
rabbitmqadmin declare exchange name=blablacar_worker_empty_exchange_test type=direct auto_delete=false durable=true

rabbitmqadmin declare queue name=blablacar_worker_queue_test auto_delete=false durable=true
rabbitmqadmin declare queue name=blablacar_worker_empty_queue_test auto_delete=false durable=true
rabbitmqadmin declare queue name=blablacar_worker_delete_queue_test auto_delete=false durable=true

rabbitmqadmin declare binding source=blablacar_worker_exchange_test routing_key=test destination=blablacar_worker_queue_test
rabbitmqadmin declare binding source=blablacar_worker_empty_exchange_test routing_key=test destination=blablacar_worker_empty_queue_test

echo "# Create some messages"
for i in `seq 1 5`
do
    rabbitmqadmin publish routing_key="test" payload="message$i" exchange="blablacar_worker_exchange_test"
done
