<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Who can see this implementation.
 *
 * @package angie.frameworks.environment
 * @subpackage models
 */
trait IWhoCanSeeThisImplementation
{
    public function canUserSeeThis(User $user)
    {
        return in_array($user->getId(), $this->whoCanSeeThis());
    }

    public function whoCanSeeThis()
    {
        $result = [];

        if ($this instanceof IChild) {
            $parent = $this->getParent();

            if ($parent instanceof IChild && $parent instanceof IWhoCanSeeThis) {
                return $parent->whoCanSeeThis();
            }

            if ($parent instanceof IProjectElement) {
                $result = $this->getWhoCanSeeElement($parent);
            }

            if ($parent instanceof Project) {
                $result = $this->getWhoCanSeeElement($parent);
            }
        } elseif ($this instanceof IProjectElement) {
            $result = $this->getWhoCanSeeElement($this);
        }

        return array_unique(
            array_merge(
                Users::findOwnerIds(),
                $result
            )
        );
    }

    private function getWhoCanSeeElement($element): array
    {
        $result = [];

        if ($element instanceof IProjectElement) {
            $project = $element->getProject();
        } elseif ($element instanceof Project) {
            $project = $element;
        } else {
            $project = null;
        }

        if ($project instanceof IMembers) {
            $project_members = $project->getMembers();

            if (!empty($project_members)) {
                foreach ($project_members as $member) {
                    if ($member->getIsTrashed()) {
                        continue;
                    }

                    if (
                        $element instanceof IHiddenFromClients &&
                        $element->getIsHiddenFromClients() &&
                        $member instanceof Client
                    ) {
                        continue;
                    }

                    if (
                        ($element instanceof Task || $element instanceof Project) &&
                        $this instanceof ITrackingObject
                    ) {
                        // case where clients cannot see time records/expenses when project is disabled for client reporting
                        if ($member->isClient() && !$project->getIsClientReportingEnabled()) {
                            continue;
                        }

                        if (!($member->isClient() || $member->isOwner() || $project->isLeader($member))) {
                            if ($member->getId() !== $this->getUserId()) {
                                continue;
                            }
                        }
                    }

                    $result[] = $member->getId();
                }
            }
        }

        if ($project) {
            $leader = $project->getLeader();

            if ($leader instanceof IUser && !$leader->getIsTrashed()) {
                $result[] = $leader->getId();
            }
        }

        return $result;
    }
}
