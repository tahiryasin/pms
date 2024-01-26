<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

class BudgetThresholdReachedNotification extends Notification
{
    public function &setProjectName(string $projectName): self
    {
        $this->setAdditionalProperty('projectName', $projectName);

        return $this;
    }

    public function &setProjectUrl(string $projectUrl): self
    {
        $this->setAdditionalProperty('projectUrl', $projectUrl);

        return $this;
    }

    public function &setThreshold(int $threshold): self
    {
        $this->setAdditionalProperty('threshold', $threshold);

        return $this;
    }

    public function getAdditionalTemplateVars(NotificationChannel $channel): array
    {
        if ($channel instanceof EmailNotificationChannel) {
            return [
                'projectName' => $this->getAdditionalProperty('projectName'),
                'projectUrl' => $this->getAdditionalProperty('projectUrl'),
                'threshold' => $this->getAdditionalProperty('threshold'),
            ];
        }

        return parent::getAdditionalProperties();
    }

    public function isThisNotificationVisibleInChannel(NotificationChannel $channel, IUser $recipient)
    {
        if ($channel instanceof EmailNotificationChannel) {
            return true;
        } elseif ($channel instanceof WebInterfaceNotificationChannel) {
            return false;
        }

        return parent::isThisNotificationVisibleInChannel($channel, $recipient);
    }
}
