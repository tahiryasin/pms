<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * @param IncomingMailMessage $incoming_mail_message
 * @param string              $source
 * @param string              $bounce
 */
function system_handle_on_email_received($incoming_mail_message, $source, &$bounce)
{
    $recipients = $incoming_mail_message->getRecipients();
    $references = $incoming_mail_message->getReferences();
    $default_sender = Angie\Mailer::getDefaultSender()->getEmail();
    $notification_type = '';

    if (AngieApplication::isOnDemand()) {
        $hostname = 'activecollab.com';
    } else {
        $hostname = 'localhost.localdomain';
        if (isset($_SERVER) and array_key_exists('SERVER_NAME', $_SERVER) and !empty($_SERVER['SERVER_NAME'])) {
            $hostname = $_SERVER['SERVER_NAME'];
        } elseif (function_exists('gethostname') && gethostname() !== false) {
            $hostname = gethostname();
        } elseif (php_uname('n') !== false) {
            $hostname = php_uname('n');
        }
    }

    // @TODO refactor this - extract to a function?
    /* ----------------- ROUTE EMAIL ----------------- */
    if (!empty($references)) {
        foreach ($references as $reference) {
            if (strpos($reference, $hostname) !== false) {
                $notification_type = 'mail_to_comment';
                break;
            }
        }
    } elseif ($incoming_mail_message->getSender() == $default_sender) {
        if ($incoming_mail_message->getMailer() == \ActiveCollab\EmailReplyExtractor::ANDROID_MAIL) {
            $bounce = lang("The Android Email application isn't supported. Your reply hasn't been posted as a comment. Please use Gmail or a similar app instead.");

            return; // Default Android email doesn't return reference-id nor in-reply-to headers so there is no possible way to find the context
        }
    }

    $parts = explode('@', $default_sender);

    foreach ($recipients as $key => $recipient) {
        preg_match('/\+(.+)\@/', $recipient, $matches);
        if (!empty($matches) && str_starts_with($recipient, $parts[0], false) && str_ends_with($recipient, $parts[1])) {
            $notification_type = 'mail_to_project';
            $project_hash = $matches[1];
            /*
             Lets throw away this address as we now know which project should we try to load.
             Other recipients are potential subscribers.
            */
            unset($recipients[$key]);
            break; // no need to further iterate through recipients
        }
    }
    /* ----------------- END ROUTE EMAIL ----------------- */

    if ($notification_type !== 'mail_to_comment') {
        return; // No need to deal with this one any further.
    }

    AngieApplication::log()->info('Email import: Email should be imported as a comment', ['source' => $source]);

    // make sure all mail addresses are lower-case
    $recipients = array_map('strtolower', $recipients);

    // make sure default sender mail address is lower-case
    $default_sender = strtolower($default_sender);

    // make sure that address is in notifications-ID@activecollab.com format
    if (AngieApplication::isOnDemand()) {
        $default_sender_bits = explode('@', $default_sender);
        $default_sender = $default_sender_bits[0] . '-' . AngieApplication::getAccountId() . '@' . $default_sender_bits[1];
    }

    if (($key = array_search($default_sender, $recipients)) === false) {
        AngieApplication::log()->info('Email import: This message is not for us, {default_sender} not found in the list of recipients', [
            'source' => $source,
            'default_sender' => $default_sender,
        ]);

        return; // default sender is not among recipients, no need to bounce
    }

    // @TODO subscribe recipients to the parent?
    // remove the default sender from recipients, so others will potentially become subscribers
    // unset($recipients[$key]);

    // find the context
    $trimmed_references = $incoming_mail_message->getTrimmedReferences();
    $context = !empty($trimmed_references) ? AngieApplication::jobsConnection()->executeFirstRow('SELECT parent_type, parent_id FROM email_log WHERE message_id IN ?', $trimmed_references) : null;
    if ($context === null || empty($context['parent_type'])) {
        $bounce = lang("It's not possible to send a reply to this type of notification.");

        return; // Unknown context. No need to deal with this any further.
    }

    $user = Users::findByEmail($incoming_mail_message->getSender());
    if ($user === null || ($user instanceof User) === false) {
        $bounce = lang("Your reply hasn't been posted as a comment. You need to have an account in ActiveCollab to be able to do this.");

        return;
    }

    try {
        /** @var Applicationobject|IComments $context */
        $context = DataObjectPool::get($context['parent_type'], $context['parent_id']);

        $additional = ['attach_uploaded_files' => $incoming_mail_message->getAttachments()];

        if ($context instanceof IComments) {
            if ($context->canComment($user)) {
                $comment = $context->submitComment(
                    nl2br($incoming_mail_message->getBody()),
                    $user,
                    $additional,
                    true
                );

                AngieApplication::log()->event('comment_created_from_email', 'Email import: Message has been imported as comment #{comment_id} to {object} #{object_id}', [
                    'source' => $source,
                    'object' => $context->getVerboseType(true),
                    'object_id' => $context->getId(),
                    'comment_id' => $comment->getId(),
                ]);
            } else {
                $bounce = lang("Your reply hasn't been posted as a comment. It's possible that this item is in trash or you don't have permission to access it.");
            }
        }
    } catch (Exception $e) {
        $bounce = 'Error: ' . $e->getMessage();
    }
}
