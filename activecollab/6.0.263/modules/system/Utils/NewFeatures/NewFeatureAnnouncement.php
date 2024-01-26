<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Module\System\Utils\NewFeatures;

use DateTimeValue;
use DateValue;
use User;

class NewFeatureAnnouncement implements NewFeatureAnnouncementInterface
{
    private $title;
    private $description;
    private $date;
    private $action_title;
    private $action_url;
    private $user_visibility_callback;
    private $channel_visibility;

    public function __construct(
        string $title,
        string $description,
        DateValue $date,
        string $action_title = null,
        string $action_url = null,
        callable $user_visibility_callback = null,
        array $channel_visibility = null
    )
    {
        $this->title = $title;
        $this->description = $description;
        $this->date = $date;
        $this->action_title = $action_title;
        $this->action_url = $action_url;
        $this->user_visibility_callback = $user_visibility_callback;
        $this->channel_visibility = $channel_visibility;
    }

    public function isVisibleToUser(User $user): bool
    {
        if (empty($this->user_visibility_callback)) {
            return true;
        }

        return call_user_func($this->user_visibility_callback, $user);
    }

    public function isVisibleInChannel(string $channel): bool
    {
        if (empty($this->channel_visibility)) {
            return true;
        }

        return in_array($channel, $this->channel_visibility);
    }

    public function isVisibleOnDate(DateValue $date): bool
    {
        return $date->getTimestamp() >= $this->date->getTimestamp();
    }

    public function isSeen(?DateTimeValue $timestamp): bool
    {
        return $timestamp && $timestamp->getTimestamp() >= $this->date->getTimestamp();
    }

    public function jsonSerialize()
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'date' => $this->date->format('Y-m-d'),
            'action_title' => $this->action_title,
            'action_url' => $this->action_url,
        ];
    }
}
