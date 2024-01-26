<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Attachments framework model definition.
 *
 * @package angie.frameworks.attachments
 * @subpackage resources
 */
class AttachmentsFrameworkModel extends AngieFrameworkModel
{
    /**
     * Construct attachments framework model definition.
     *
     * @param AttachmentsFramework $parent
     */
    public function __construct(AttachmentsFramework $parent)
    {
        parent::__construct($parent);

        $this->addModelFromFile('attachments')
            ->setTypeFromField('type')
            ->setOrderBy('created_on');
    }
}
