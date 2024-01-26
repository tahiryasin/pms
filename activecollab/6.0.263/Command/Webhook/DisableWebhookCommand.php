<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Command\Webhook;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Webhook;

class DisableWebhookCommand extends WithSelectedWebhookCommand
{
    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Disable a webhook by their ID or URL.')
            ->addOption('delete', '', InputOption::VALUE_NONE, 'Delete the webhook');
    }

    protected function withSelectedWebhook(Webhook $webhook, InputInterface $input): string
    {
        if ($input->getOption('delete')) {
            $webhook->delete();

            return sprintf(
                'Webhook <comment>#%d</comment> (<comment>%s</comment>) is deleted!',
                $webhook->getId(),
                $webhook->getUrl()
            );
        } else {
            $webhook->setIsEnabled(false);
            $webhook->save();

            return sprintf(
                'Webhook <comment>#%d</comment> (<comment>%s</comment>) is disabled.',
                $webhook->getId(),
                $webhook->getUrl()
            );
        }
    }
}
