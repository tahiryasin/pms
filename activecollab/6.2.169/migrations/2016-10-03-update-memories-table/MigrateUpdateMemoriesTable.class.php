<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * @package ActiveCollab.migrations
 */
class MigrateUpdateMemoriesTable extends AngieModelMigration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        if ($this->tableExists('memories')) {
            $memories = $this->useTableForAlter('memories');
            $memories->alterColumn('value', DBTextColumn::create('value')->setSize(DBTextColumn::MEDIUM));
            $this->doneUsingTables();
        }
    }
}
