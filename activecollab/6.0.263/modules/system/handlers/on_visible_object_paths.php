<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * on_visible_object_paths event handler.
 *
 * @package activeCollab.modules.system
 * @subpackage handlers
 */

/**
 * @param User                   $user
 * @param array                  $contexts
 * @param array                  $ignore_contexts
 * @param ApplicationObject|null $in
 */
function system_handle_on_visible_object_paths(User $user, array &$contexts, array &$ignore_contexts, &$in)
{
    if ($in instanceof Project && ($user->isOwner() || $in->isMember($user))) {
        $contexts['projects/' . $in->getId()] = true;

        if ($user instanceof Client) {
            $contexts['projects/' . $in->getId() . '/visible-to-clients/*'] = true;
        } else {
            $contexts['projects/' . $in->getId() . '/*'] = true;
        }
    } elseif (empty($in)) {
        $contexts['users/*'] = $user->getVisibleUserIds(null, STATE_TRASHED);

        if ($user->isOwner()) {
            $contexts['companies/*'] = $contexts['teams/*'] = $contexts['projects/*'] = true;
        } else {
            $contexts['companies/*'] = $user->getVisibleCompanyIds();

            if ($user->isPowerUser()) {
                $contexts['teams/*'] = true;
            }

            if ($project_ids = $user->getProjectIds()) {
                foreach ($project_ids as $project_id) {
                    $contexts["projects/{$project_id}"] = true;

                    if ($user instanceof Client) {
                        $contexts["projects/{$project_id}/visible-to-clients/*"] = true;
                    } else {
                        $contexts["projects/{$project_id}/*"] = true;
                    }
                }
            }
        }
    }
}
