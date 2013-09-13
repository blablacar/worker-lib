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

    protected $startTime;
    protected $nbMessagesProcessed = 0;

    public function __construct(ConsumerInterface $consumer)
    {
        $this->consumer = $consumer;
    }

    /**
     * {@inheritDoc}
     */
    public function preProcess(Context $context = null)
    {
        $this->startTime = time();
        if (null === $context) {
            return;
        }

        $context->output(sprintf(
            '<comment>Run worker (pid: <info>%d</info>. Consume <info>%d messages</info> or stop after <info>%ds</info>.</comment>',
            getmypid(),
            $context->getMaxMessages(),
            $context->getMaxExecutionTime()
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function postProcess(Context $context = null)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(\AMQPEnvelope $envelope, \AMQPQueue $queue, Context $context = null)
    {
        $currentStartTime = time();
        if (null === $context) {
            $context = new Context();
        }

        try {
            $consumer = $this->consumer;
            $returnCode = $consumer($envelope, $queue, $context);

            $queue->ack($envelope->getDeliveryTag());

            $context->output(sprintf(
                '<comment>ACK [%s]. Duration <info>%.2fs</info>. Memory usage: <info>%.2f Mo</info></comment>',
                $envelope->getDeliveryTag(),
                time()-$currentStartTime,
                round(memory_get_usage()/1024/1024, 2)
            ));
        } catch (\Exception $e) {
            $queue->nack($envelope->getDeliveryTag(), $context->getRequeueOnError()? AMQP_REQUEUE : null);
            $context->output(sprintf(
                '<error>NACK [%s].</error>',
                $envelope->getDeliveryTag()
            ));

            if ($context->getOutput()->getVerbosity() >= 2) {
                throw $e;
            }
            $returnCode = false;
        }


        $elapsedTime = time()-$this->startTime;
        if (++$nbMessagesProcessed >= $context->getMaxMessages()) {
            $context->output(sprintf(
                '<info>Exiting after processing <comment>%d messages</comment> in <comment>%.2fs</comment>.</info>',
                $this->nbMessagesProcessed,
                $elapsedTime
            ));

            return false;
        }

        if ($elapsedTime >= $context->getMaxExecutionTime()) {
            $context->output(sprintf(
                '<info>Exiting after processing <comment>%d messages</comment> in <comment>%.2fs</comment>.</info>',
                $this->nbMessagesProcessed,
                $elapsedTime
            ));

            return false;
        }

        return $returnCode;
    }
}
