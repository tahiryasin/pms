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
use TrelloImporterIntegration;
use Users;

/**
 * Class ImportTrelloAccountCommand.
 */
class ImportTrelloAccountCommand extends Command
{
    /**
     * Configure command.
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Import Trello Boards to ActiveCollab')
            ->addArgument('api_key', InputArgument::REQUIRED, 'Trello api key')
            ->addArgument('api_key_secret', InputArgument::REQUIRED, 'Trello api key secret')
            ->addArgument('access_token', InputArgument::REQUIRED, 'Trello access token')
            ->addArgument('access_token_secret', InputArgument::REQUIRED, 'Trello access token secret');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            // get command arguments
            $trello_api_key = $input->getArgument('api_key');
            $trello_api_key_secret = $input->getArgument('api_key_secret');
            $trello_access_token = $input->getArgument('access_token');
            $trello_access_token_secret = $input->getArgument('access_token_secret');

            // load trello importer integration
            $integration = Integrations::findFirstByType('TrelloImporterIntegration');
            if (!($integration instanceof TrelloImporterIntegration)) {
                throw new Exception('Trello importer integration does not exists');
            }

            // log first owner as logged user
            AngieApplication::authentication()->setAuthenticatedUser(Users::findFirstOwner());

            // set trello credentials
            $integration->setCredentials($trello_api_key, $trello_api_key_secret,
                $trello_access_token, $trello_access_token_secret);

            // validate credentials
            $integration->validateCredentials();

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
