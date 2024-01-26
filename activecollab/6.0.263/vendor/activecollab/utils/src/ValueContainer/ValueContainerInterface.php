<?php

/*
 * This file is part of the Active Collab Utils project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\ValueContainer;

/**
 * @package ActiveCollab\ValueContainer
 */
interface ValueContainerInterface
{
    /**
     * @return bool
     */
    public function hasValue();

    /**
     * @return mixed
     */
    public function getValue();
}
