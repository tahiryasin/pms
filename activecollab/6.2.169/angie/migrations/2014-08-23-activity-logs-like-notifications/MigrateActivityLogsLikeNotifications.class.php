<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Migrate activity logs model to be more like notifications model.
 *
 * @package angie.migrations
 */
class MigrateActivityLogsLikeNotifications extends AngieModelMigration
{
    /**
     * Migrate up.
     */
    public function up()
    {
        if ($this->tableExists('activity_logs')) {
            $this->dropTable('activity_logs');
        }

        $this->createTable(DB::createTable('activity_logs')->addColumns([
            new DBIdColumn(),
            DBTypeColumn::create('ActivityLog'),
            new DBParentColumn(),
            DBStringColumn::create('parent_path', 255, ''),
            new DBCreatedOnByColumn(true, true),
            new DBAdditionalPropertiesColumn(),
        ])->addIndices([
            DBIndex::create('parent_path', DBIndex::KEY, ['parent_path', 'parent_id']),
        ]));
    }
}
