<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\Mailer\Decorator;

use AngieApplication;
use DataObject;
use IComments;
use ISubscriptions;
use IUser;
use User;

/**
 * Class Generic.
 *
 * @package Angie\Mailer\Decorator
 */
class Generic extends Decorator
{
    /**
     * Render message header.
     *
     * @param  IUser           $recipient
     * @param  string          $subject
     * @param  DataObject|null $context
     * @param  bool            $supports_go_to_action
     * @return string
     */
    protected function renderHeader(IUser $recipient, $subject, $context = null, $supports_go_to_action = false)
    {
        $return = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>' . clean($subject) . '</title>

    <style type="text/css">
      body { width:100% !important; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; margin:0; padding:0; background: #f1f1f1; }
      a { color: #005EB0; }
      h1, h2, h3, p { margin: 1em 0; }
      pre { overflow-x: scroll; max-width: 455px; background-color: #f1f1f1; padding: 15px; }

      @media only screen and (min-device-width: 601px) {
        .content {
          width: 600px !important;
        }
      }
    </style>
</head>
<body>' . ($supports_go_to_action ? $this->getGoToAction($recipient, $context) : '') . '

<!--[if (gte mso 9)|(IE)]>
  <table width="600" cellpadding="10" cellspacing="0" border="0" align="center">
    <tr>
      <td>
<![endif]-->

<!-- Outer Table -->
<table style="width: 100%; background-color: #f1f1f1;" cellpadding="10" cellspacing="0" border="0" align="center">
<tr>
<td style="font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #999999;" align="center">';

        if ($context instanceof IComments && $context->canCommentViaEmail($recipient)) {
            $return .= '<p style="margin-top: 0;">- ' . lang('Reply above this line to leave a comment', [], true, $recipient->getLanguage()) . ' -</p>';
        }

        $return .= '<table class="content" style="width: 100%; max-width: 600px; background-color: #ffffff; border-width: 1px; border-color: #d1d1d1; border-radius: 3px;" cellpadding="20" cellspacing="0" align="center"><tr><td style="font-family: Helvetica, Arial, sans-serif; font-size: 16px; line-height: 22px; padding-top: 0; color: #000000;" align="left">';

        return $return;
    }

    /**
     * Render message footer.
     *
     * @param  IUser           $recipient
     * @param  DataObject|null $context
     * @param  string          $unsubscribe_url
     * @return string
     */
    protected function renderFooter(IUser $recipient, $context = null, $unsubscribe_url = '')
    {
        $language = $recipient->getLanguage();

        $result = '</td></tr></table><p>' . lang('Delivered by :application_name', ['application_name' => AngieApplication::getName()], true, $language);

        if ($unsubscribe_url && $context instanceof ISubscriptions) {
            $result .= '<br>' . lang('Getting too many notifications?', [], true, $language) . '<br>';
            $result .= '<a href="' . clean($unsubscribe_url) . '" style="color: #999999;">' . lang('Unsubscribe from this :type', ['type' => $context->getVerboseType(true, $language)], true, $language) . '</a>';
            $result .= ' ' . lang('or', [], true, $language) . ' ';
            $result .= '<a href="' . clean(ROOT_URL . '/settings') . '" style="color: #999999;">' . lang('adjust your email settings', [], true, $language) . '</a>.';
        }

        $result .= '</p>
<!-- End Outer Table -->
</td>
</tr>
</table>
<!--[if (gte mso 9)|(IE)]>
  </td>
</tr>
</table>
<![endif]-->
</body></html>';

        return $result;
    }

    /**
     * Render go to action.
     *
     * @see    https://developers.google.com/gmail/markup/reference/go-to-action
     * @param  IUser           $recipient
     * @param  DataObject|null $context
     * @return string
     */
    private function getGoToAction(IUser $recipient, $context = null)
    {
        if ($recipient instanceof User && $context) {
            if (method_exists($context, 'getPublicUrl')) {
                $url = $context->getPublicUrl();
            } else {
                if (method_exists($context, 'getViewUrl')) {
                    $url = $context->getViewUrl();
                } else {
                    return '';
                }
            }

            return '<div itemscope itemtype="http://schema.org/EmailMessage">
          <div itemprop="action" itemscope itemtype="http://schema.org/ViewAction">
            <link itemprop="url" href="' . clean($url) . '"></link>
            <meta itemprop="name" content="View in ' . clean(AngieApplication::getName()) . '"></meta>
          </div>
          <meta itemprop="description" content="Open this ' . clean($context->getVerboseType(true, $recipient->getLanguage()) . ' in ' . AngieApplication::getName()) . '"></meta>
        </div>';
        }

        return '';
    }
}
