<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Application level labels manager.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
class Labels extends FwLabels
{
    /**
     * Get color palette.
     *
     * @return array
     */
    public static function getColorPalette()
    {
        return array_keys(Label::COLOR_PALETTE);
    }

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
        $collection = parent::prepareCollection($collection_name, $user);

        switch ($collection_name) {
            case 'project_labels':
                $collection->setConditions(['type = ?', ProjectLabel::class]);
                break;
            case 'task_labels':
                $collection->setConditions(['type = ?', TaskLabel::class]);
                break;
            case DataManager::ALL:
                break;
            default:
                throw new InvalidParamError('collection_name', $collection_name);
        }

        return $collection;
    }

    /**
     * Return array of project ID-s in which this label is used.
     *
     * @param  Project $project
     * @return array
     */
    public static function getLabelIdsByProject(Project $project)
    {
        $label_ids = DB::executeFirstColumn(
            'SELECT DISTINCT pl.label_id FROM parents_labels AS pl LEFT JOIN tasks AS t ON pl.parent_type = ? AND pl.parent_id = t.id WHERE t.project_id = ?',
            Task::class,
            $project->getId()
        );

        if (empty($label_ids)) {
            $label_ids = [];
        }

        return $label_ids;
    }

    /**
     * Get labels details by type without hydration.
     * It gives better performance for initial request.
     *
     * @param  string $label_type
     * @return array
     */
    public static function getLabelsDetailsByType($label_type)
    {
        $results = [];

        $labels = $labels = DB::execute('SELECT * FROM `labels` WHERE `type` = ?', $label_type);

        if (!empty($labels)) {
            foreach ($labels as $label) {
                $color = strtoupper($label['color'] ? $label['color'] : Label::LABEL_DEFAULT_COLOR);

                $results[] = [
                    'id' => $label['id'],
                    'class' => $label_type,
                    'name' => $label['name'],
                    'color' => $color,
                    'darker_text_color' => self::getDarkerTextColorFor((string) $color),
                    'lighter_text_color' => self::getLighterTextColorFor((string) $color),
                    'is_default' => $label['is_default'],
                    'is_global' => $label['is_global'],
                    'position' => $label['position'],
                    'url_path' => "/labels/{$label['id']}",
                ];
            }
        }

        return $results;
    }

    private static function getLighterTextColorFor(string $color): string
    {
        return self::getTextColorFromPalette($color, 'lighter_text', '#ACACAC');
    }

    private static function getDarkerTextColorFor(string $color): string
    {
        return self::getTextColorFromPalette($color, 'darker_text', '#808080');
    }

    private static function getTextColorFromPalette(string $color, string $key, string $default): string
    {
        return array_key_exists($color, Label::COLOR_PALETTE)
            ? Label::COLOR_PALETTE[$color][$key]
            : $default;
    }
}
