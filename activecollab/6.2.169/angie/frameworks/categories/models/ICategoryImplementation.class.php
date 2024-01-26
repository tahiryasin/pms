<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Parent's category implementation.
 *
 * @package angie.frameworks.categories
 * @subpackage models
 */
trait ICategoryImplementation
{
    /**
     * Say hallo to the parent object.
     */
    public function ICategoryImplementation()
    {
        $this->registerEventHandler('on_json_serialize', function (array &$result) {
            $result['category_id'] = $this->getCategoryId();
        });

        $this->registerEventHandler('on_describe_single', function (array &$result) {
            $result['category'] = $this->getCategory();
        });

        $this->registerEventHandler('on_history_field_renderers', function (&$renderers) {
            $renderers['category_id'] = function ($old_value, $new_value, Language $language) {
                $category_ids = [];

                if ($old_value) {
                    $category_ids[] = $old_value;
                }

                if ($new_value) {
                    $category_ids[] = $new_value;
                }

                $names = Categories::getNamesByIds($category_ids);

                if ($new_value) {
                    if ($old_value) {
                        return lang('Category changed from <b>:old_value</b> to <b>:new_value</b>', ['old_value' => $names[$old_value], 'new_value' => $names[$new_value]], true, $language);
                    } else {
                        return lang('Category set to <b>:new_value</b>', ['new_value' => $names[$new_value]], true, $language);
                    }
                } elseif ($old_value) {
                    return lang('Category set to empty value', null, true, $language);
                }
            };
        });
    }

    /**
     * Return parent's category.
     *
     * @return Category|null
     */
    public function getCategory()
    {
        return $this->getCategoryId() ? DataObjectPool::get('Category', $this->getCategoryId()) : null;
    }

    /**
     * Set category.
     *
     * @param  Category             $category
     * @param  bool                 $save
     * @return Category|null
     * @throws InvalidInstanceError
     */
    public function setCategory($category, $save = false)
    {
        if ($category instanceof Category) {
            $this->setCategoryId($category->getId());
        } elseif ($category === null) {
            $this->setCategoryId(0);
        } else {
            throw new InvalidInstanceError('category', $category, 'Category');
        }

        if ($save) {
            $this->save();
        }

        return $category;
    }

    /**
     * Return category context (global by default).
     *
     * @return ICategoriesContext|null
     */
    public function getCategoryContext()
    {
        return null;
    }

    /**
     * Return category context string.
     *
     * @return string
     */
    public function getCategoryContextString()
    {
        $context = $this->getCategoryContext();
        if ($context && $context instanceof ApplicationObject) {
            if ($context->fieldExists('id')) {
                return get_class($context) . '_' . $context->getId();
            } else {
                return get_class($context);
            }
        }

        return null;
    }

    // ---------------------------------------------------
    //  Expectations
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
     * Return category ID.
     *
     * @return int
     */
    abstract public function getCategoryId();

    /**
     * Set value of category_id field.
     *
     * @param  int $value
     * @return int
     */
    abstract public function setCategoryId($value);

    /**
     * Save to database.
     */
    abstract public function save();
}
