<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Reset logs for feather.
 *
 * @package angie.migrations
 */
class MigrateResetLogsForFeather extends AngieModelMigration
{
    /**
     * Migreate up.
     */
    public function up()
    {
        $this->resetAccessLogs();
        $this->resetActivityLogs();
        $this->resetMailingLog();
        $this->resetModificationLog();
        $this->resetNotifications();
        $this->resetObjectContexts();

        $this->doneUsingTables();
    }

    /**
     * Reset access logs.
     */
    private function resetAccessLogs()
    {
        foreach ($this->useTables('access_logs') as $table) {
            $this->execute("TRUNCATE TABLE $table");
        }

        $this->dropTable('access_logs_archive');
    }

    /**
     * Reset activity logs.
     */
    private function resetActivityLogs()
    {
        $this->dropTable('activity_logs');
    }

    /**
     * Reset mailing logs and queue.
     */
    private function resetMailingLog()
    {
        foreach ($this->useTables('mailing_activity_logs', 'outgoing_messages') as $table) {
            $this->execute("TRUNCATE TABLE $table");
        }

        $this->execute('DELETE FROM ' . $this->useTables('attachments')[0] . " WHERE parent_type = 'OutgoingMessage'");
    }

    /**
     * Reset modificaiton logs.
     */
    private function resetModificationLog()
    {
        $logs = $this->useTableForAlter('modification_logs');
        $values = $this->useTableForAlter('modification_log_values');

        $this->execute('TRUNCATE TABLE ' . $logs->getName());
        $this->execute('TRUNCATE TABLE ' . $values->getName());

        $logs->dropColumn('is_first');

        $values->dropColumn('value');
        $values->addColumn(DBTextColumn::create('old_value')->setSize(DBColumn::BIG), 'field');
        $values->addColumn(DBTextColumn::create('new_value')->setSize(DBColumn::BIG), 'old_value');
    }

    /**
     * Reset web interface notifications.
     */
    private function resetNotifications()
    {
        foreach ($this->useTables('notifications', 'notification_recipients') as $table) {
            $this->execute("TRUNCATE TABLE $table");
        }
    }

    /**
     * Reset object contexts.
     */
    private function resetObjectContexts()
    {
        $this->dropTable('object_contexts');
    }
}
