<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Reusable members implementation.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
trait IMembersViaConnectionTableImplementation
{
    use IBasicMembersImplementation;

    /**
     * Say hello to the parent object.
     */
    public function IMembersViaConnectionTableImplementation()
    {
        $this->registerEventHandler('on_json_serialize', 'describeMembers');
        $this->registerEventHandler('on_before_delete', function () {
            $this->clearMembers();
        });
    }

    /**
     * Return member IDs.
     *
     * @param  bool  $use_cache
     * @return array
     */
    public function getMemberIds($use_cache = true)
    {
        return Users::getMemberIdsFor($this, function () {
            $state_filter = '';

            if (!$this->includeArchivedAndTrashedMembers()) {
                $state_filter = DB::prepare(' AND u.is_archived = ? AND u.is_trashed = ?', false, false);
            }

            return DB::executeFirstColumn("SELECT u.id AS 'id' FROM users AS u LEFT JOIN " . $this->getMembersTableName() . ' AS m ON u.id = m.user_id WHERE m.' . $this->getMembersFkName() . " = ? {$state_filter} ORDER BY u.id", $this->getId());
        }, $use_cache);
    }

    /**
     * Replace current set of members with the new set.
     *
     * @param User[]     DBResult $users
     * @param array|null          $additional
     */
    public function setMembers($users, $additional = null)
    {
        DB::transact(function () use ($users, $additional) {
            $this->untouchable(function () {
                $this->clearMembers();
            });

            if ($users && is_foreachable($users)) {
                $this->addMembers($users, $additional);
            }
        });
    }

    /**
     * Add user to this context.
     *
     * @param User[]|DBResult $users
     * @param array|null      $additional
     */
    public function addMembers($users, $additional = null)
    {
        if ($users && is_foreachable($users)) {
            DB::transact(
                function () use ($users) {
                    $batch = new DBBatchInsert(
                        $this->getMembersTableName(),
                        [
                            'user_id', $this->getMembersFkName(),
                        ],
                        50,
                        DBBatchInsert::REPLACE_RECORDS
                    );

                    foreach ($users as $user) {
                        if ($user instanceof User) {
                            $batch->insert($user->getId(), $this->getId());
                        } else {
                            throw new InvalidParamError('users', $users);
                        }
                    }

                    $batch->done();
                }
            );

            $this->touch();
        }
    }

    /**
     * Remove user from this context.
     *
     * @param  User[]|DBResult   $users
     * @param  array|null        $additional
     * @throws InvalidParamError
     */
    public function removeMembers($users, $additional = null)
    {
        if (count($users)) {
            $members_table = $this->getMembersTableName();
            $members_fk = $this->getMembersFkName();

            $user_ids = [];

            foreach ($users as $user) {
                if ($user instanceof User) {
                    $user_ids[] = $user->getId();
                } else {
                    throw new InvalidParamError('users', $users);
                }
            }

            DB::execute("DELETE FROM $members_table WHERE $members_fk = ? AND user_id IN (?)", $this->getId(), $user_ids);

            $this->touch();
        }
    }

    /**
     * Clone members from parent context to $to.
     *
     * @param ApplicationObject|IMembers $to
     * @param array|null                 $additional
     */
    public function cloneMembers(IMembers $to, $additional = null)
    {
        DB::transact(function () use ($to, $additional) {
            $batch = new DBBatchInsert($this->getMembersTableName(), [$this->getMembersFkName(), 'user_id'], 50, DBBatchInsert::REPLACE_RECORDS);

            foreach ($this->getMemberIds() as $member_id) {
                $batch->insert($to->getId(), $member_id);
            }

            $batch->done();

            $to->touch();
        }, 'Cloning project members');
    }

    /**
     * Clear all relations.
     *
     * @param array|null $additional
     */
    public function clearMembers($additional = null)
    {
        $members_table = $this->getMembersTableName();
        $members_fk = $this->getMembersFkName();

        DB::execute("DELETE FROM $members_table WHERE $members_fk = ?", $this->getId());

        $this->touch();
    }

    /**
     * Replace one user with another user.
     *
     * @param User       $replace
     * @param User       $with
     * @param array|null $additional
     */
    public function replaceMember(User $replace, User $with, $additional = null)
    {
        DB::transact(function () use ($replace, $with, $additional) {
            $this->removeMembers([$replace], $additional);
            $this->addMembers([$with], $additional);
        });

        AngieApplication::cache()->removeByObject($this, ['members']);
    }

    // ---------------------------------------------------
    //  Utility methods
    // ---------------------------------------------------

    /**
     * Cached members table name.
     *
     * @var string
     */
    private $members_table_name;

    /**
     * Return table name.
     *
     * @return string
     */
    public function getMembersTableName()
    {
        if (empty($this->members_table_name)) {
            $this->members_table_name = $this->getModelName(true, true) . '_users';
        }

        return $this->members_table_name;
    }

    /**
     * Cached members FK name.
     *
     * @var string
     */
    private $members_fk_name;

    /**
     * Return name of the FK in the connection table.
     *
     * @return string
     */
    public function getMembersFkName()
    {
        if (empty($this->members_fk_name)) {
            $this->members_fk_name = $this->getModelName(true, true) . '_id';
        }

        return $this->members_fk_name;
    }

    /**
     * Should we include or ignore archived and trashed members (TRUE for include, FALSE for ignore).
     *
     * @return bool
     */
    protected function includeArchivedAndTrashedMembers()
    {
        return true;
    }

    // ---------------------------------------------------
    //  Expectations
    // ---------------------------------------------------

    /**
     * Return parent object ID.
     */
    abstract public function getId();

    /**
     * Return name of this model.
     *
     * @param  bool   $underscore
     * @param  bool   $singular
     * @return string
     */
    abstract public function getModelName($underscore = false, $singular = false);

    /**
     * Register an internal event handler.
     *
     * @param $event
     * @param $handler
     * @throws InvalidParamError
     */
    abstract protected function registerEventHandler($event, $handler);

    /**
     * Refresh object's updated_on flag.
     *
     * @param User|null  $by
     * @param array|null $additional
     * @param bool       $save
     */
    abstract public function touch($by = null, $additional = null, $save = true);

    /**
     * Run $callback while this object is untouchable.
     *
     * @param callable $callback
     */
    abstract public function untouchable(callable $callback);
}
