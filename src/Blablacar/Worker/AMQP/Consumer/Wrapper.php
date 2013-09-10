<?php

namespace Blablacar\Worker\AMQP\Consumer;

class Wrapper implements ConsumerInterface
{
    protected $consumer;

    public function __construct(ConsumerInterface $consumer)
    {
        $this->consumer = $consumer;
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(\AMQPEnvelope $envelope, \AMQPQueue $queue, Context $context = null)
    {
        if (null === $context) {
            $context = new Context();
        }

        try {
            $consumer = $this->consumer;
            $returnCode = $consumer($envelope, $queue, $context);

            $queue->ack($envelope->getDeliveryTag());
        } catch (\Exception $e) {
            $queue->nack($envelope->getDeliveryTag(), $context->getRequeueOnError()? AMQP_REQUEUE : null);
            $returnCode = false;
        }

        return $returnCode;
    }
}
