<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

class Currency extends FwCurrency
{
    /**
     * Returns true if $user can delete this currency.
     *
     * @param  User $user
     * @return bool
     */
    public function canDelete(User $user)
    {
        return parent::canDelete($user)
            ? empty(Invoices::countByCurrency($this)) && empty(Projects::countByCurrency($this))
            : false;
    }
}
