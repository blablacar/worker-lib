<?php

namespace Blablacar\Worker\AMQP\Consumer;

interface ConsumerInterface
{
    /**
     * __invoke
     *
     * @param \AMQPEnvelope $envelope
     * @param \AMQPQueue $queue
     * @param Context $context
     *
     * @return void
     */
    function __invoke(\AMQPEnvelope $envelope, \AMQPQueue $queue, Context $context = null);
}
