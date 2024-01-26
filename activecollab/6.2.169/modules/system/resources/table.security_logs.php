<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Security logs table definition.
 *
 * @package angie.frameworks.authentication
 * @subpackage resources
 */

return DB::createTable('security_logs')->addColumns([
    (new DBIdColumn())
        ->setSize(DBColumn::BIG),
    DBEnumColumn::create('event', ['login_attempt', 'login', 'logout']),
    DBUserColumn::create('user', true),
    new DBIpAddressColumn('user_ip'),
    DBTextColumn::create('user_agent'),
    DBDateTimeColumn::create('created_on'),
])->addIndices([
    DBIndex::create('event'),
    DBIndex::create('created_on'),
    DBIndex::create('user_ip'),
]);
