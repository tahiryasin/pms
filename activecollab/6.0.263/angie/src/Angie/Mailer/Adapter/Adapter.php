<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\Mailer\Adapter;

use Angie\Mailer;
use Attachment;
use DataObject;
use IComments;
use IUser;

/**
 * Base for all application mailer adapters.
 *
 * @package angie.mailer
 */
abstract class Adapter
{
    /**
     * Send a message to a single recipient.
     *
     * @param  IUser             $sender
     * @param  IUser             $recipient
     * @param  string            $subject
     * @param  string            $body
     * @param  DataObject|null   $context
     * @param  Attachment[]|null $attachments
     * @param  callable|null     $on_sent
     * @return int
     */
    abstract public function send(IUser $sender, IUser $recipient, $subject, $body, $context = null, $attachments = null, callable $on_sent = null);

    /**
     * Call $on_sent callback with proper parameters.
     *
     * @param  IUser             $sender
     * @param  IUser             $recipient
     * @param  string            $subject
     * @param  string            $body
     * @param  DataObject|null   $context
     * @param  Attachment[]|null $attachments
     * @param  callable|null     $on_sent
     * @return int
     */
    protected function messageSent(IUser $sender, IUser $recipient, $subject, $body, DataObject $context = null, $attachments = null, callable $on_sent = null)
    {
        if ($on_sent) {
            $from = $sender->getName() . ' <' . $sender->getEmail() . '>';
            $to = $recipient->getName() . ' <' . $recipient->getEmail() . '>';
            $reply_to = $this->routeReplyTo($sender, $recipient, $context);

            call_user_func($on_sent, $from, $to, $subject, $body, $reply_to);
        }

        return 1;
    }

    /**
     * Prepare reply to data based on input paramteres.
     *
     * - False means don't set Reply-To header
     * - String is an actual address
     * - Array is reply to context
     *
     * @param  IUser             $sender
     * @param  IUser             $recipient
     * @param  DataObject        $context
     * @return array|bool|string
     */
    protected function routeReplyTo(IUser $sender, IUser $recipient, DataObject $context = null)
    {
        if ($sender->getEmail() == $recipient->getEmail() || $sender->getEmail() == Mailer::getDefaultSender()->getEmail()) {
            return false;
        } else {
            if ($context instanceof IComments && $context->canCommentViaEmail($recipient)) {
                return $context->getId() ? [get_class($context), $context->getId()] : false;
            } else {
                return $sender->getEmail();
            }
        }
    }
}
