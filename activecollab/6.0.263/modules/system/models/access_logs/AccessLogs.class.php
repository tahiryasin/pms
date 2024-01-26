<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * AccessLogs class.
 *
 * @package activeCollab.modules.system
 * @subpackage models
 */
class AccessLogs extends BaseAccessLogs
{
    /**
     * Log access.
     *
     * @param  ApplicationObject|IAccessLog $instance
     * @param  IUser                        $by
     * @return IAccessLog
     */
    public static function logAccess(IAccessLog &$instance, $by)
    {
        if (!self::isPrefetchRequest() && !self::shouldProtectFromConsecutiveWrites($instance, $by)) {
            if ($by instanceof IUser) {
                DB::execute(
                    'INSERT INTO
                        access_logs (parent_type, parent_id, accessed_by_id, accessed_by_name, accessed_by_email, accessed_on, ip_address)
                        VALUES (?, ?, ?, ?, ?, ?, ?)',
                    get_class($instance),
                    $instance->getId(),
                    $by->getId(),
                    $by->getDisplayName(),
                    $by->getEmail(),
                    DateTimeValue::now(),
                    AngieApplication::getVisitorIp()
                );

                if ($by instanceof User && AngieApplication::isFrameworkLoaded('notifications')) {
                    Notifications::markReadByParent($instance, $by);
                }
            } else {
                DB::execute(
                    'INSERT INTO
                        access_logs (parent_type, parent_id, accessed_by_id, accessed_by_name, accessed_by_email, accessed_on, ip_address)
                        VALUES (?, ?, ?, ?, ?, ?, ?)',
                    get_class($instance),
                    $instance->getId(),
                    null,
                    null,
                    null,
                    DateTimeValue::now(),
                    AngieApplication::getVisitorIp()
                );
            }
        }

        return $instance;
    }

    /**
     * Check if recent access has been logged for the given user and object.
     *
     * @param  ApplicationObject|IAccessLog|array $parent
     * @param  IUser                              $by
     * @return bool
     * @throws InvalidParamError
     */
    protected static function shouldProtectFromConsecutiveWrites($parent, $by)
    {
        return static::isAccessedSince($parent, $by, DateTimeValue::now()->advance(-3, false));
    }

    /**
     * Returns true if $parent object was accessed by a given user since given
     * date and time value.
     *
     * $parent can be IAccessLogs instance, or array where first element is
     * parent class and the second element is parent ID
     *
     * @param  ApplicationObject|array|IAccessLog $parent
     * @param  IUser                              $by
     * @param  DateTimeValue                      $since
     * @return bool
     * @throws InvalidInstanceError
     * @throws InvalidParamError
     */
    public static function isAccessedSince($parent, IUser $by, DateTimeValue $since)
    {
        if ($parent instanceof IAccessLog) {
            $parent_type = get_class($parent);
            $parent_id = $parent->getId();
        } else {
            if (is_array($parent) && count($parent) == 2 && isset($parent[0]) && isset($parent[1])) {
                [$parent_type, $parent_id] = $parent;
            } else {
                throw new InvalidParamError('parent', $parent, 'Parent should be an IAccessLog instance or an array');
            }
        }

        if ($by instanceof User) {
            return (bool) DB::executeFirstCell('SELECT COUNT(id) FROM access_logs WHERE parent_type = ? AND parent_id = ? AND accessed_by_id = ? AND accessed_on >= ?', $parent_type, $parent_id, $by->getId(), $since);
        } elseif ($by instanceof AnonymousUser) {
            return (bool) DB::executeFirstCell('SELECT COUNT(id) FROM access_logs WHERE parent_type = ? AND parent_id = ? AND accessed_by_id = ? AND accessed_by_email = ? AND accessed_on >= ?', $parent_type, $parent_id, 0, $by->getEmail(), $since);
        }

        throw new InvalidInstanceError('by', $by, 'IUser');
    }

    /**
     * Register download.
     *
     * @param  ApplicationObject|IAccessLog|IFile             $instance
     * @param  IUser                                          $by
     * @return \Angie\Http\Response\FileDownload\FileDownload
     * @throws InvalidInstanceError
     */
    public static function logDownload(&$instance, $by = null)
    {
        if ($instance instanceof IFile) {
            if (!self::isPrefetchRequest() && !self::shouldProtectFromConsecutiveWrites($instance, $by)) {
                if ($by instanceof IUser) {
                    DB::execute('INSERT INTO access_logs (parent_type, parent_id, accessed_by_id, accessed_by_name, accessed_by_email, accessed_on, ip_address, is_download) VALUES (?, ?, ?, ?, ?, UTC_TIMESTAMP(), ?, ?)', get_class($instance), $instance->getId(), $by->getId(), $by->getDisplayName(), $by->getEmail(), AngieApplication::getVisitorIp(), true);
                } else {
                    DB::execute('INSERT INTO access_logs (parent_type, parent_id, accessed_by_id, accessed_by_name, accessed_by_email, accessed_on, ip_address, is_download) VALUES (?, ?, ?, ?, ?, UTC_TIMESTAMP(), ?, ?)', get_class($instance), $instance->getId(), null, null, null, AngieApplication::getVisitorIp(), true);
                }
            }

            return $instance->prepareForDownload();
        } else {
            throw new InvalidInstanceError('instance', $instance, 'IFile');
        }
    }

    /**
     * Log access on etag match.
     *
     * @param string $model
     * @param int    $id
     * @param string $email
     */
    public static function logAccessOnObjectEtagMatch($model, $id, $email)
    {
        if (self::isPrefetchRequest()) {
            return;
        }

        if ($user = Users::findByEmail($email, true)) {
            $class_name_from = call_user_func("$model::getInstanceClassNameFrom");

            if ($class_name_from == self::CLASS_NAME_FROM_FIELD) {
                $class_name = DB::executeFirstCell('SELECT type FROM ' . call_user_func("$model::getTableName") . ' WHERE id = ?', $id);
            } else {
                $class_name = call_user_func("$model::getInstanceClassName");
            }

            if ($class_name && self::classImplementsAccessLogInterface($class_name) && !self::shouldProtectFromConsecutiveWrites([$class_name, $id], $user)) {
                DB::execute('INSERT INTO access_logs (parent_type, parent_id, accessed_by_id, accessed_by_name, accessed_by_email, accessed_on, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?)', $class_name, $id, $user->getId(), $user->getName(), $user->getEmail(), DateTimeValue::now(), AngieApplication::getVisitorIp());
            }
        }
    }

    /**
     * @var array
     */
    private static $class_implements_access_logs_interface = [];

    /**
     * Return true if $class_name implements IAccessLog interface.
     *
     * @param  string $class_name
     * @return bool
     */
    public static function classImplementsAccessLogInterface($class_name)
    {
        if (!array_key_exists($class_name, self::$class_implements_access_logs_interface)) {
            self::$class_implements_access_logs_interface[$class_name] = class_exists($class_name, true)
                && (new ReflectionClass($class_name))->implementsInterface(IAccessLog::class);
        }

        return self::$class_implements_access_logs_interface[$class_name];
    }

    /**
     * Get all logs for given object.
     *
     * @param  IAccessLog $parent
     * @return array
     */
    public static function findByParent(IAccessLog $parent)
    {
        $result = [];

        if ($logs = DB::execute('SELECT accessed_by_id, accessed_by_name, ip_address, accessed_on, is_download FROM access_logs WHERE ' . self::parentToCondition($parent) . ' ORDER BY accessed_on')) {
            foreach ($logs as $log) {
                $result[] = [
                    'accessed_on' => DateTimeValue::makeFromString($log['accessed_on'])->getTimestamp(),
                    'name' => $log['accessed_by_name'],
                    'action' => $log['is_download'] ? 'download' : 'access',
                    'address' => $log['ip_address'] == '127.0.0.1' || $log['ip_address'] == '::1' ? 'localhost' : $log['ip_address'],
                ];
            }
        }

        return $result;
    }

    /**
     * Return true if this request is a resource pre-fetch request.
     *
     * @return bool
     */
    private static function isPrefetchRequest()
    {
        return isset($_SERVER['HTTP_X_ANGIE_PREFETCH']) && $_SERVER['HTTP_X_ANGIE_PREFETCH'];
    }
}
