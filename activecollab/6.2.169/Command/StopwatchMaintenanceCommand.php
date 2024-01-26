<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\Command;

use ActiveCollab\Module\Tracking\Utils\StopwatchMaintenanceRunnerInterface;
use Angie\Command\Command;
use AngieApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StopwatchMaintenanceCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->setDescription('Stopwatch Maintenance at Activecollab');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $runner = AngieApplication::getContainer()
            ->get(StopwatchMaintenanceRunnerInterface::class);
        $runner->run();

        return $this->success('Stopwatch maintenance done.', $input, $output);
    }
}
