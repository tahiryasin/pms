<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Files manager class.
 *
 * @package ActiveCollab.modules.files
 * @subpackage models
 */
class Files extends BaseFiles
{
    use IProjectElementsImplementation;

    /**
     * Return new collection.
     *
     * @param  string                    $collection_name
     * @param  User|null                 $user
     * @return ModelCollection
     * @throws InvalidParamError
     * @throws ImpossibleCollectionError
     */
    public static function prepareCollection($collection_name, $user)
    {
        if (str_starts_with($collection_name, 'files_in_project')) {
            $bits = explode('_', $collection_name);

            $page = array_pop($bits);
            array_pop($bits); // _page_

            $project = DataObjectPool::get('Project', array_pop($bits));

            if ($project instanceof Project) {
                $collection = new ProjectFilesAndAttachmentsCollection($collection_name, $user);

                $collection->setProject($project);
                $collection->setPagination($page, 30);

                if ($user instanceof Client) {
                    $collection->setSkipFilesHiddenFromClients(true);
                }

                return $collection;
            } else {
                throw new ImpossibleCollectionError('Project not found');
            }
        } else {
            throw new InvalidParamError('collection_name', $collection_name, 'Invalid collection name');
        }
    }

    // ---------------------------------------------------
    //  Permissions
    // ---------------------------------------------------

    /**
     * Returns true if $user can create a new task in $project.
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
    //  Utility
    // ---------------------------------------------------

    public static function create(array $attributes, bool $save = true, bool $announce = true)
    {
        $code = isset($attributes['uploaded_file_code']) && $attributes['uploaded_file_code'] ? $attributes['uploaded_file_code'] : null;
        $uploaded_file = $code ? UploadedFiles::findByCode($code) : null;

        if ($uploaded_file instanceof UploadedFile) {
            $attributes['type'] = str_replace('UploadedFile', '', get_class($uploaded_file)) . 'File';
            $attributes['name'] = $uploaded_file->getName();
            $attributes['mime_type'] = $uploaded_file->getMimeType();
            $attributes['size'] = $uploaded_file->getSize();
            $attributes['location'] = $uploaded_file->getLocation();
            $attributes['md5'] = $uploaded_file->getMd5();
            $attributes['raw_additional_properties'] = serialize($uploaded_file->getAdditionalProperties());
            if ($uploaded_file instanceof WarehouseUploadedFile) {
                $attributes['search_content'] = $uploaded_file->getTikaData();
            }
        } else {
            throw new InvalidParamError('attributes[uploaded_file_code]', $code);
        }

        /** @var File $file */
        $file = parent::create($attributes, $save, false);

        if ($file instanceof File) {
            $uploaded_file->keepFileOnDelete(true);
            $uploaded_file->delete();
        }

        return DataObjectPool::announce($file, DataObjectPool::OBJECT_CREATED, $attributes);
    }

    /**
     * Return file name that is unique in the project.
     *
     * @param  string               $name
     * @param  Project              $project
     * @return string
     * @throws InvalidInstanceError
     */
    public static function getProjectSafeName($name, Project $project)
    {
        if ($project instanceof Project) {
            /*
             * Check if $name is reserved in the given context
             *
             * @param string $name
             * @return bool
             */
            $is_name_reserved = function ($name) use ($project) {
                return (bool) DB::executeFirstCell('SELECT COUNT(id) FROM files WHERE project_id = ? AND name = ?', $project->getId(), $name);
            };

            if ($is_name_reserved($name)) {
                $first_dot = strpos($name, '.');

                if (empty($first_dot)) {
                    $base = $name;
                    $extension = '';
                } else {
                    $base = substr($name, 0, $first_dot);
                    $extension = substr($name, $first_dot);
                }

                $counter = 1;

                do {
                    $new_name = $base . '-' . $counter++ . '' . $extension;
                } while ($is_name_reserved($new_name));

                return $new_name;
            }

            return $name;
        }

        throw new InvalidInstanceError('project', $project, 'IProject');
    }
}
