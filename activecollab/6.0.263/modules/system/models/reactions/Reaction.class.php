<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Foundation\Urls\Router\Context\RoutingContextInterface;

/**
 * Reaction class.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
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

    /**
     * Return a list of properties that are watched.
     *
     * @return array
     */
    public function touchParentOnPropertyChange()
    {
        return ['type'];
    }

    /**
     * {@inheritdoc}
     */
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
