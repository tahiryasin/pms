<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * @package ActiveCollab.migrations
 */
class MigrateAddSearchContentFieldToAttachmentsAndFilesTables extends AngieModelMigration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->useTableForAlter('attachments')->addColumn(DBTextColumn::create('search_content')->setSize(DBTextColumn::BIG), 'raw_additional_properties');
        $this->useTableForAlter('files')->addColumn(DBTextColumn::create('search_content')->setSize(DBTextColumn::BIG), 'raw_additional_properties');
    }
}
