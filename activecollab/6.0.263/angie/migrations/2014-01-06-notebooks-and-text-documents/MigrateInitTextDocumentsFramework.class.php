<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Initialize text document framework.
 *
 * @package angie.migrations
 */
class MigrateInitTextDocumentsFramework extends AngieModelMigration
{
    /**
     * Migrate up.
     */
    public function up()
    {
        $this->createTable(DB::createTable('text_documents')->addColumns([
            new DBIdColumn(),
            DBTypeColumn::create('TextDocument'),
            new DBParentColumn(),
            DBRelatedObjectColumn::create('legacy_parent', true),
            DBNameColumn::create(255),
            DBBodyColumn::create(),
            DBStateColumn::create(),
            DBIntegerColumn::create('visibility', 3, 0)->setUnsigned(true)->setSize(DBColumn::TINY),
            DBIntegerColumn::create('original_visibility', 3)->setUnsigned(true)->setSize(DBColumn::TINY),
            DBActionOnByColumn::create('created', true, true), // Legacy, maybe old version does not have DBCreatedOnByColumn
            DBActionOnByColumn::create('updated'), // Legacy, maybe old version does not have DBCreatedOnByColumn
            DBActionOnByColumn::create('last_version', true),
            DBIntegerColumn::create('version', DBIntegerColumn::NORMAL, 1)->setUnsigned(true),
            DBIntegerColumn::create('position', DBIntegerColumn::NORMAL, 0)->setUnsigned(true),
            new DBAdditionalPropertiesColumn(),
        ])->addIndices([
            DBIndex::create('position'),
        ]));

        if (!$this->tableExists('text_document_versions')) {
            $this->createTable(DB::createTable('text_document_versions')->addColumns([
                new DBIdColumn(),
                DBIntegerColumn::create('text_document_id', 10, 0)->setUnsigned(true),
                DBIntegerColumn::create('version_num', 5, 0)->setUnsigned(true),
                DBNameColumn::create(255),
                DBBodyColumn::create(),
                DBActionOnByColumn::create('created'),
            ])->addIndices([
                DBIndex::create('text_document_version', DBIndex::KEY, ['text_document_id', 'version_num']),
            ]));
        }

        if ($this->isModuleInstalled('files') || $this->isModuleInstalled('notebooks')) {
            $this->setConfigOptionValue('project_tabs', function (&$project_tabs) {
                if (!is_array($project_tabs)) {
                    $project_tabs = [];
                }

                $project_tabs[] = 'text_documents';

                $notebooks_key = array_search('notebooks', $project_tabs);

                if ($notebooks_key) {
                    unset($project_tabs[$notebooks_key]);
                }
            });
        }
    }
}
