<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Foundation\Events\DataObjectLifeCycleEvent\DataObjectLifeCycleEventInterface;
use ActiveCollab\Foundation\Events\WebhookEvent\WebhookEventInterface;

/**
 * Webhook class.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
class Webhook extends BaseWebhook
{
    /**
     * Serialize.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'url' => $this->getUrl(),
            'secret' => $this->getSecret(),
            'is_enabled' => $this->getIsEnabled(),
            'projects' => $this->getProjects(),
        ];
    }

    /**
     * Return projects from ids in filter.
     *
     * @return array
     */
    public function getProjects()
    {
        $result = [];

        if (!empty($this->getFilterProjects())) {
            if ($project_id_name_map = Projects::getIdNameMap($this->getFilterProjects())) {
                foreach ($project_id_name_map as $id => $name) {
                    $result[] = ['id' => $id, 'name' => $name];
                }
            }
        }

        return $result;
    }

    /**
     * Validate before save.
     *
     * @param ValidationErrors $errors
     */
    public function validate(ValidationErrors &$errors)
    {
        if (!$this->validateUniquenessOf('is_enabled', 'url')) {
            $errors->addError('Target URL already in use in another webhook. Please enter a different URL.');
        }
    }

    /**
     * Return formatted payload for webhook.
     *
     * @param  string     $event_type
     * @param  DataObject $object
     * @return array|null
     */
    public function getPayload(string $event_type, DataObject $object): ?array
    {
        /** @var WebhookPayloadTransformatorInterface $transformator */
        foreach (Webhooks::getPayloadTransformators() as $transformator) {
            if ($transformator->shouldTransform($this->getUrl())) {
                $payload = $transformator->transform($event_type, $object);

                return $payload;
            }
        }

        return [
            'payload' => $object->jsonSerialize(),
            'timestamp' => AngieApplication::currentTimestamp()->getCurrentTimestamp(),
            'type' => $event_type,
            'instance_id' => AngieApplication::getAccountId(),
        ];
    }

    /**
     * Retrieve a list of event types filters for a webhook.
     *
     * @return array
     */
    public function getFilterEventTypes()
    {
        if ($types = parent::getFilterEventTypes()) {
            return explode(',', $types);
        }

        return [];
    }

    /**
     * Set an array of event type filters.
     *
     * @param  array        $value
     * @return mixed|string
     */
    public function setFilterEventTypes($value)
    {
        return $this->setFieldValue('filter_event_types', implode(',', array_filter($value)));
    }

    /**
     * Retrieve a list of project ids to be filtered for a webhook.
     *
     * @return array
     */
    public function getFilterProjects()
    {
        if ($project_ids = parent::getFilterProjects()) {
            return array_map(function ($project_id) {
                return (int) $project_id;
            }, explode(',', $project_ids));
        }

        return [];
    }

    /**
     * Set an array of project ids filters.
     *
     * @param  array $value
     * @return mixed
     */
    public function setFilterProjects($value)
    {
        return $this->setFieldValue('filter_projects', implode(',', array_filter($value)));
    }

    public function filterEvent(WebhookEventInterface $webhook_event): bool
    {
        if ($webhook_event instanceof DataObjectLifeCycleEventInterface) {
            return $this->shouldBeDispatched($webhook_event->getObject(), $webhook_event->getWebhookEventType());
        }

        return false;
    }

    /**
     * Return true if this webhook should be dispatched.
     *
     * @param  DataObject $object
     * @param  string     $event_type
     * @return bool
     */
    public function shouldBeDispatched($object, $event_type)
    {
        return $this->shouldBeDispatchedForObject($object) && $this->shouldBeDispatchedForEvent($event_type);
    }

    /**
     * Return true if no projects are filtered or if the $object is a project related element (e.g. a task) or it's parent
     * is a project related element (e.g. comment) and the project id is among filtered projects.
     *
     * @param  DataObject $object
     * @return bool
     */
    private function shouldBeDispatchedForObject($object)
    {
        if ($object instanceof Project && empty($this->getFilterProjects())) {
            return true; // no project filtering for a newly created project if all projects option is selected
        }

        $filter_projects = false;
        $context = $object instanceof IChild ? $object->getParent() : $object;

        if (empty($this->getFilterProjects())) {
            $filter_projects = true;
        } elseif ($context instanceof Project) {
            if (in_array($context->getId(), $this->getFilterProjects())) {
                $filter_projects = true;
            }
        } elseif ($context instanceof IProjectElement) {
            if (in_array($context->getProjectId(), $this->getFilterProjects())) {
                $filter_projects = true;
            }
        } else {
            $filter_projects = empty($this->getFilterProjects()); // Allow contexts that are not a project or project related element (such as user) if all projects option is selected
        }

        return $filter_projects;
    }

    /**
     * Return true if no filters defined or if event type is among filters defined.
     *
     * @param  string $event_type
     * @return bool
     */
    private function shouldBeDispatchedForEvent($event_type)
    {
        return empty($this->getFilterEventTypes()) || (in_array($event_type, $this->getFilterEventTypes()));
    }

    public function getCustomQueryParams(WebhookEventInterface $webhook_event = null): string
    {
        return '';
    }

    public function getCustomHeaders(WebhookEventInterface $webhook_event = null): array
    {
        return [
            'X-Angie-WebhookSecret' => $this->getSecret(),
            'Content-Type' => 'application/json',
        ];
    }
}
