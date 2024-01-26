<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * @package ActiveCollab.migrations
 */
class MigrateAddReactionsTable extends AngieModelMigration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        if (!$this->tableExists('reactions')) {
            $this->createTable(
                DB::createTable('reactions')
                    ->addColumns(
                        [
                            new DBIdColumn(),
                            new DBTypeColumn(),
                            new DBParentColumn(),
                            new DBCreatedOnByColumn(true, true),
                        ]
                    )
            );
        }
    }
}
