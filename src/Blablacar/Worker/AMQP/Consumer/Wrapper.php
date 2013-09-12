<?php
declare(ticks = 1);

namespace Blablacar\Worker\AMQP\Consumer;

/**
 * Wrapper
 *
 * @TODO: Refactor the __invoke method !
 */
class Wrapper implements ConsumerInterface
{
    protected $consumer;

    protected $firstRun = true;
    protected $startTime;

    public function __construct(ConsumerInterface $consumer)
    {
        $this->consumer = $consumer;
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(\AMQPEnvelope $envelope, \AMQPQueue $queue, Context $context = null)
    {
        if ($this->firstRun) {
            $this->firstRun = false;
            $this->startTime = time();
            $context->output(sprintf(
                '<comment>Run worker (pid: <info>%d</info>. Consume <info>%d messages</info> or stop after <info>%ds</info>.</comment>',
                getmypid(),
                $context->getMaxMessages(),
                $context->getMaxExecutionTime()
            ));
        }

        if (null === $context) {
            $context = new Context();
        }

        try {
            $consumer = $this->consumer;
            $returnCode = $consumer($envelope, $queue, $context);

            $queue->ack($envelope->getDeliveryTag());

            $context->output(sprintf(
                '<comment>ACK [%s]. Duration <info>%.4fs</info>. Memory usage: <info>%.2f Mo</info></comment>',
                $envelope->getDeliveryTag(),
                time()-$this->startTime,
                round(memory_get_usage()/1024/1024, 2)
            ));
        } catch (\Exception $e) {
            $queue->nack($envelope->getDeliveryTag(), $context->getRequeueOnError()? AMQP_REQUEUE : null);
            $context->output(sprintf(
                '<error>NACK [%s].</error>',
                $envelope->getDeliveryTag()
            ));
            $returnCode = false;
        }

        return $returnCode;
    }
}
