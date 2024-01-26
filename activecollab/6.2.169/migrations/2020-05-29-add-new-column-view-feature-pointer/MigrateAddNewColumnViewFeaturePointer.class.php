<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Module\System\Model\FeaturePointer\NewColumnViewFeaturePointer;

class MigrateAddNewColumnViewFeaturePointer extends AngieModelMigration
{
    public function up()
    {
        $this->execute(
            'INSERT INTO feature_pointers (type, parent_id, created_on) VALUES (?, ?, ?)',
            NewColumnViewFeaturePointer::class,
            null,
            new DateTimeValue()
        );
    }
}
