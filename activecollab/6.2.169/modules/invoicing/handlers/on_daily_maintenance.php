<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Module\System\Features\InvoicesFeatureInterface;

function invoicing_handle_on_daily_maintenance()
{
    /** @var QuickbooksIntegration $quckbooks_integration */
    $quckbooks_integration = Integrations::findFirstByType(QuickbooksIntegration::class);

    if ($quckbooks_integration->needReconnect()) {
        try {
            $quckbooks_integration->reconnect();
        } catch (Exception $e) {
            unset($e);
        }
    }

    if (AngieApplication::featureFactory()->makeFeature(InvoicesFeatureInterface::NAME)->isEnabled()) {
        // Send invoice overdue reminders
        require_once InvoicingModule::PATH . '/models/InvoiceOverdueReminders.class.php';
        InvoiceOverdueReminders::send();
    }
}
