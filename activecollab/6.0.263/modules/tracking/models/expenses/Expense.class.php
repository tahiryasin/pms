<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Foundation\Urls\Router\Context\RoutingContextInterface;
use Angie\Globalization;

/**
 * Expense class.
 *
 * @package activeCollab.modules.tracking
 * @subpackage models
 */
class Expense extends BaseExpense implements RoutingContextInterface
{
    /**
     * Construct data object and if $id is present load.
     *
     * @param mixed $id
     */
    public function __construct($id = null)
    {
        parent::__construct($id);

        $this->addHistoryFields('category_id');
    }

    /**
     * Return true if parent is optional.
     *
     * @return bool
     */
    public function isParentOptional()
    {
        return false;
    }

    /**
     * Return name string.
     *
     * @param  bool   $detailed
     * @param  bool   $in_category
     * @return string
     */
    public function getName($detailed = false, $in_category = false)
    {
        if ($detailed) {
            $user = $this->getUser();
            $value = $this->getFormatedValue();

            if ($in_category) {
                return lang(':value in :category', ['value' => $value, 'category' => $this->getCategoryName()]);
            } else {
                if ($user instanceof IUser) {
                    return lang(':value by :name', ['value' => $value, 'name' => $user->getDisplayName(true)]);
                } else {
                    return $value;
                }
            }
        } else {
            return Globalization::formatMoney($this->getValue(), $this->getCurrency(), null, true);
        }
    }

    /**
     * Return value formated with currency.
     *
     * @return float
     */
    public function getFormatedValue()
    {
        return Globalization::formatMoney($this->getValue(), $this->getCurrency());
    }

    /**
     * Return Currency.
     *
     * @return Currency
     */
    public function getCurrency()
    {
        return $this->getProject() instanceof Project && $this->getProject()->getCurrency() instanceof Currency ? $this->getProject()->getCurrency() : null;
    }

    /**
     * Return expense category name.
     *
     * @return string
     */
    public function getCategoryName()
    {
        return ExpenseCategories::getNameById($this->getCategoryId());
    }

    /**
     * Return expense category.
     *
     * @return ExpenseCategory
     */
    public function getCategory()
    {
        return DataObjectPool::get('ExpenseCategory', $this->getCategoryId());
    }

    /**
     * Set expense category.
     *
     * @param  ExpenseCategory $category
     * @return ExpenseCategory
     */
    public function setCategory(ExpenseCategory $category)
    {
        $this->setCategoryId($category->getId());

        return $category;
    }

    /**
     * Return array or property => value pairs that describes this object.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $result = parent::jsonSerialize();

        $result['category_id'] = $this->getCategoryId();
        $result['currency_id'] = $this->getProject() instanceof Project && $this->getProject()->getCurrencyId()
            ? $this->getProject()->getCurrencyId()
            : Currencies::getDefaultId();
        $result['user_name'] = $this->getUserName();
        $result['user_email'] = $this->getUserEmail();

        return $result;
    }

    public function getRoutingContext(): string
    {
        return 'expense';
    }

    public function getRoutingContextParams(): array
    {
        $parent = $this->getParent();

        if ($parent instanceof Task) {
            $project = $parent->getProject();
        } else {
            $project = $parent;
        }

        return [
            'project_id' => $project->getId(),
            'expense_id' => $this->getId(),
        ];
    }

    /**
     * Return true if $user can delete this record.
     *
     * @param  User $user
     * @return bool
     */
    public function canDelete(User $user)
    {
        return $this->canEdit($user);
    }
}
