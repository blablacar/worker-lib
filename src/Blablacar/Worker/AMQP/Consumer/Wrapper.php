<?php
declare(ticks = 1);

namespace Blablacar\Worker\AMQP\Consumer;

use Blablacar\Worker\Util\SignalHandler;

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

    public function __construct($consumer)
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
            '<info>Run worker (pid: <comment>%d</comment>. Consume <comment>%d messages</comment> or stop after <comment>%ds</comment>.</info>',
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
        $currentStartTime = microtime(true);
        if (null === $context) {
            $context = new Context();
        }

        if ($context->getUseSigHandler()) {
            SignalHandler::start();
        }

        try {
            $consumer = $this->consumer;
            $consumer($envelope, $queue, $context);

            $queue->ack($envelope->getDeliveryTag());

            $context->output(sprintf(
                '<comment>ACK [%s]. Duration <info>%.2fs</info>. Memory usage: <info>%.2f Mo</info></comment>',
                $envelope->getDeliveryTag(),
                microtime(true)-$currentStartTime,
                round(memory_get_usage()/1024/1024, 2)
            ));
        } catch (\Exception $e) {
            $queue->nack($envelope->getDeliveryTag(), $context->getRequeueOnError()? AMQP_REQUEUE : null);
            $context->output(sprintf(
                '<error>NACK [%s].</error>',
                $envelope->getDeliveryTag()
            ));

            if (null !== $context->getOutput() && $context->getOutput()->getVerbosity() >= 2) {
                throw $e;
            }
        }


        $elapsedTime = microtime(true)-$this->startTime;
        if (++$this->nbMessagesProcessed >= $context->getMaxMessages()) {
            $context->output(sprintf(
                '<info>Max messages reached. Exiting after processing <comment>%d messages</comment> in <comment>%.2fs</comment>.</info>',
                $this->nbMessagesProcessed,
                $elapsedTime
            ));

            return false;
        }

        if ($elapsedTime >= $context->getMaxExecutionTime()) {
            $context->output(sprintf(
                '<info>Maximum time exceeded. Exiting after processing <comment>%d messages</comment> in <comment>%.2fs</comment>.</info>',
                $this->nbMessagesProcessed,
                $elapsedTime
            ));

            return false;
        }

        if ($context->getUseSigHandler()) {
            $context->output(sprintf(
                '<info>Signal received. Exiting after processing <comment>%d messages</comment> in <comment>%.2fs</comment>.</info>',
                $this->nbMessagesProcessed,
                $elapsedTime
            ));

            return SignalHandler::haveToStop();
        }

        return true;
    }
}
