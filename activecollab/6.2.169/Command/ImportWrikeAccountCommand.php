<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\Command;

use Angie\Command\Command;
use AngieApplication;
use Exception;
use Integrations;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Users;

/**
 * @package ActiveCollab\Command
 */
class ImportWrikeAccountCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Import Wrike account into Activecollab')
            ->addArgument('client_id', InputArgument::REQUIRED, 'Wrike account Client ID')
            ->addArgument('client_secret', InputArgument::REQUIRED, 'Wrike account Client Secret Key')
            ->addArgument('access_token', InputArgument::REQUIRED, 'Wrike account Acess Token')
            ->addArgument('account_id', InputArgument::REQUIRED, 'Wrike account Client Account ID');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            // get command arguments
            $client_id = $input->getArgument('client_id');
            $client_secret = $input->getArgument('client_secret');
            $access_token = $input->getArgument('access_token');
            $account_id = $input->getArgument('account_id');

            // load wrike importer integration
            $integration = Integrations::findFirstByType('WrikeImporterIntegration');
            if (!($integration instanceof \WrikeImporterIntegration)) {
                throw new Exception('Wrike importer integration does not exists');
            }

            // log first owner as logged user
            AngieApplication::authentication()->setAuthenticatedUser(Users::findFirstOwner());

            // set wrike credentials
            $integration->setCredentials($client_id, $client_secret, $access_token, $account_id);

            // start the import process
            $integration->startImport(function ($message) use ($output) {
                $output->write($message);
            });

            return $this->success('Done', $input, $output);
        } catch (Exception $e) {
            return $this->abortDueToException($e, $input, $output);
        }
    }
}
