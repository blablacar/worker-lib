<?php

namespace Blablacar\Worker\AMQP\Consumer;

use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Wrapper
 *
 * @TODO: Stopwatch must not be passed in the constructor (and must be null by
 * default)
 * @TODO: Refactor the __invoke method !
 */
class Wrapper implements ConsumerInterface
{
    protected $consumer;
    protected $stopwatch;

    public function __construct(ConsumerInterface $consumer, Stopwatch $stopwatch)
    {
        $this->consumer  = $consumer;
        $this->stopwatch = $stopwatch;
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(\AMQPEnvelope $envelope, \AMQPQueue $queue, Context $context = null)
    {
        $stopwatchKey = spl_object_hash($envelope);
        if (!$this->stopwatch->isStarted($stopwatchKey)) {
            $this->stopwatch->start($stopwatchKey);
        }

        if (null === $context) {
            $context = new Context();
        }

        try {
            $consumer = $this->consumer;
            $returnCode = $consumer($envelope, $queue, $context);

            $queue->ack($envelope->getDeliveryTag());

            $event = $this->stopwatch->lap($stopwatchKey);
            $periods = $event->getPeriods();
            $usage = round(end($periods)->getMemory()/1024/1024, 2);

            $context->output(sprintf(
                '<comment>ACK [%s]. Duration <info>%ss</info>. Memory usage: <info>%s</info></comment>',
                $envelope->getDeliveryTag(),
                end($periods)->getDuration(),
                $usage
            ));
        } catch (\Exception $e) {
            $queue->nack($envelope->getDeliveryTag(), $context->getRequeueOnError()? AMQP_REQUEUE : null);
            $returnCode = false;
        }

        return $returnCode;
    }
}
