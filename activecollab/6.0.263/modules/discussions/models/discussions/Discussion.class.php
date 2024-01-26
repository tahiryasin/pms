<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

use ActiveCollab\Foundation\History\Renderers\IsHiddenFromClientsHistoryFieldRenderer;
use Angie\Search\SearchDocument\SearchDocumentInterface;

class Discussion extends BaseDiscussion
{
    public function getRoutingContext(): string
    {
        return 'discussion';
    }

    public function getRoutingContextParams(): array
    {
        return [
            'project_id' => $this->getProjectId(),
            'discussion_id' => $this->getId(),
        ];
    }

    public function getHistoryFieldRenderers()
    {
        $renderers = parent::getHistoryFieldRenderers();

        $renderers['is_hidden_from_clients'] = new IsHiddenFromClientsHistoryFieldRenderer();

        return $renderers;
    }

    public function getSearchDocument(): SearchDocumentInterface
    {
        return new ProjectElementSearchDocument($this);
    }

    public function validate(ValidationErrors &$errors)
    {
        if (!$this->validatePresenceOf('name')) {
            $errors->addError('Summary is required', 'name');
        }

        parent::validate($errors);
    }

    public function canMoveToProject(User $user, Project $target_project)
    {
        $can_move = parent::canMoveToProject($user, $target_project);

        if ($user->isPowerClient(true)) {
            return $can_move && $this->isCreatedBy($user);
        } elseif ($user->isClient()) {
            return false;
        } else {
            return $can_move;
        }
    }

    public function canCopyToProject(User $user, Project $target_project)
    {
        $can_copy = parent::canCopyToProject($user, $target_project);

        if ($user->isPowerClient(true)) {
            return $can_copy && $this->isCreatedBy($user);
        } elseif ($user->isClient()) {
            return false;
        } else {
            return $can_copy;
        }
    }
}
