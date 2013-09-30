<?php

namespace Blablacar\Worker\AMQP\Consumer;

use Symfony\Component\Console\Output\OutputInterface;

class Context
{
    protected $maxExecutionTime = 300;
    protected $maxMessages      = 300;
    protected $useSigHandler    = false;
    protected $requeueOnError   = false;
    protected $pollInterval     = 500000; // microseconds

    protected $output;

    public function __construct(
        $maxExecutionTime = 300,
        $maxMessages      = 300,
        $useSigHandler    = false,
        $requeueOnError   = false,
        $pollInterval     = 500000
    )
    {
        $this->maxExecutionTime = $maxExecutionTime;
        $this->maxMessages      = $maxMessages;
        $this->useSigHandler    = $useSigHandler;
        $this->requeueOnError   = $requeueOnError;
        $this->pollInterval     = $pollInterval;
    }

    public function output($output)
    {
        if (null === $this->output) {
            return;
        }

        $this->output->writeln($output);
    }

    public function getMaxExecutionTime()
    {
        return $this->maxExecutionTime;
    }

    public function setMaxExecutionTime($maxExecutionTime)
    {
        $this->maxExecutionTime = $maxExecutionTime;
    }

    public function getMaxMessages()
    {
        return $this->maxMessages;
    }

    public function setMaxMessages($maxMessages)
    {
        $this->maxMessages = $maxMessages;
    }

    public function getUseSigHandler()
    {
        return $this->useSigHandler;
    }

    public function setUseSigHandler($useSigHandler)
    {
        $this->useSigHandler = $useSigHandler;
    }

    public function getRequeueOnError()
    {
        return $this->requeueOnError;
    }

    public function setRequeueOnError($requeueOnError)
    {
        $this->requeueOnError = $requeueOnError;
    }

    public function getOutput()
    {
        return $this->output;
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function getPollInterval()
    {
        return $this->pollInterval;
    }

    public function setPollInterval($pollInterval)
    {
        $this->pollInterval = $pollInterval;
    }
}
