<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\Inflector;

/**
 * Integration class.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
abstract class Integration extends BaseIntegration implements IntegrationInterface
{
    /**
     * Return short integration description.
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Developer did not provide a description for this integration. Bad developer!';
    }

    /**
     * Get group of this integration.
     *
     * @return string
     */
    public function getGroup()
    {
        return 'other';
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupOrder()
    {
        return null;
    }

    /**
     * Get open action name.
     *
     * @return string
     */
    public function getOpenActionName()
    {
        return lang('Configure');
    }

    /**
     * Returns true if this integration is in use.
     *
     * For non-singleton integration, this method will do a simple counter check or complex properties check (using a
     * callback return by getIsInUseAdditionalPropertiesChecker() method) to determine whether integration is in use or
     * not.
     *
     * @param  User $user
     * @return bool
     */
    public function isInUse(User $user = null)
    {
        if (!$this->isSingleton()) {
            $is_in_use_properties_checker = $this->getIsInUseAdditionalPropertiesChecker();

            if ($is_in_use_properties_checker && is_callable($is_in_use_properties_checker)) {
                if ($rows = DB::execute('SELECT `raw_additional_properties` FROM `integrations` WHERE `type` = ?', get_class($this))) {
                    foreach ($rows as $row) {
                        $properties = unserialize($row['raw_additional_properties']);

                        if ($properties && is_array($properties) && call_user_func($is_in_use_properties_checker, $properties)) {
                            return true;
                        }
                    }
                }
            } else {
                return (bool) DB::executeFirstCell('SELECT COUNT(`id`) FROM `integrations` WHERE `type` = ?', get_class($this));
            }
        }

        return false;
    }

    /**
     * Return webhooks created and owned by this integration.
     *
     * @return Webhook[]|DBResult|null
     */
    public function getWebhooks()
    {
        return Webhooks::find(
            [
                'conditions' => ['`integration_id` = ?', $this->getId()],
            ]
        );
    }

    /**
     * Returns true if this integration is provided by a third party.
     *
     * @return bool
     */
    public function isThirdParty()
    {
        return false;
    }

    /**
     * Return callable that checks additional integration attributes to determine whether it is in use or not.
     *
     * @return callable|null
     */
    protected function getIsInUseAdditionalPropertiesChecker()
    {
        return null;
    }

    /**
     * Return true if this integration is available for self-hosted packages.
     *
     * @return bool
     */
    public function isAvailableForSelfHosted()
    {
        return true;
    }

    /**
     * Return true if this integration is available for on-demand packages.
     *
     * @return bool
     */
    public function isAvailableForOnDemand()
    {
        return true;
    }

    /**
     * Set non-field value during DataManager::create() and DataManager::update() calls.
     *
     * @param string $attribute
     * @param mixed  $value
     */
    public function setAttribute($attribute, $value)
    {
        $method = 'set' . Inflector::camelize($attribute);

        if (method_exists($this, $method)) {
            $this->$method($value);
        } else {
            parent::setAttribute($attribute, $value);
        }
    }

    // ---------------------------------------------------
    //  Routing
    // ---------------------------------------------------

    /**
     * Returns true if this integration is singleton (can be only one integration of this type in the system).
     *
     * @return bool
     */
    public function isSingleton()
    {
        return false;
    }

    /**
     * Return integration short name.
     */
    public function getShortName()
    {
        $bits = explode('_', Inflector::underscore(get_class($this)));
        array_pop($bits);

        return implode('-', $bits);
    }

    public function getRoutingContext(): string
    {
        return $this->isSingleton() ? 'integration_singletons' : 'integration';
    }

    public function getRoutingContextParams(): array
    {
        return $this->isSingleton()
            ? [
                'integration_type' => $this->getShortName(),
            ]
            : [
                'integration_id' => $this->getId(),
            ];
    }

    // ---------------------------------------------------
    //  Permissions
    // ---------------------------------------------------

    /**
     * All members can access Timer settings.
     *
     * @param  User $user
     * @return bool
     */
    public function canView(User $user)
    {
        return $user->isOwner();
    }

    /**
     * Only owners can update Timer integration settings.
     *
     * @param  User $user
     * @return bool
     */
    public function canEdit(User $user)
    {
        return $user->isOwner();
    }

    /**
     * Only owners can drop Timer integration settings.
     *
     * @param  User $user
     * @return bool
     */
    public function canDelete(User $user)
    {
        return $user->isOwner();
    }

    // ---------------------------------------------------
    //  System
    // ---------------------------------------------------

    /**
     * Validate before save.
     *
     * @param ValidationErrors $errors
     */
    public function validate(ValidationErrors &$errors)
    {
        if ($this->isSingleton()) {
            if ($this->getType() == '') {
                $this->setType(get_class($this));
            }

            if (!$this->validateUniquenessOf('type')) {
                $errors->fieldValueNeedsToBeUnique('type');
            }
        }

        parent::validate($errors);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($bulk = false)
    {
        try {
            DB::beginWork('Delete integration and related webhooks @ ' . __CLASS__);

            if ($webhooks = $this->getWebhooks()) {
                foreach ($webhooks as $webhook) {
                    $webhook->delete(true);
                }
            }

            parent::delete($bulk);

            DB::commit('Integration and related webhooks deleted @ ' . __CLASS__);
        } catch (Exception $e) {
            DB::rollback('Failed to delete integration and related webhooks @ ' . __CLASS__);
            throw $e;
        }
    }
}
