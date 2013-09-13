<?php

namespace Blablacar\Worker\Util;

class SignalHandler
{
    static protected $handledSignals      = array(SIGTERM, SIGINT, SIGQUIT);
    static protected $shouldExitForSignal = false;

    public static function start()
    {
        if (!extension_loaded('pcntl')) {
            return;
        }

        foreach (self::$handledSignals as $signal) {
            pcntl_signal($signal, function ($signal) {
                SignalHandler::$shouldExitForSignal = true;
            });
        }
    }

    public static function haveToStop()
    {
        if (!extension_loaded('pcntl')) {
            return false;
        }

        pcntl_signal_dispatch();

        foreach (self::$handledSignals as $signal) {
            pcntl_signal($signal, SIG_DFL);
        }

        return self::$shouldExitForSignal;
    }
}
