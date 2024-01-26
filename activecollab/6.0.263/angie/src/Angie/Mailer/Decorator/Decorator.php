<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\Mailer\Decorator;

use DataObject;
use IUser;

/**
 * Abstract email decorator implementation.
 *
 * @package angie.frameworks.mailer
 * @subpackage models
 */
abstract class Decorator
{
    /**
     * Return message subject.
     *
     * @param  string $subject
     * @return string
     */
    public function decorateSubject($subject)
    {
        return mb_substr($subject, 0, 150);
    }

    /**
     * Decorate message body.
     *
     * @param  IUser           $recipient
     * @param  string          $subject
     * @param  string          $body
     * @param  DataObject|null $context
     * @param  string|null     $unsubscribe_url
     * @param  bool            $supports_go_to_action
     * @return string
     */
    public function decorateBody(IUser $recipient, $subject, $body, $context = null, $unsubscribe_url = null, $supports_go_to_action = false)
    {
        return $this->renderHeader($recipient, $subject, $context, $supports_go_to_action) . $body . $this->renderFooter($recipient, $context, $unsubscribe_url);
    }

    // ---------------------------------------------------
    //  Renderers
    // ---------------------------------------------------

    /**
     * Render message header.
     *
     * @param  IUser           $recipient
     * @param  string          $subject
     * @param  DataObject|null $context
     * @param  bool            $supports_go_to_action
     * @return string
     */
    abstract protected function renderHeader(IUser $recipient, $subject, $context = null, $supports_go_to_action = false);

    /**
     * Render message footer.
     *
     * @param  IUser           $recipient
     * @param  DataObject|null $context
     * @param  string          $unsubscribe_url
     * @return string
     */
    abstract protected function renderFooter(IUser $recipient, $context = null, $unsubscribe_url = '');
}
