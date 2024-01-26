<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\Authentication\AuthorizationIntegrationLocator;

use AuthorizationIntegration;
use AuthorizationIntegrationInterface;
use Integrations;
use InvalidArgumentException;
use LocalAuthorizationIntegration;
use LogicException;
use ReflectionClass;
use ShepherdAuthorizationIntegration;

class AuthorizationIntegrationLocator implements AuthorizationIntegrationLocatorInterface
{
    /**
     * @var string
     */
    private $authorizer_integration_class_name;

    /**
     * AuthorizationIntegrationLocator constructor.
     *
     * @param bool   $is_on_demand
     * @param bool   $is_in_dev_or_test
     * @param bool   $is_on_demand_next_gen
     * @param string $integration_class_name
     */
    public function __construct(
        bool $is_on_demand,
        bool $is_in_dev_or_test = false,
        bool $is_on_demand_next_gen = false,
        string $integration_class_name = ''
    )
    {
        if (!$is_on_demand && $is_on_demand_next_gen) {
            throw new LogicException('Next gen OnDemand is available only in OnDemand mode');
        }

        if ($is_in_dev_or_test) {
            $integration_class_name = LocalAuthorizationIntegration::class;
        } elseif (empty($integration_class_name)) {
            if ($is_on_demand) {
                $integration_class_name = ShepherdAuthorizationIntegration::class;
            } else {
                $integration_class_name = LocalAuthorizationIntegration::class;
            }
        }

        $this->authorizer_integration_class_name = $this->validateIntegrationClass(
            $is_on_demand,
            $is_in_dev_or_test,
            $integration_class_name
        );
    }

    /**
     * @return string
     */
    public function getAuthorizerIntegrationClassName()
    {
        return $this->authorizer_integration_class_name;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationIntegration()
    {
        return Integrations::findFirstByType($this->getAuthorizerIntegrationClassName());
    }

    /**
     * Validate integration class name.
     *
     * @param  bool   $is_on_demand
     * @param  bool   $is_in_dev_or_test
     * @param  string $integration_class_name
     * @return string
     */
    private function validateIntegrationClass($is_on_demand, $is_in_dev_or_test, $integration_class_name)
    {
        if (!class_exists($integration_class_name, true)) {
            throw new InvalidArgumentException('Authorization class does not exist');
        }

        $reflection_class = new ReflectionClass($integration_class_name);

        if (!$reflection_class->implementsInterface(AuthorizationIntegrationInterface::class)) {
            throw new InvalidArgumentException('Authorization class does not implement AuthorizationIntegrationInterface');
        }

        if (!$reflection_class->isSubclassOf(AuthorizationIntegration::class)) {
            throw new InvalidArgumentException('Authorization class does not extend AuthorizationIntegration');
        }

        if ($is_on_demand && !$is_in_dev_or_test && $integration_class_name === LocalAuthorizationIntegration::class) {
            throw new LogicException("Local authorization adapter can't be used when in On Demand mode");
        }

        return $integration_class_name;
    }
}
