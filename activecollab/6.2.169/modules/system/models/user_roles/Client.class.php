<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Client implementation.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
class Client extends User
{
    /**
     * Returns true if this user can manage company finances - receive and pay invoices, quotes etc.
     *
     * @return bool
     */
    public function canManageCompanyFinances()
    {
        return $this->getSystemPermission('can_manage_client_finances');
    }

    /**
     * Returns true if this user can request new projects.
     *
     * @return bool
     */
    public function canRequestProjects()
    {
        return $this->getSystemPermission('can_request_project');
    }

    /**
     * Return list of custom permissions that are available to this particular role.
     *
     * @return array
     */
    public function getAvailableCustomPermissions()
    {
        $custom_permissions = parent::getAvailableCustomPermissions();

        if ($this->isClient(true) && Integrations::findFirstByType(ClientPlusIntegration::class)->isInUse()) {
            $custom_permissions = array_merge($custom_permissions, [User::CAN_MANAGE_TASKS]);
        }

        return $custom_permissions;
    }
}
