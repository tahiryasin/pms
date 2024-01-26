<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\Module\Tasks\Utils\ProjectTasksFilter\LabelFilter;

interface LabelFilterInterface
{
    public function getFilter(int $project_id, bool $is_for_client, string $tasks_status = 'open'): array;
}
