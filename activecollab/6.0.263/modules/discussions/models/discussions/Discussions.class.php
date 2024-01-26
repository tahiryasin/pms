<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Discussions manager class.
 *
 * @package ActiveCollab.modules.discussions
 * @subpackage models
 */
class Discussions extends BaseDiscussions
{
    use IProjectElementsImplementation;

    // Sharing context
    const SHARING_CONTEXT = 'discussion';

    /**
     * Return new collection.
     *
     * @param  string            $collection_name
     * @param  User|null         $user
     * @return ModelCollection
     * @throws InvalidParamError
     */
    public static function prepareCollection($collection_name, $user)
    {
        if (str_starts_with($collection_name, 'discussions_in_project')) {
            $bits = explode('_', $collection_name);

            $page = (int) array_pop($bits);
            array_pop($bits); // Remove _page_

            $project = DataObjectPool::get('Project', array_pop($bits));

            if ($project instanceof Project) {
                $collection = new ProjectDiscussionsCollection($collection_name, $user);
                $collection->setPagination($page, 30);

                $collection->setWhosAsking($user);
                $collection->setProject($project);

                return $collection;
            } else {
                throw new InvalidParamError('collection_name', $collection_name, 'Invalid collection name');
            }
        } else {
            throw new InvalidParamError('collection_name', $collection_name, 'Invalid collection name');
        }
    }

    public static function create(array $attributes, bool $save = true, bool $announce = true)
    {
        $notify_subscribers = array_var($attributes, 'notify_subscribers', true, true);

        $discussion = parent::create($attributes, $save, false);

        if ($discussion instanceof Discussion && $discussion->isLoaded()) {
            /** @var Discussion $discussion */
            $discussion = self::autoSubscribeProjectLeader($discussion);

            if ($notify_subscribers) {
                AngieApplication::notifications()
                    ->notifyAbout('discussions/new_discussion', $discussion, $discussion->getCreatedBy())
                    ->sendToSubscribers();
            }
        }

        return DataObjectPool::announce($discussion, DataObjectPool::OBJECT_CREATED, $attributes);
    }

    /**
     * @param  Discussion $discussion
     * @param  User       $by
     * @return Task
     */
    public static function promoteToTask(Discussion $discussion, User $by)
    {
        // users who can't add tasks to a project certainly can not promote discussions to tasks
        if (!Tasks::canAdd($by, $discussion->getProject())) {
            throw new LogicException('The user is not allowed to create tasks in this project');
        }

        // client plus member can add tasks but they cannot promote other members discussions to tasks
        if ($by instanceof Client && $discussion->getCreatedById() != $by->getId()) {
            throw new LogicException("The user is not allowed to promote other member's discussions to task");
        }

        $task = null;

        DB::transact(function () use ($discussion, $by, &$task) {
            if (DB::executeFirstColumn('SELECT id FROM tasks WHERE created_from_discussion_id = ?', $discussion->getId())) {
                throw new LogicException('This Discussion is already promoted to Task.');
            }

            $task_list = TaskLists::getFirstTaskList($discussion->getProject());

            $task = Tasks::create([
                'project_id' => $discussion->getProjectId(),
                'task_list_id' => $task_list->getId(),
                'name' => $discussion->getName(),
                'body' => $discussion->getBody(),
                'created_on' => $discussion->getCreatedOn(),
                'created_by_id' => $discussion->getCreatedById(),
                'updated_on' => $discussion->getUpdatedOn(),
                'updated_by_id' => $discussion->getUpdatedById(),
                'is_hidden_from_clients' => $discussion->getIsHiddenFromClients(),
            ]);

            // Update tasks table with discussion relationship id
            DB::execute('UPDATE tasks SET created_from_discussion_id = ? WHERE id = ?', $discussion->getId(), $task->getId());

            $task->clearSubscribers(); // Clear default subscribers - we'll move everything from a discussion and make sure that $by is subscribed later on

            // Remove all discussion notifications
            if ($notification_ids = DB::executeFirstColumn('SELECT id FROM notifications WHERE parent_type = ? AND parent_id = ?', 'Discussion', $discussion->getId())) {
                DB::execute('DELETE FROM notifications WHERE id IN (?)', $notification_ids);
                DB::execute('DELETE FROM notification_recipients WHERE notification_id IN (?)', $notification_ids);

                Notifications::clearCacheFor($notification_ids);
            }

            // Remove discussion creation log, that's the only log that we will not need (we need info about comments)
            DB::execute('DELETE FROM activity_logs WHERE type = ? AND parent_type = ? AND parent_id = ?', 'InstanceCreatedActivityLog', 'Discussion', $discussion->getId());

            // Move comments, attachments and activity logs to the new parent
            foreach (['activity_logs', 'attachments', 'comments', 'subscriptions'] as $table_name) {
                if ($ids = DB::executeFirstColumn('SELECT id FROM ' . $table_name . ' WHERE parent_type = ? AND parent_id = ?', 'Discussion', $discussion->getId())) {
                    DB::execute('UPDATE ' . $table_name . ' SET parent_type = ?, parent_id = ? WHERE id IN (?)', 'Task', $task->getId(), $ids);

                    call_user_func([\Angie\Inflector::camelize($table_name), 'clearCacheFor'], $ids);
                }
            }

            // Make sure that object path for activity logs is properly updated to task's path
            DB::execute('UPDATE activity_logs SET parent_path = ? WHERE parent_type = ? AND parent_id = ?', $task->getObjectPath(), 'Task', $task->getId());

            $task->subscribe($by);

            $discussion->delete();
        });

        return $task;
    }

    // ---------------------------------------------------
    //  Permissions
    // ---------------------------------------------------

    /**
     * Returns true if $user can add discussions to $project.
     *
     * @param  User    $user
     * @param  Project $project
     * @return bool
     */
    public static function canAdd(User $user, Project $project)
    {
        return $user instanceof User && ($user->isOwner() || $project->isMember($user));
    }

    // ---------------------------------------------------
    //  Utilities
    // ---------------------------------------------------

    /**
     * Return read status for discussions in a project.
     *
     * @param  User    $user
     * @param  Project $project
     * @return array
     */
    public static function getReadStatusInProject(User $user, Project $project)
    {
        $result = [];

        if ($user instanceof Client) {
            $conditions = ['project_id = ? AND is_hidden_from_clients = ? AND is_trashed = ?', $project->getId(), false, false];
        } else {
            $conditions = ['project_id = ? AND is_trashed = ?', $project->getId(), false];
        }

        /** @var Discussion[] $discussions */
        if ($discussions = self::find(['conditions' => $conditions])) {
            foreach ($discussions as $discussion) {
                $result[$discussion->getId()] = $discussion->isRead($user);
            }
        }

        return $result;
    }

    public static function whatIsWorthRemembering(): array
    {
        return [
            'project_id',
            'name',
            'body',
            'is_trashed',
        ];
    }
}
