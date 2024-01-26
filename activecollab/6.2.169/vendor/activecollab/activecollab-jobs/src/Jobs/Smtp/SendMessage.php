<?php

/*
 * This file is part of the Active Collab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\ActiveCollabJobs\Jobs\Smtp;

use InvalidArgumentException;
use PHPMailer\PHPMailer\PHPMailer;
use RuntimeException;

/**
 * @package ActiveCollab\ActiveCollabJobs\Jobs\Smtp
 */
class SendMessage extends Job
{
    /**
     * Construct a new Job instance.
     *
     * @param  array|null               $data
     * @throws InvalidArgumentException
     */
    public function __construct(array $data = null)
    {
        $data['use_native_mailer'] = !empty($data['use_native_mailer']);

        if (!$data['use_native_mailer']) {
            foreach (['smtp_host', 'smtp_port', 'smtp_security'] as $required_argument) {
                if (empty($data[$required_argument])) {
                    throw new InvalidArgumentException("'$required_argument' property is required");
                }
            }
        }

        if (empty($data['smtp_username'])) {
            $data['smtp_username'] = '';
        }

        if (empty($data['smtp_password'])) {
            $data['smtp_password'] = '';
        }

        if (empty($data['instance_id_in_reply_to'])) {
            $data['instance_id_in_reply_to'] = false;
        }

        if (empty($data['from']) || !is_array($data['from']) || empty($data['from']['email'])) {
            throw new InvalidArgumentException("'from' property is required");
        }

        if (empty($data['to']) || !is_array($data['to']) || empty($data['to']['email'])) {
            throw new InvalidArgumentException("'to' property is required");
        }

        foreach (['subject', 'body', 'service_address'] as $required_argument) {
            if (empty($data[$required_argument])) {
                throw new InvalidArgumentException("'$required_argument' property is required");
            }
        }

        if (empty($data['route_reply_to'])) {
            $data['route_reply_to'] = false;
        }

        if (empty($data['message_id'])) {
            $data['message_id'] = '';
        }

        if (empty($data['entity_ref_id'])) {
            $data['entity_ref_id'] = '';
        }

        if (empty($data['attachments'])) {
            $data['attachments'] = [];
        }

        parent::__construct($data);
    }

    /**
     * Use PHPMailer to send an email, and log the message into mailing log.
     */
    public function execute()
    {
        $use_native_mailer = $this->getData()['use_native_mailer'];

        $mailer = $this->getMailer($use_native_mailer);

        if (!$use_native_mailer && !$mailer->getSMTPInstance()->connected()) {
            throw new RuntimeException('Repeat try to send an email, but SMTP is not connected');
        }

        $from = $this->getData()['from'];
        $recipient = $this->getData()['to'];

        $mailer->CharSet = 'utf-8';
        $mailer->Encoding = '8bit';

        $mailer->From = $from['email'];
        $mailer->FromName = $from['name'];

        $mailer->Sender = $from['email']; // Force -f mail() function param

        $mailer->addAddress($recipient['email'], $recipient['name']);

        // ---------------------------------------------------
        //  Configure email replies
        // ---------------------------------------------------

        $message_parent_type = $message_parent_id = null;

        $service_address = $this->getData()['service_address'];

        if ($route_reply_to = $this->getData()['route_reply_to']) {

            // Reply to object
            if (is_array($route_reply_to)) {
                if ($this->getData()['instance_id_in_reply_to']) {
                    $mailer->addReplyTo('notifications-' . $this->getData()['instance_id'] . '@activecollab.com');
                } else {
                    $mailer->addReplyTo($service_address);
                }

                list($message_parent_type, $message_parent_id) = $route_reply_to;

                // Reply to person
            } else {
                $mailer->addReplyTo($route_reply_to); // Direct reply
            }
        }

        $mailer->addCustomHeader('Return-Path', $service_address);

        if ($message_id = $this->getData()['message_id']) {
            $mailer->MessageID = $message_id;
        }

        // ---------------------------------------------------
        //  Subject and body
        // ---------------------------------------------------

        $subject = $this->getData('subject');
        $body = $this->getData('body');

        $mailer->addCustomHeader('Auto-Submitted', 'auto-generated');
        $mailer->addCustomHeader('Precedence', 'bulk');

        if ($entity_ref_id = $this->getData()['entity_ref_id']) {
            $mailer->addCustomHeader('X-Entity-Ref-ID', $entity_ref_id);
        }

        $mailer->isHTML(true);

        $mailer->Subject = $subject;
        $mailer->Body = $body;

        if ($attachments = $this->getData()['attachments']) {
            foreach ($attachments as $attachment) {
                $mailer->addAttachment($attachment['path'], (empty($attachment['name']) ? '' : $attachment['name']));
            }
        }

        // ---------------------------------------------------
        //  Send
        // ---------------------------------------------------

        try {
            $mailer->send();

            $this->logMessageSent(
                (integer) $this->getData('instance_id'),
                $message_parent_type,
                $message_parent_id,
                ($from['name'] ? trim($from['name'] . ' <' . $from['email'] . '>') : $from['email']),
                ($recipient['name'] ? trim($recipient['name'] . ' <' . $recipient['email'] . '>') : $recipient['email']),
                $subject,
                trim(trim($mailer->getLastMessageID(), '<'), '>')
            );
        } catch (\Exception $e) {
            throw new RuntimeException($mailer->ErrorInfo);
        } finally {
            $mailer->smtpClose();
        }
    }

    /**
     * Return mailer instance based on the job settings.
     *
     * @param  bool      $use_native_mailer
     * @return PHPMailer
     */
    private function getMailer($use_native_mailer)
    {
        $mailer = new PHPMailer(true);

        if (!$use_native_mailer) {
            $mailer->SMTPKeepAlive = true;

            list($host, $port, $security, $username, $password, $verify_certificate) = self::getSmtpConnectionParams();

            $mailer->isSMTP();
            $mailer->Host = $host;
            $mailer->Port = $port;

            if ($username && $password) {
                $mailer->SMTPAuth = true;
                $mailer->Username = $username;
                $mailer->Password = $password;
            }

            if (!$verify_certificate) {
                $mailer->SMTPOptions = [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                    ],
                ];
            }

            switch ($security) {
                case 'ssl':
                    $mailer->SMTPSecure = 'ssl';
                    break;
                case 'tls':
                    $mailer->SMTPSecure = 'tls';
                    break;
                case 'auto':
                    $mailer->SMTPAuth = false;
                    break;
            }

            $mailer->smtpConnect($mailer->SMTPOptions);
        }

        return $mailer;
    }

    /**
     * @return array
     */
    private function getSmtpConnectionParams()
    {
        return [
            $this->getData('smtp_host'),
            $this->getData('smtp_port'),
            $this->getData('smtp_security'),
            $this->getData('smtp_username'),
            $this->getData('smtp_password'),
            $this->getData('smtp_verify_certificate'),
        ];
    }

    /**
     * Log that we have successfully sent a message.
     *
     * @param int    $instance_id
     * @param string $parent_type
     * @param int    $parent_id
     * @param string $sender
     * @param string $recipient
     * @param string $subject
     * @param string $message_id
     */
    private function logMessageSent($instance_id, $parent_type, $parent_id, $sender, $recipient, $subject, $message_id)
    {
        if (empty($parent_type)) {
            $parent_type = '';
        }

        $parent_id = (integer) $parent_id;

        if ($parent_id < 1) {
            $parent_id = 0;
        }

        $this->connection->execute('INSERT INTO email_log (instance_id, parent_type, parent_id, sender, recipient, subject, message_id, sent_on) VALUES (?, ?, ?, ?, ?, ?, ?, UTC_TIMESTAMP())', $instance_id, $parent_type, $parent_id, mb_substr($sender, 0, 191), mb_substr($recipient, 0, 191), mb_substr($subject, 0, 191), mb_substr($message_id, 0, 191));
    }
}
