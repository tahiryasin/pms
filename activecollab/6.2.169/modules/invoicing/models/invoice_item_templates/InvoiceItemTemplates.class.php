<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * InvoiceItemTemplates class.
 *
 * @package ActiveCollab.modules.invoicing
 * @subpackage models
 */
class InvoiceItemTemplates extends BaseInvoiceItemTemplates
{
    /**
     * Find by tax mode.
     *
     * @param  bool                  $two_taxes
     * @return InvoiceItemTemplate[]
     */
    public static function findByTaxMode($two_taxes = true)
    {
        if ($two_taxes) {
            return self::find([
                'order' => 'description ASC',
            ]);
        } else {
            return self::find([
                'conditions' => ['second_tax_rate_id < ?', 1],
                'order' => 'description ASC',
            ]);
        }
    }

    // ---------------------------------------------------
    //  Permissions
    // ---------------------------------------------------

    /**
     * Returns true if $user can create a new instance of this type.
     *
     * @param  User $user
     * @return bool
     */
    public static function canAdd(User $user)
    {
        return $user->isOwner();
    }
}
