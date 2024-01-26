<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

class CategoriesFramework extends AngieFramework
{
    const NAME = 'categories';

    protected $name = 'categories';

    public function init()
    {
        parent::init();

        DataObjectPool::registerTypeLoader(
            Category::class,
            function ($ids) {
                return Categories::findByIds($ids);
            }
        );
    }

    public function defineClasses()
    {
        AngieApplication::setForAutoload(
            [
                FwCategory::class => __DIR__ . '/models/categories/FwCategory.class.php',
                FwCategories::class => __DIR__ . '/models/categories/FwCategories.class.php',

                ICategoriesContext::class => __DIR__ . '/models/ICategoriesContext.class.php',
                ICategoriesContextImplementation::class => __DIR__ . '/models/ICategoriesContextImplementation.class.php',

                ICategory::class => __DIR__ . '/models/ICategory.class.php',
                ICategoryImplementation::class => __DIR__ . '/models/ICategoryImplementation.class.php',
            ]
        );
    }
}
