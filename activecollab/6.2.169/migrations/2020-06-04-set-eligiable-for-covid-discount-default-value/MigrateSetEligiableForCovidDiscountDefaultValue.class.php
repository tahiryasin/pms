<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

class MigrateSetEligiableForCovidDiscountDefaultValue extends AngieModelMigration
{
    public function up()
    {
        $users_table = $this->useTableForAlter('users');

        if ($users_table->getColumn('is_eligible_for_covid_discount')) {
            $users_table->alterColumn(
                'is_eligible_for_covid_discount',
                DBBoolColumn::create(
                    'is_eligible_for_covid_discount',
                    0
                )
            );
        }

        if (!AngieApplication::isOnDemand()) {
            DB::execute('UPDATE users SET is_eligible_for_covid_discount = ?', false);
        }
    }
}
