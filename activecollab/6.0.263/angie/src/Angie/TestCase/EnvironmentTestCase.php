<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace Angie\TestCase;

use Angie\Mailer;
use Angie\Mailer\Adapter\Silent as SilentMailer;
use AngieApplication;
use AnonymousUser;
use DateTimeValue;
use DB;

abstract class EnvironmentTestCase extends ModelTestCase
{
    protected $mailing_log = [];

    public function setUp()
    {
        parent::setUp();

        // Initialize authentication
        AngieApplication::authentication();

        // Set logged user
        AngieApplication::authentication()->setAuthenticatedUser($this->owner);

        $this->mailing_log = [];

        Mailer::setAdapter(new SilentMailer());
        Mailer::setDefaultSender(new AnonymousUser('Default From', 'default@from.com'));
        Mailer::onSent(
            function ($from, $to, $subject, $body, $reply_to) {
                $this->mailing_log[] = [
                    'from' => $from,
                    'to' => $to,
                    'subject' => $subject,
                    'body' => $body,
                    'reply_to' => $reply_to,
                ];
            }
        );
    }

    public function tearDown()
    {
        AngieApplication::unsetAuthentication();
        Mailer::onSent(null);

        if (DateTimeValue::isCurrentTimestampLocked()) {
            DateTimeValue::unlockCurrentTimestamp();
        }

        AngieApplication::setContainer(null);

        parent::tearDown();
    }

    protected function clearNotificationAndEmailLog(): void
    {
        foreach (['notifications', 'notification_recipients'] as $table_name) {
            DB::execute('TRUNCATE TABLE ' . $table_name);
        }

        $this->mailing_log = [];
        $this->assertEmptyNotificationAndEmailLog();
    }

    public function assertEmptyNotificationAndEmailLog(): void
    {
        $this->assertCount(0, $this->mailing_log);

        $this->assertEquals(
            0,
            DB::executeFirstCell('SELECT COUNT(id) AS "row_count" FROM notifications')
        );
        $this->assertEquals(
            0,
            DB::executeFirstCell('SELECT COUNT(id) AS "row_count" FROM notification_recipients')
        );
    }
}
