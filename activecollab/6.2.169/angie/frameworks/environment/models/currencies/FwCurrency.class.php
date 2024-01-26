<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\Globalization;

/**
 * Framework level currency implementation.
 *
 * @package angie.frameworks.globalization
 * @subpackage models
 */
abstract class FwCurrency extends BaseCurrency
{
    /**
     * Return properly formatted value.
     *
     * @param  float    $value
     * @param  Language $language
     * @param  bool     $with_currency_code
     * @return string
     */
    public function format($value, $language = null, $with_currency_code = false)
    {
        return Globalization::formatMoney($value, $this, $language, $with_currency_code);
    }

    /**
     * Return array or property => value pairs that describes this object.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $result = parent::jsonSerialize();

        $result['code'] = $this->getCode();
        $result['is_default'] = $this->getIsDefault();
        $result['decimal_spaces'] = $this->getDecimalSpaces();
        $result['decimal_rounding'] = $this->getDecimalRounding();

        return $result;
    }

    public function getRoutingContext(): string
    {
        return 'currency';
    }

    public function getRoutingContextParams(): array
    {
        return [
            'currency_id' => $this->getId(),
        ];
    }

    // ---------------------------------------------------
    //  Permissions
    // ---------------------------------------------------

    /**
     * Returns true if $user can see currency details.
     *
     * @param  User $user
     * @return bool
     */
    public function canView(User $user)
    {
        return $user->isOwner();
    }

    /**
     * Check if $user can edit this currency.
     *
     * @param  User $user
     * @return bool
     */
    public function canEdit(User $user)
    {
        return $user->isOwner();
    }

    /**
     * Returns true if $user can delete this currency.
     *
     * @param  User $user
     * @return bool
     */
    public function canDelete(User $user)
    {
        return $user->isOwner() && !$this->getIsDefault();
    }

    // ---------------------------------------------------
    //  System
    // ---------------------------------------------------

    /**
     * Validate before save.
     *
     * @param ValidationErrors $errors
     */
    public function validate(ValidationErrors &$errors)
    {
        if ($this->validatePresenceOf('name')) {
            if (!$this->validateUniquenessOf('name')) {
                $errors->fieldValueNeedsToBeUnique('name');
            }
        } else {
            $errors->fieldValueIsRequired('name');
        }

        if ($this->validatePresenceOf('code')) {
            if (!$this->validateUniquenessOf('code')) {
                $errors->fieldValueNeedsToBeUnique('code');
            }
        } else {
            $errors->fieldValueIsRequired('code');
        }
    }

    /**
     * Save a currency.
     */
    public function save()
    {
        $save = parent::save();

        AngieApplication::cache()->remove('currencies_id_name_map');
        AngieApplication::cache()->remove('currencies_id_details_map');

        return $save;
    }
}
