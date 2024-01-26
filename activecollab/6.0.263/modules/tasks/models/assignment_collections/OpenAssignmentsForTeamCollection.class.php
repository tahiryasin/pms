<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Open assignments for team collection.
 *
 * @package ActiveCollab.modules.tasks
 * @subpackage models
 */
class OpenAssignmentsForTeamCollection extends AssignmentsCollection
{
    /**
     * @var Team
     */
    private $team;
    /**
     * @var ModelCollection
     */
    private $tasks_collection;
    private $subtasks_collection;

    /**
     * Return team instance.
     *
     * @return Team
     */
    public function &getTeam()
    {
        return $this->team;
    }

    /**
     * Set assignee.
     *
     * @param  Team              $team
     * @return $this
     * @throws InvalidParamError
     */
    public function &setTeam(Team $team)
    {
        if ($team instanceof Team) {
            $this->team = $team;
        } else {
            throw new InvalidParamError('team', $team, 'Team');
        }

        return $this;
    }

    /**
     * Return user or team timestamp.
     *
     * @return string
     */
    public function getContextTimestamp()
    {
        return $this->team->getUpdatedOn()->toMySQL();
    }

    /**
     * Return model name.
     *
     * @return string
     */
    public function getModelName()
    {
        return 'Teams';
    }

    /**
     * Return assigned tasks collection.
     *
     * @return ModelCollection
     * @throws ImpossibleCollectionError
     */
    protected function &getTasksCollections()
    {
        if (empty($this->tasks_collection)) {
            if ($this->team instanceof Team && $this->getWhosAsking() instanceof User) {
                $this->tasks_collection = Tasks::prepareCollection('open_tasks_assigned_to_team_' . $this->team->getId(), $this->getWhosAsking());
            } else {
                throw new ImpossibleCollectionError("Invalid user and/or who's asking instance");
            }
        }

        return $this->tasks_collection;
    }

    /**
     * Return assigned subtasks collection.
     *
     * @return ModelCollection
     * @throws ImpossibleCollectionError
     */
    protected function &getSubtasksCollection()
    {
        if (empty($this->subtasks_collection)) {
            if ($this->team instanceof Team && $this->getWhosAsking() instanceof User) {
                $this->subtasks_collection = Subtasks::prepareCollection('open_subtasks_assigned_to_team_' . $this->team->getId(), $this->getWhosAsking());
            } else {
                throw new ImpossibleCollectionError("Invalid user and/or who's asking instance");
            }
        }

        return $this->subtasks_collection;
    }
}
