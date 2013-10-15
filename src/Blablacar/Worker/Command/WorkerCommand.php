<?php

namespace Blablacar\Worker\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Blablacar\Worker\AMQP\Consumer\Context;

abstract class WorkerCommand extends Command
{
    /**
     * getManager
     *
     * @param string $connection
     *
     * @return Manager
     */
    abstract protected function getManager($connection = 'default');

    /**
     * getConsumer
     *
     * @return ConsumerInterface
     */
    abstract protected function getConsumer(InputInterface $input, OutputInterface $output);

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('consume')
            ->setDescription('Consume a queue')
            ->addArgument('queue', InputArgument::REQUIRED, 'Queue to consume')
            ->addArgument('connection', InputArgument::OPTIONAL, 'Connection to use', 'default')
            ->addOption('timeout', null, InputOption::VALUE_REQUIRED, 'Timeout (seconds) before exit', 300)
            ->addOption('max-messages', null, InputOption::VALUE_REQUIRED, 'Max messages to process before exit', 300)
            ->addOption('no-sighandler', null, InputOption::VALUE_NONE, 'Disable signal handlers')
            ->addOption('requeue-on-error', null, InputOption::VALUE_NONE, 'Requeue in the same queue on error')
            ->addOption('poll-interval', null, InputOption::VALUE_REQUIRED, 'Poll interval (in micro-seconds)', 500000)
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queue = $input->getArgument('queue');
        $connection = $input->getArgument('connection');

        // Manager
        try {
            $manager = $this->getManager($connection);
            if (OutputInterface::VERBOSITY_DEBUG === $output->getVerbosity()) {
                $output->writeln(sprintf(
                    'Use connection: "%s"',
                    $manager->getConfig()
                ));
            }
        } catch (\Exception $e) {
            $output->writeln(sprintf(
                '<error>No manager "%s".</error>',
                $connection
            ));

            return 1;
        }

        // Consumer
        $consumer = $this->getConsumer($input, $output);

        // Context
        $context = new Context(
            $input->getOption('timeout'),
            $input->getOption('max-messages'),
            !$input->getOption('no-sighandler'),
            $input->getOption('requeue-on-error'),
            $input->getOption('poll-interval')
        );
        $context->setOutput($output);

        $manager->consume($queue, $consumer, $context);
    }
}
