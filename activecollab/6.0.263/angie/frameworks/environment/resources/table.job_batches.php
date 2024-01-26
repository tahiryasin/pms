<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Job batches table definition.
 *
 * @package angie.frameworks.environment
 * @subpackage resources
 */

return DB::createTable('job_batches')->addColumns([
    new DBIdColumn(),
    DBStringColumn::create('name'),
    DBIntegerColumn::create('jobs_count', 10, 0)->setUnsigned(true),
    DBDateTimeColumn::create('created_at'),
])->addIndices([
    DBIndex::create('created_at'),
]);
