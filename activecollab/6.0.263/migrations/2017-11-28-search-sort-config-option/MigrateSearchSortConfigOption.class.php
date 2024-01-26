<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * @package ActiveCollab.migrations
 */
class MigrateSearchSortConfigOption extends AngieModelMigration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $config_option_name = 'search_sort_preference';
        $config_options_values = $this->useTables('config_option_values')[0];

        $this->addConfigOption($config_option_name, 'score'); // default option value

        $users = DB::execute('SELECT * FROM users');

        $options_batch = new DBBatchInsert(
            $config_options_values,
            ['name',  'parent_type', 'parent_id', 'value'],
            500,
            DBBatchInsert::REPLACE_RECORDS
        );

        foreach ($users as $user) {
            $options_batch->insert(
                $config_option_name,
                User::class,
                $user['id'],
                serialize('date')
            );
        }

        $options_batch->done();
    }
}
