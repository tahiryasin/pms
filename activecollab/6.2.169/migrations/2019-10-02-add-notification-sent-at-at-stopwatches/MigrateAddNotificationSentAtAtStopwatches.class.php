<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

class MigrateAddNotificationSentAtAtStopwatches extends AngieModelMigration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        if ($this->tableExists('stopwatches')) {
            $stopwatches = $this->useTableForAlter('stopwatches');

            if (!$stopwatches->getColumn('notification_sent_at')) {
                $stopwatches->addColumn(DBDateTimeColumn::create(
                    'notification_sent_at',
                    null)
                );
            }

            $this->doneUsingTables();
        }
    }
}
