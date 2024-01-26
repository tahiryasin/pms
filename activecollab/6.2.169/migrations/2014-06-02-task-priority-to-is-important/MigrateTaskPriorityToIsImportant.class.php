<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Rename priority field to is_important.
 *
 * @package activeCollab.modules.system
 * @subpackage migrations
 */
class MigrateTaskPriorityToIsImportant extends AngieModelMigration
{
    /**
     * Migrate up.
     */
    public function up()
    {
        $tasks = $this->useTableForAlter('tasks');

        $this->execute('UPDATE ' . $tasks->getName() . ' SET priority = ? WHERE priority IS NULL OR priority <= ?', false, 0);
        $this->execute('UPDATE ' . $tasks->getName() . ' SET priority = ? WHERE priority > ?', true, 0);

        $tasks->alterColumn('priority', DBBoolColumn::create('is_important'));

        $this->doneUsingTables();
    }
}
