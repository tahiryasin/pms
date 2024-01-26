<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Application level owner role implementation.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
class Owner extends Member
{
    /**
     * Return array of visible user ID-s.
     *
     * @param  bool  $use_cache
     * @return array
     */
    public function getVisibleCompanyIds($use_cache = true)
    {
        return AngieApplication::cache()->getByObject($this, ['visible_companies'], function () use ($use_cache) {
            return DB::executeFirstColumn('SELECT id FROM companies ORDER BY id');
        }, empty($use_cache));
    }
}
