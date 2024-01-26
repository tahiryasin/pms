<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Multiple labels interface definition.
 *
 * @package angie.frameworks.labels
 */
interface ILabels
{
    /**
     * Return object labels.
     *
     * @return Label[]
     */
    public function getLabels();

    /**
     * Return a list of object label ID-s.
     *
     * @return int[]
     */
    public function getLabelIds(): array;

    /**
     * Return number of labels that parent object has.
     *
     * @return int
     */
    public function countLabels();

    /**
     * Clear all existing object labels and return the old state (list of label ID-s before clearing).
     *
     * @return array
     */
    public function clearLabels(): array;

    /**
     * Clone attachments to a given object.
     *
     * @param  DataObject|ILabels $to
     * @return ILabels
     */
    public function &cloneLabelsTo(ILabels $to);

    public function getLabelType(): string;
}
