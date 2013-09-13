<?php

namespace Blablacar\Worker\AMQP\Consumer;

interface ConsumerInterface
{
    /**
     * preProcess
     *
     * @param Context $context
     *
     * @return void
     */
    function preProcess(Context $context = null);

    /**
     * postProcess
     *
     * @param Context $context
     *
     * @return void
     */
    function postProcess(Context $context = null);

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
