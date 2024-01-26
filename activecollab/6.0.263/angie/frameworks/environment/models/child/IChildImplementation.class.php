<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Foundation\Urls\Router\Context\RoutingContextImplementation;
use ActiveCollab\Foundation\Urls\Router\Context\RoutingContextInterface;

/**
 * Child implementation.
 *
 * @package angie.frameworks.environment
 * @subpackage models
 */
trait IChildImplementation
{
    use RoutingContextImplementation;

    /**
     * @var bool
     */
    private $prevent_touch_on_next_delete = false;

    /**
     * Say hello to the parent object.
     */
    public function IChildImplementation()
    {
        $this->registerEventHandler('on_json_serialize', function (array &$result) {
            $parent = $this->getParent();

            if ($parent instanceof ApplicationObject) {
                $result['parent_type'] = get_class($parent);
                $result['parent_id'] = $parent->getId();
            } else {
                $result['parent_type'] = $result['parent_id'] = null;
            }
        });

        if ($this->touchParentOnPropertyChange()) {
            $this->registerEventHandler('on_after_save', function ($was_new, $modifications) {
                $parent = $this->getParent();

                if ($parent instanceof ApplicationObject) {
                    $touch = $was_new;

                    if (empty($touch)) {
                        foreach ($this->touchParentOnPropertyChange() as $property) {
                            if (isset($modifications[$property])) {
                                $touch = true;
                                break;
                            }
                        }
                    }

                    if ($touch) {
                        $parent->touch();
                    }
                }

                if (isset($modifications['parent_type']) || isset($modifications['parent_id'])) {
                    $old_parent_type = $this->getParentType();
                    $old_parent_id = $this->getParentId();

                    if (isset($modifications['parent_type'])) {
                        $old_parent_type = $modifications['parent_type'][0];
                    }

                    if (isset($modifications['parent_id'])) {
                        $old_parent_id = $modifications['parent_id'][0];
                    }

                    $old_parent = DataObjectPool::get($old_parent_type, $old_parent_id);

                    if ($old_parent instanceof DataObject) {
                        $old_parent->touch();
                    }
                }
            });
        }

        $this->registerEventHandler('on_after_delete', function () {
            if ($this->prevent_touch_on_next_delete) {
                $this->prevent_touch_on_next_delete = false;
            } else {
                if ($this->getParent() instanceof ApplicationObject) {
                    $this->getParent()->touch();
                }
            }
        });

        if (!$this->isParentOptional()) {
            $this->registerEventHandler('on_validate', function (ValidationErrors &$errors) {
                if (!$this->validatePresenceOf('parent_type') || !$this->validatePresenceOf('parent_id')) {
                    $errors->addError('Parent is required', 'parent');
                }
            });
        }

        if ($this instanceof IHistory) {
            $this->addHistoryFields('parent_type', 'parent_id');
        }
    }

    /**
     * Register an internal event handler.
     *
     * @param $event
     * @param $handler
     * @throws InvalidParamError
     */
    abstract protected function registerEventHandler($event, $handler);

    /**
     * {@inheritdoc}
     */
    public function &getParent()
    {
        return DataObjectPool::get($this->getParentType(), $this->getParentId());
    }

    /**
     * Return parent type.
     *
     * @return string
     */
    abstract public function getParentType();

    /**
     * Return parent ID.
     *
     * @return int
     */
    abstract public function getParentId();

    // ---------------------------------------------------
    //  Routing context implementation
    // ---------------------------------------------------

    /**
     * Return list of fields that are watched for changes.
     *
     * @return array|false
     */
    abstract public function touchParentOnPropertyChange();

    /**
     * {@inheritdoc}
     */
    public function isParentOptional()
    {
        return true;
    }

    // ---------------------------------------------------
    //  Expectations
    // ---------------------------------------------------

    /**
     * Validates presence of specific field.
     *
     * In case of string value is trimmed and compared with the empty string. In
     * case of any other type empty() function is used. If $min_value argument is
     * provided value will also need to be larger or equal to it
     * (validateMinValueOf validator is used)
     *
     * @param  string  $field     Field name
     * @param  mixed   $min_value
     * @param  Closure $modifier
     * @return bool
     */
    abstract public function validatePresenceOf($field, $min_value = null, $modifier = null);

    /**
     * {@inheritdoc}
     */
    public function setParent($parent, $save = false)
    {
        if ($parent instanceof DataObject) {
            $this->setParentType(get_class($parent));
            $this->setParentId($parent->getId());
        } elseif ($parent === null) {
            $this->setParentType(null);
            $this->setParentId(0);
        } else {
            throw new InvalidInstanceError('parent', $parent, 'DataObject');
        }

        if ($save) {
            $this->save();
        }

        return $parent;
    }

    /**
     * Set parent type.
     *
     * @param  string $value
     * @return string
     */
    abstract public function setParentType($value);

    /**
     * Set parent ID.
     *
     * @param  int $value
     * @return int
     */
    abstract public function setParentId($value);

    /**
     * Save to database.
     */
    abstract public function save();

    /**
     * {@inheritdoc}
     */
    public function isParent(ApplicationObject $parent)
    {
        if ($parent instanceof ApplicationObject) {
            return $this->getParentType() == get_class($parent) && $this->getParentId() == $parent->getId();
        } else {
            throw new InvalidInstanceError('parent', $parent, 'ApplicationObject');
        }
    }

    public function getRoutingContext(): string
    {
        return AngieApplication::cache()->getByObject($this, ['routing', 'context'], function () {
            $parent = $this->getParent();
            $type_name = $this->getBaseTypeName();

            return $parent instanceof RoutingContextInterface ? $parent->getRoutingContext() . '_' . $type_name : $type_name;
        });
    }

    public function getRoutingContextParams(): array
    {
        return AngieApplication::cache()->getByObject($this, ['routing', 'params'], function () {
            $parent = $this->getParent();
            $type_name = $this->getBaseTypeName();

            if ($parent instanceof RoutingContextInterface) {
                $params = $parent->getRoutingContextParams();

                if (empty($params)) {
                    $params = [];
                }

                $params["{$type_name}_id"] = $this->getId();
            } else {
                $params = ["{$type_name}_id" => $this->getId()];
            }

            return $params;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function preventTouchOnNextDelete()
    {
        $this->prevent_touch_on_next_delete = true;
    }
}
