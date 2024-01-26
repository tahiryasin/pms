<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Handle on_report_sections event.
 *
 * @package ActiveCollab.modules.system
 * @subpackage handlers
 */

/**
 * @param \Angie\NamedList $sections
 */
function system_handle_on_report_sections(\Angie\NamedList &$sections)
{
    $sections->add('assignments', lang('Assignments'));
    $sections->add('finances', lang('Finances'));
    $sections->add('time_and_expenses', lang('Time and Expense Reports'));
}
