<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * @package ActiveCollab.modules.system
 * @subpackage model
 */
interface ProjectExportInterface
{
    const EXPORT_ROUTINE_VERSION = '2.0';

    /**
     * Export project to machine readble format.
     *
     * @param  bool   $delete_work_folder
     * @return string
     */
    public function export($delete_work_folder = true);
}
