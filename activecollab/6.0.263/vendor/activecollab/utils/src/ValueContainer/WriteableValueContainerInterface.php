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
interface WriteableValueContainerInterface
{
    /**
     * @param  mixed $value
     * @return $this
     */
    public function &setValue($value);

    /**
     * @return $this
     */
    public function &removeValue();
}
