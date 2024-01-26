<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * on_available_integrations event handler.
 *
 * @package ActiveCollab.modules.system
 * @subpackage handlers
 */

/**
 * Handle on_available_integrations event.
 *
 * @param array $integrations
 * @param User  $user
 */
function system_handle_on_available_integrations(array &$integrations, User &$user)
{
    if ($user instanceof Owner) {
        $integrations[] = Integrations::findFirstByType(ClientPlusIntegration::class);
        $integrations[] = Integrations::findFirstByType(SlackIntegration::class);
        $integrations[] = Integrations::findFirstByType(BasecampImporterIntegration::class);
        $integrations[] = Integrations::findFirstByType(TrelloImporterIntegration::class);
        $integrations[] = Integrations::findFirstByType(TestLodgeIntegration::class);
        $integrations[] = Integrations::findFirstByType(HubstaffIntegration::class);
        $integrations[] = Integrations::findFirstByType(TimeCampIntegration::class);
        $integrations[] = Integrations::findFirstByType(ZapierIntegration::class);
        $integrations[] = Integrations::findFirstByType(WrikeImporterIntegration::class);
        $integrations[] = Integrations::findFirstByType(AsanaImporterIntegration::class);

        if (!AngieApplication::isOnDemand()) {
            $integrations[] = Integrations::findFirstByType(DropboxIntegration::class);
            $integrations[] = Integrations::findFirstByType(GoogleDriveIntegration::class);
        }

        if (AngieApplication::isEdgeChannel()) {
            $integrations[] = Integrations::findFirstByType(OneLoginIntegration::class);
        }
    }

    if ($user->isPowerUser()) {
        $integrations[] = Integrations::findFirstByType(SampleProjectsIntegration::class);
    }

    $integrations[] = Integrations::findFirstByType(DesktopAppIntegration::class);
}
