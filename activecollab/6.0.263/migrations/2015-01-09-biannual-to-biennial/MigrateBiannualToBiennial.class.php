<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Class description.
 *
 * @package
 * @subpackage
 */
class MigrateBiannualToBiennial extends AngieModelMigration
{
    /**
     * Migrate up.
     */
    public function up()
    {
        $recurring_profiles = $this->useTableForAlter('recurring_profiles');

        $recurring_profiles->alterColumn('frequency', DBEnumColumn::create('frequency', ['daily', 'weekly', 'biweekly', 'monthly', 'bimonthly', 'quarterly', 'halfyearly', 'yearly', 'biannual', 'biennial'], 'monthly'));
        $this->execute('UPDATE ' . $recurring_profiles->getName() . ' SET frequency = ? WHERE frequency = ?', 'biennial', 'biannual');
        $recurring_profiles->alterColumn('frequency', DBEnumColumn::create('frequency', ['daily', 'weekly', 'biweekly', 'monthly', 'bimonthly', 'quarterly', 'halfyearly', 'yearly', 'biennial'], 'monthly'));

        $this->doneUsingTables();
    }
}
