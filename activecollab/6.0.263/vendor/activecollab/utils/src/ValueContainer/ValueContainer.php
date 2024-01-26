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
class ValueContainer implements ValueContainerInterface, WriteableValueContainerInterface
{
    /**
     * @var bool
     */
    private $value_is_set = false;

    /**
     * @var mixed
     */
    private $value;

    /**
     * {@inheritdoc}
     */
    public function hasValue()
    {
        return $this->value_is_set;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function &setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function &removeValue()
    {
        $this->value = null;
        $this->value_is_set = false;

        return $this;
    }
}
