<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * notification_attachments_table helper implementation.
 *
 * @package angie.frameworks.email
 * @subpackage helpers
 */

/**
 * Render notification new comment made.
 *
 * @param  array  $params
 * @return string
 */
function smarty_function_notification_attachments_table($params)
{
    $object = array_required_var($params, 'object', false, 'ApplicationObject');

    if ($object instanceof IAttachments) {
        if ($attachments = $object->getAttachments()) {
            $content = "<table width='100%' id='attachment'><tbody><tr><td style='padding-bottom:15px;'>";

            /** @var Attachment $attachment */
            foreach ($attachments as $attachment) {
                $content .= "&#128206; <a href='" . clean($attachment->getPublicDownloadUrl(true)) . "'>" . clean($attachment->getName()) . "</a> <span style='color:#91918D;'>" . format_file_size($attachment->getSize()) . '</span><br/>';
            }

            return "$content</td></tr></tbody></table>";
        }
    }
}
