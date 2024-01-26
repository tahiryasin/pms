<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Updated on implementation.
 *
 * @package angie.framework.environment
 * @subpackage models
 */
trait IUpdatedOnImplementation
{
    /**
     * Say hello to the paret object.
     */
    public function IUpdatedOnImplementation()
    {
        $this->registerEventHandler('on_json_serialize', function (array &$result) {
            $result['updated_on'] = $this->getUpdatedOn();
        });

        $this->registerEventHandler('on_before_save', function ($is_new, $modifications) {
            if (empty($modifications['updated_on'])) {
                $this->setUpdatedOn(new DateTimeValue());
            }
        });
    }

    // ---------------------------------------------------
    //  Expectatons
    // ---------------------------------------------------

    /**
     * Register an internal event handler.
     *
     * @param $event
     * @param $handler
     * @throws InvalidParamError
     */
    abstract protected function registerEventHandler($event, $handler);

    /**
     * Return value of updated_on field.
     *
     * @return DateTimeValue
     */
    abstract public function getUpdatedOn();

    /**
     * Set value of updated_on field.
     *
     * @param  DateTimeValue $value
     * @return DateTimeValue
     */
    abstract public function setUpdatedOn($value);
}
