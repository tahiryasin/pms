<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Foundation\Urls\Router\Context\RoutingContextInterface;

abstract class Reaction extends BaseReaction implements RoutingContextInterface
{
    public function getRoutingContext(): string
    {
        return 'reaction';
    }

    public function getRoutingContextParams(): array
    {
        return [
            'reaction_id' => $this->getId(),
        ];
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return array_merge(
            parent::jsonSerialize(),
            [
                'parent_id' => $this->getParentId(),
                'parent_type' => $this->getParentType(),
            ]
        );
    }

    public function touchParentOnPropertyChange(): ?array
    {
        return [
            'type',
        ];
    }

    public function delete($bulk = false)
    {
        try {
            DB::beginWork('Deleting reaction @ ' . __CLASS__);

            DataObjectPool::announce($this, DataObjectPool::OBJECT_DELETED);

            parent::delete($bulk);

            /** @var Comment $comment */
            $comment = $this->getParent();
            /** @var Task $task */
            $task = $comment->getParent();

            Notifications::deleteByParentAndAdditionalProperty($task, 'reaction_id', $this->getId());

            DB::commit('Reaction deleted @ ' . __CLASS__);
        } catch (Exception $e) {
            DB::rollback('Failed to delete reaction @ ' . __CLASS__);
            throw $e;
        }
    }
}
