<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\Command;

use Angie\Command\Command;
use AngieApplication;
use BasecampImporterIntegration;
use Exception;
use Integrations;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Users;

/**
 * @package ActiveCollab\Command
 */
class ImportBasecampAccountCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Import Basecamp Projects to ActiveCollab')
            ->addArgument('account', InputArgument::REQUIRED, 'Basecamp account ID')
            ->addArgument('username', InputArgument::REQUIRED, 'Basecamp account username')
            ->addArgument('password', InputArgument::REQUIRED, 'Basecamp account password');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            // get command arguments
            $basecamp_application_id = $input->getArgument('account');
            $basecamp_username = $input->getArgument('username');
            $basecamp_password = $input->getArgument('password');

            // load basecamp importer integration
            $integration = Integrations::findFirstByType('BasecampImporterIntegration');
            if (!($integration instanceof BasecampImporterIntegration)) {
                throw new Exception('Basecamp importer integration does not exists');
            }

            // log first owner as logged user
            AngieApplication::authentication()->setAuthenticatedUser(Users::findFirstOwner());

            // set basecamp credentials
            $integration->setCredentials($basecamp_username, $basecamp_password, $basecamp_application_id);

            // validate credentials
            $integration->validateCredentials();

            // start the import process
            $integration->startImport();

            return $this->success('Done', $input, $output);
        } catch (Exception $e) {
            return $this->abortDueToException($e, $input, $output);
        }
    }
}
