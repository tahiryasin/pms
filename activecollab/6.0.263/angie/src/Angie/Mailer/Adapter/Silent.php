<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\Mailer\Adapter;

use Attachment;
use DataObject;
use IUser;

/**
 * Silent mailer adapter.
 *
 * Different between silend and disabled adapter is that disabled does not log
 * any activity, while silend will do everything regular adapter would do
 * except actually sending an email
 *
 * @package angie.mailer
 */
final class Silent extends Adapter
{
    /**
     * @param  IUser             $sender
     * @param  IUser             $recipient
     * @param  string            $subject
     * @param  string            $body
     * @param  DataObject|null   $context
     * @param  Attachment[]|null $attachments
     * @param  callable|null     $on_sent
     * @return int
     */
    public function send(IUser $sender, IUser $recipient, $subject, $body, $context = null, $attachments = null, callable $on_sent = null)
    {
        return $this->messageSent($sender, $recipient, $subject, $body, $context, $attachments, $on_sent);
    }
}
