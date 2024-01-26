<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\ActiveCollabJobs\Jobs\Http\SendRequest;
use ActiveCollab\JobsQueue\Jobs\Job;
use Angie\Inflector;

class Webhooks extends BaseWebhooks
{
    const ENABLED = 'enabled';
    const DISABLED = 'disabled';

    public static function create(array $attributes, bool $save = true, bool $announce = true)
    {
        if (!empty($attributes['integration_type'])) {
            $parent_integratrion = self::getParentIntegration($attributes['integration_type']);

            if ($parent_integratrion instanceof Integration) {
                $attributes['integration_id'] = $parent_integratrion->getId();
            }
        }

        if (empty($attributes['integration_id'])) {
            $attributes['integration_id'] = Integrations::findFirstByType('WebhooksIntegration')->getId();
        }

        if (empty($attributes['type'])) {
            $attributes['type'] = Webhook::class;
        }

        return parent::create($attributes, $save, $announce);
    }

    // ---------------------------------------------------
    //  Permissions
    // ---------------------------------------------------

    /**
     *  Returns true if $user can define new webhook.
     *
     * @param  User $user
     * @return bool
     */
    public static function canAdd(User $user)
    {
        return $user->isOwner();
    }

    /**
     * Returns true if $user can edit existing webhhoks.
     *
     * @param  User $user
     * @return bool
     */
    public static function canEdit(User $user)
    {
        return $user->isOwner();
    }

    /**
     * Returns true if $user can delete existing webhooks.
     *
     * @param  User $user
     * @return bool
     */
    public static function canDelete(User $user)
    {
        return $user->isOwner();
    }

    // ---------------------------------------------------
    //  Collections
    // ---------------------------------------------------

    /**
     * Returns webhooks collection.
     *
     * Expected collection names:
     *  - all (returns all webhooks)
     *  - all_enabled (returns all enabled webhooks)
     *  - webhooks_integration (returns all webhooks related to this integration)
     *  - webhooks_integration_enabled (returns all enabled webhooks related to this integration)
     *  - webhooks_integration_disabled (returns all disabled webhooks related to this integration)
     *
     * @param  string            $collection_name
     * @param  User|null         $user
     * @return ModelCollection
     * @throws InvalidParamError
     */
    public static function prepareCollection($collection_name, $user)
    {
        $collection = parent::prepareCollection($collection_name, $user);
        $conditions = [];
        if (strpos($collection_name, 'integration') !== false) {
            $integration = self::getIntegrationByCollectionName($collection_name);
            $conditions[] = DB::prepareConditions(['integration_id = ?', $integration->getId()]);
        } elseif (strpos($collection_name, DataManager::ALL) === false) {
            throw new InvalidParamError('collection_name', $collection_name);
        }

        if (str_ends_with($collection_name, self::ENABLED)) {
            $conditions[] = DB::prepareConditions(['is_enabled = ?', true]);
        } elseif (str_ends_with($collection_name, self::DISABLED)) {
            $conditions[] = DB::prepareConditions(['is_enabled =? ', false]);
        }

        // merge conditions
        if (!empty($conditions)) {
            $collection->setConditions(implode(' AND ', $conditions));
        }

        return $collection;
    }

    /**
     * Return all enabled webhooks.
     *
     * @return iterable|DBResult|Webhook[]
     */
    public static function findEnabled(): ?iterable
    {
        return self::find(
            [
                'conditions' => ['`is_enabled` = ?', true],
            ]
        );
    }

    /**
     * Return all enabled webhooks for an integration.
     *
     * @param  IntegrationInterface$integration
     * @return DBResult|Webhook[]
     */
    public static function findEnabledForIntegration(IntegrationInterface $integration): ?iterable
    {
        return self::find(
            [
                'conditions' => ['`integration_id` = ? AND `is_enabled` = ?', $integration->getId(), true],
            ]
        );
    }

    public static function countEnabledForIntegration(IntegrationInterface $integration): int
    {
        return self::count(['`integration_id` = ? AND `is_enabled` = ?', $integration->getId(), true]);
    }

    // ---------------------------------------------------
    //  Dispatcher
    // ---------------------------------------------------

    /**
     * Find all enabled webhooks and dispatch them.
     *
     * @param DataObject $object
     * @param            $event_type
     */
    public static function dispatch(DataObject $object, $event_type)
    {
        /** @var Webhook[] $webhooks */
        if ($webhooks = self::findEnabled()) {
            foreach ($webhooks as $webhook) {
                // filter event types and projects
                if (!$webhook->shouldBeDispatched($object, $event_type)) {
                    AngieApplication::log()->debug(
                        "Skipping '{event_type}' webhook dispatch to '{url} due to filters settings'. Object type: '{object_type}', object id: '{object_id}'",
                        [
                            'event_type' => $event_type,
                            'url' => $webhook->getUrl(),
                            'object_type' => $object->getModelName(),
                            'object_id' => $object->getId(),
                        ]
                    );
                    continue;
                }

                if ($payload = $webhook->getPayload($event_type, $object)) {
                    $url = $webhook->getUrl();
                    $custom_query_params = $webhook->getCustomQueryParams();

                    if (strlen($custom_query_params)) {
                        $url .= parse_url($url, PHP_URL_QUERY) ? '&' : '?';
                        $url .= $custom_query_params;
                    }

                    try {
                        AngieApplication::jobs()->dispatch(
                            new SendRequest(
                                [
                                    'priority' => Job::HAS_HIGHEST_PRIORITY,
                                    'instance_id' => $object->getId(),
                                    'url' => $url,
                                    'method' => 'POST',
                                    'headers' => $webhook->getCustomHeaders(),
                                    'payload' => json_encode($payload),
                                ]
                            ),
                            WebhooksIntegration::JOBS_QUEUE_CHANNEL
                        );
                    } catch (Exception $e) {
                        AngieApplication::log()->error(
                            "SendRequest job for send '{event_type}' webhook failed with reason: '{reason}'",
                            [
                                'reason' => $e->getMessage(),
                                'url' => $url,
                                'object_id' => $object->getId(),
                                'object_class' => get_class($object),
                                'event_type' => $event_type,
                                'payload' => $payload,
                            ]
                        );
                    }
                } else {
                    AngieApplication::log()->debug(
                        'Skipping event type {event_type} for webhook {url}, payload empty',
                        [
                            'event_type' => $event_type,
                            'url' => $webhook->getUrl(),
                        ]
                    );
                }
            }
        } else {
            AngieApplication::log()->debug(
                "Skipping '{event_type}' webhook dispatch. No enabled webhooks'",
                [
                    'event_type' => $event_type,
                ]
            );
        }
    }

    // ---------------------------------------------------
    //  Integration resolvers
    // ---------------------------------------------------

    /**
     * Return parent integration by type.
     *
     * @param $integration_type
     * @return Integration
     */
    public static function getParentIntegration($integration_type)
    {
        $integration = Integrations::findFirstByType(Inflector::camelize($integration_type), false);

        return !empty($integration) ? $integration : Integrations::findFirstByType('WebhooksIntegration');
    }

    /**
     * Return integration by collection name.
     *
     * @param $collection_name
     * @return Integration
     */
    private static function getIntegrationByCollectionName($collection_name)
    {
        $bits = explode('_', $collection_name);
        $name = $bits[0] . '_' . $bits[1];

        return self::getParentIntegration($name);
    }

    /**
     * Return an array of webhook payload transformator instances.
     *
     * @return WebhookPayloadTransformatorInterface[]|array
     */
    public static function getPayloadTransformators()
    {
        /** @var WebhookPayloadTransformatorInterface[] $available_transformators */
        $available_transformators = [];
        \Angie\Events::trigger('on_available_webhook_payload_transformators', [&$available_transformators]);

        $result = [];
        foreach ($available_transformators as $available_transformator) {
            $result[] = new $available_transformator();
        }

        return $result;
    }
}
