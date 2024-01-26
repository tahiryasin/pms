<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * notification_invoice_pay helper implementation.
 *
 * @package activeCollab.modules.invoicing
 * @subpackage helpers
 */

/**
 * Return payment link for given invoice, if needed.
 *
 * @param  array  $params
 * @return string
 */
function smarty_function_notification_invoice_pay($params)
{
    $context = array_required_var($params, 'context', true, 'Invoice');
    $context_view_url = array_required_var($params, 'context_view_url');
    $recipient = array_required_var($params, 'recipient', true, 'IUser');

    /** @var Invoice $context */
    if ($context->isIssued()) {
        if ($recipient->isFinancialManager()) {
            $label = lang('Go to Invoice', null, true, $recipient->getLanguage());
            $url = $context_view_url;
        } else {
            if ($context->canMakePayment()) {
                $label = lang('Pay Online Now', null, true, $recipient->getLanguage());
            } else {
                $label = lang('View Online', null, true, $recipient->getLanguage());
            }

            $url = $context->getPublicUrl();
        }

        if (isset($label) && isset($url)) {
            return '<table style="margin-top: 16px;" bgcolor="#ffffff" width="100%"><tr><td style="font-size: 120%; text-align: center;"><a href="' . clean($url) . '" target="_blank">' . $label . '</a></td></tr></table>';
        }
    }
}
