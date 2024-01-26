<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\Mailer\Adapter;

use ActiveCollab\ActiveCollabJobs\Jobs\Smtp\SendMessage;
use ActiveCollab\JobsQueue\Jobs\Job;
use Angie\Mailer;
use AngieApplication;
use Attachment;
use DataObject;
use EmailIntegration;
use IFile;
use Integrations;
use IUser;
use OnDemand;

/**
 * Deliver email using jobs queue.
 *
 * @package Angie\Mailer
 */
final class Queued extends Adapter
{
    /**
     * Send the message to the jobs queue.
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
    public function send(
        IUser $sender,
        IUser $recipient,
        $subject,
        $body,
        $context = null,
        $attachments = null,
        callable $on_sent = null
    )
    {
        $data = [];

        if (AngieApplication::isOnDemand()) {
            $data['instance_id_in_reply_to'] = true;
            $data['use_native_mailer'] = true;
        } else {
            $data['instance_id_in_reply_to'] = false;
            $data['use_native_mailer'] = false;

            /** @var EmailIntegration $email_integration */
            $email_integration = Integrations::findFirstByType(EmailIntegration::class);

            $data['smtp_host'] = $email_integration->getSmtpHost();
            $data['smtp_port'] = $email_integration->getSmtpPort();
            $data['smtp_security'] = $email_integration->getSmtpSecurity();
            $data['smtp_username'] = $email_integration->getSmtpUsername();
            $data['smtp_password'] = $email_integration->getSmtpPassword();
            $data['smtp_verify_certificate'] = $email_integration->getSmtpVerifyCertificate();

            if (empty($data['smtp_security'])) {
                $data['smtp_security'] = 'auto';
            }

            $in_test = AngieApplication::isInTestMode();

            // If SMTP is not set, and we are not in test, skip email sending
            if ((empty($data['smtp_host']) || empty($data['smtp_port']) || empty($data['smtp_security'])) && !$in_test) {
                AngieApplication::log()->notice(
                    'Skipped sending email to {recipient_email}. Outgoing mail server not configured',
                    [
                        'subject' => $subject,
                        'recipient_email' => $recipient->getEmail(),
                    ]
                );

                return 0;
            }
        }

        $default_sender = Mailer::getDefaultSender();

        $data = array_merge(
            $data,
            [
                'priority' => Job::HAS_HIGHEST_PRIORITY,
                'instance_id' => AngieApplication::getAccountId(),
                'attempts' => 5,
                'delay' => 60,
                'first_attempt_delay' => 0,
                'from' => [
                    'name' => $this->getFrom($sender, $default_sender),
                    'email' => $default_sender->getEmail(),
                ],
                'to' => [
                    'name' => $recipient->getName(),
                    'email' => $recipient->getEmail(),
                ],
                'subject' => $subject,
                'body' => $body,
                'route_reply_to' => $this->routeReplyTo($sender, $recipient, $context),
                'service_address' => $default_sender->getEmail(),
                'message_id' => $this->getMessageId(),
                'entity_ref_id' => $this->getEntityRefId($context),
            ]
        );

        if (is_array($attachments) && !empty($attachments)) {
            $data['attachments'] = [];

            foreach ($attachments as $path => $attachment) {
                if ($attachment instanceof IFile) {
                    $data['attachments'][] = ['path' => $attachment->getPath(), 'name' => $attachment->getName()];
                } else {
                    $data['attachments'][] = ['path' => $path, 'name' => $attachment];
                }
            }
        }

        if (AngieApplication::isOnDemand()) {
            OnDemand::prepareSendMessageJobData($data);
        }

        AngieApplication::jobs()->dispatch(new SendMessage($data), EmailIntegration::JOBS_QUEUE_CHANNEL);

        return $this->messageSent($sender, $recipient, $subject, $body, $context, $attachments, $on_sent);
    }

    private function getFrom(IUser $sender, IUser $default_sender): string
    {
        return $sender->getEmail() == $default_sender->getEmail()
            ? (string) $sender->getDisplayName()
            : $sender->getDisplayName() . ' (' . AngieApplication::getName() . ')';
    }

    /**
     * Return message ID (if we are on-demand) or let PHPMailer generate a value for us it we are not.
     *
     * @return string
     */
    private function getMessageId()
    {
        if (AngieApplication::isOnDemand() && extension_loaded('openssl')) {
            return '<' . base_convert(microtime(), 10, 36) . '-' . base_convert(bin2hex(openssl_random_pseudo_bytes(8)), 16, 36) . '@activecollab.com' . '>';
        } else {
            return '';
        }
    }

    /**
     * @param  DataObject|null $context
     * @return string
     */
    private function getEntityRefId($context)
    {
        if ($context instanceof DataObject) {
            return implode(
                '-',
                [
                    AngieApplication::getAccountId(),
                    $context->getModelName(true, true),
                    $context->getId(),
                ]
            );
        }

        return '';
    }
}
