<?php

namespace Blablacar\Worker\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;
use Blablacar\Worker\AMQP\Manager;

class WorkerCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testLaunchCommandWithoutError()
    {
        $this->markTestIncomplete('Command don\'t seems to work correctly.');

        $consumer = $this->getMock('Blablacar\Worker\AMQP\Consumer\ConsumerInterface');
        $consumer
            ->expects($this->exactly(3))
            ->method('__invoke')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->anything()
            )
            ->will($this->onConsecutiveCalls(true, true, false))
        ;

        $command = $this->getMockForAbstractClass('Blablacar\Worker\Command\WorkerCommand', array('getConsumer', 'gerManager'));
        $command
            ->expects($this->once())
            ->method('getConsumer')
            ->will($this->returnValue($consumer))
        ;
        $command
            ->expects($this->once())
            ->method('getManager')
            ->with($this->anything())
            ->will($this->returnValue(new Manager(new \AMQPConnection())))
        ;

        $app = new Application('Worker');
        $app->add($command);

        $input = new StringInput('consume blablacar_worker_exchange_test');
        $output = new StreamOutput(fopen('php://memory', 'w', false));

        $statusCode = $app->run($input, $output);

        rewind($output->getStream());
        $result = stream_get_contents($output->getStream());

        $this->assertEquals(0, $statusCode);
    }
}

