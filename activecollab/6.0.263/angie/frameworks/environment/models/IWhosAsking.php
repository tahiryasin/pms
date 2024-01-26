<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Composite collection.
 *
 * @package angie.frameworks.environment
 * @subpackage models
 */
trait IWhosAsking
{
    /**
     * @var User
     */
    private $whos_asking;

    /**
     * Return who's asking instance.
     *
     * @return User
     */
    public function &getWhosAsking()
    {
        return $this->whos_asking;
    }

    /**
     * Set who's asking for the collection data.
     *
     * @param  User                 $whos_asking
     * @return $this
     * @throws InvalidInstanceError
     */
    public function &setWhosAsking(User $whos_asking)
    {
        if ($whos_asking instanceof User) {
            $this->whos_asking = $whos_asking;
        } else {
            throw new InvalidInstanceError('whos_asking', $whos_asking, 'User');
        }

        return $this;
    }
}
