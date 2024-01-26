<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\Globalization;

/**
 * TaxRate class.
 *
 * @package ActiveCollab.modules.invoicing
 * @subpackage models
 */
class TaxRate extends BaseTaxRate
{
    /**
     * Return array or property => value pairs that describes this object.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $result = parent::jsonSerialize();

        $result['percentage'] = $this->getPercentage();
        $result['is_default'] = $this->getIsDefault();
        $result['is_used'] = $this->isUsed();

        return $result;
    }

    /**
     * Return true if this particular record is used in external resources (invoice for example).
     *
     * @return bool
     */
    public function isUsed()
    {
        return InvoiceItems::countByTaxRate($this) > 0;
    }

    public function getRoutingContext(): string
    {
        return 'tax_rate';
    }

    public function getRoutingContextParams(): array
    {
        return [
            'tax_rate_id' => $this->getId(),
        ];
    }

    /**
     * Return verbose percentage.
     *
     * @return string
     */
    public function getVerbosePercentage()
    {
        return Globalization::formatNumber($this->getPercentage()) . '%';
    }

    // ---------------------------------------------------
    //  Permissions
    // ---------------------------------------------------

    /**
     * Return true if $user can see this rate.
     *
     * @param  User $user
     * @return bool
     */
    public function canView(User $user)
    {
        return $user->isFinancialManager();
    }

    /**
     * Returns true if $user can update this rate.
     *
     * @param  User $user
     * @return bool
     */
    public function canEdit(User $user)
    {
        return $user->isFinancialManager();
    }

    /**
     * Returns true if $user can delete this tax rate.
     *
     * @param  User $user
     * @return bool
     */
    public function canDelete(User $user)
    {
        return $user->isFinancialManager() && !$this->getIsDefault() && !$this->isUsed();
    }

    // ---------------------------------------------------
    //  System
    // ---------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function validate(ValidationErrors &$errors)
    {
        if ($this->validatePresenceOf('name')) {
            if (!$this->validateUniquenessOf('name', 'percentage')) {
                $errors->fieldValueNeedsToBeUnique('name');
            }
        } else {
            $errors->fieldValueIsRequired('name');
        }

        if ($this->validatePresenceOf('percentage')
            && !$this->validateValueInRange('percentage', -99.999, 99.999)
        ) {
             $errors->addError('Percentage can be from -99.999 to 99.999', 'percentage');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        if ($this->isModifiedField('percentage') && $this->isUsed()) {
            // Override save method so we cannot change tax rate percentage if tax rate is used.
            $this->revertField('percentage');
        }

        parent::save();
    }
}
