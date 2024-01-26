<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Categories framework model definition.
 *
 * @package angie.frameworks.categories
 * @subpackage resources
 */
class CategoriesFrameworkModel extends AngieFrameworkModel
{
    /**
     * Construct categories framework model definition.
     *
     * @param CategoriesFramework $parent
     */
    public function __construct(CategoriesFramework $parent)
    {
        parent::__construct($parent);

        $this->addModel(DB::createTable('categories')->addColumns([
            new DBIdColumn(),
            DBTypeColumn::create('Category'),
            new DBParentColumn(),
            DBNameColumn::create(100),
            new DBCreatedOnByColumn(),
            new DBUpdatedOnColumn(),
        ])->addIndices([
            DBIndex::create('name', DBIndex::UNIQUE, ['parent_type', 'parent_id', 'type', 'name']),
        ]))->setTypeFromField('type')
            ->setOrderBy('name')
            ->setObjectIsAbstract(true)
            ->addModelTrait(null, 'IResetInitialSettingsTimestamp');
    }
}
