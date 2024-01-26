<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * on_expense_created event handler.
 *
 * @package ActiveCollab.modules.tracking
 * @subpackage handlers
 */

/**
 * Handle on_expense_created event.
 *
 * @param Expense $expense
 */
function tracking_handle_on_expense_created(Expense $expense)
{
    Webhooks::dispatch($expense, 'ExpenseCreated');
}
