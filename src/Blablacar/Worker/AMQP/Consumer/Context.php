<?php

namespace Blablacar\Worker\AMQP\Consumer;

use Symfony\Component\Console\Output\OutputInterface;

class Context
{
    protected $maxExecutionTime;
    protected $maxMessages;
    protected $useSigHandler  = false;
    protected $requeueOnError = false;

    protected $output;

    public function __construct($maxExecutionTime = null, $maxMessages = null, $useSigHandler = false, $requeueOnError = false)
    {
        $this->maxExecutionTime = $maxExecutionTime;
        $this->maxMessages      = $maxMessages;
        $this->useSigHandler    = $useSigHandler;
        $this->requeueOnError   = $requeueOnError;
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
}
