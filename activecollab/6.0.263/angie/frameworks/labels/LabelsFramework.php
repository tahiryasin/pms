<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

class LabelsFramework extends AngieFramework
{
    const NAME = 'labels';

    /**
     * Short framework name.
     *
     * @var string
     */
    protected $name = 'labels';

    public function init()
    {
        parent::init();

        DataObjectPool::registerTypeLoader(
            Label::class,
            function ($ids) {
                return Labels::findByIds($ids);
            }
        );
    }

    public function defineClasses()
    {
        AngieApplication::setForAutoload(
            [
                FwLabel::class => __DIR__ . '/models/labels/FwLabel.php',
                FwLabels::class => __DIR__ . '/models/labels/FwLabels.php',

                ILabel::class => __DIR__ . '/models/single_label/ILabel.php',
                ILabelImplementation::class => __DIR__ . '/models/single_label/ILabelImplementation.php',

                ILabels::class => __DIR__ . '/models/multiple_labels/ILabels.php',
                ILabelsImplementation::class => __DIR__ . '/models/multiple_labels/ILabelsImplementation.php',
            ]
        );
    }

    public function defineHandlers()
    {
        $this->listen('on_reset_manager_states');
    }
}
