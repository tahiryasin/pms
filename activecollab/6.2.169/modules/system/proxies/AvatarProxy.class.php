<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Forward user avatar proxy.
 *
 * @package ActiveCollab.modules.system
 * @subpackage proxies
 */
class AvatarProxy extends ProxyRequestHandler
{
    /**
     * ID of the user.
     *
     * @var int
     */
    protected $user_id;

    /**
     * Name of the user.
     *
     * @var string|null
     */
    protected $user_name;

    /**
     * Email of the user.
     *
     * @var string|null
     */
    protected $user_email;

    /**
     * Expected avatar dimensions.
     *
     * @var int
     */
    protected $size;

    /**
     * @var array
     */
    private $sizes = [20, 40, 80, 256];

    /**
     * Construct proxy request handler.
     *
     * @param array $params
     */
    public function __construct($params = null)
    {
        $this->user_id = isset($params['user_id']) && (int) $params['user_id'] > 0 ? (int) $params['user_id'] : null;
        $this->user_name = isset($params['user_name']) ? (string) $params['user_name'] : null;
        $this->user_email = isset($params['user_email']) ? (string) $params['user_email'] : null;
        $this->size = isset($params['size']) && $params['size'] ? (int) $params['size'] : 0;

        if (!in_array($this->size, $this->sizes)) {
            $this->size = 40;
        }
    }

    /**
     * Forward avatar.
     */
    public function execute()
    {
        require_once ANGIE_PATH . '/functions/general.php';
        require_once ANGIE_PATH . '/functions/web.php';
        require_once ANGIE_PATH . '/functions/files.php';

        if ($connection = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME)) {
            $connection->set_charset('utf8mb4');

            if (is_int($this->user_id) && $this->user_id > 0) {
                $result = $connection->query(sprintf("SELECT `avatar_location`, `first_name`, `last_name`, `email`, `raw_additional_properties` FROM `users` WHERE `id` = '%s'", $connection->real_escape_string($this->user_id)));
                if ($result->num_rows > 0) {
                    $user_details = $result->fetch_assoc();

                    $avatar_location = $user_details['avatar_location'];

                    $integration = $connection->query(sprintf("SELECT `raw_additional_properties` FROM `integrations` WHERE `type` = '%s'", 'WarehouseIntegration'));

                    $warehouse_integrations = $integration->num_rows
                        ? $integration->fetch_assoc()['raw_additional_properties']
                        : null;

                    if ($warehouse_integrations !== null) {
                        $properties = unserialize($user_details['raw_additional_properties']);
                        $avatar_md5 = isset($properties['avatar_md5']) ? $properties['avatar_md5'] : null;

                        if (!empty($avatar_location) && !empty($avatar_md5)) {
                            $this->renderAvatarFromWarehouse($avatar_location, $avatar_md5, $user_details);
                        } else {
                            $this->makeDefaultAvatar($user_details);
                        }
                    } else {
                        $source_file = empty($avatar_location) ? '' : UPLOAD_PATH . '/' . $avatar_location;

                        // user have uploaded avatar, use that avatar
                        if (is_file($source_file)) {
                            $tag = md5($avatar_location);

                            if ($this->getCachedEtag() == $tag) {
                                $this->avatarNotChanged($tag);
                            }

                            $this->renderAvatarFromSource($source_file, $tag);

                            // user doesn't have avatar uploaded generate it
                        } else {
                            $this->makeDefaultAvatar($user_details);
                        }
                    }
                } else {
                    $this->renderNaAvatar();
                }
            } elseif ($this->user_name || $this->user_email) {
                $this->handleAvatarWithNameOrEmail($this->user_name, $this->user_email);
            } else {
                $this->renderNaAvatar();
            }
        } else {
            $this->renderNaAvatar();
        }
    }

    /**
     * Handle avatar with user name or email.
     *
     * @param $name
     * @param $email
     */
    public function handleAvatarWithNameOrEmail($name, $email)
    {
        $user_details = [
            'first_name' => $name,
            'last_name' => null,
            'email' => $email,
        ];

        if ($name && $email) {
            // get appropriate image name for the fake user
            $image_tag = $this->getTagFromNameAndEmail($name, $email);
            $source_file = APPLICATION_PATH . "/modules/system/resources/sample_projects/avatars/{$image_tag}.png";

            if (is_file($source_file)) {
                $tag = $this->getTagFromSourceFile($source_file);

                return $this->renderAvatarFromSource($source_file, $tag);
            } else {
                return $this->makeDefaultAvatar($user_details);
            }
        } elseif ($name || $email) {
            return $this->makeDefaultAvatar($user_details);
        } else {
            return $this->renderNaAvatar();
        }
    }

    /**
     * Return tag from name and email.
     *
     * @param $user_name
     * @param $user_email
     * @return string
     */
    public function getTagFromNameAndEmail($user_name, $user_email)
    {
        return md5($user_name . $user_email);
    }

    /**
     * Return tag from source file.
     *
     * @param $source_file
     * @return string
     */
    public function getTagFromSourceFile($source_file)
    {
        return md5_file($source_file);
    }

    /**
     * Make default user avatar.
     *
     * @param $user_details
     */
    private function makeDefaultAvatar($user_details)
    {
        $tag = $this->getDefaultAvatarTag($user_details['first_name'], $user_details['last_name'], $user_details['email']);

        if ($this->getCachedEtag() == $tag) {
            $this->avatarNotChanged($tag);
        }

        $this->renderDefaultAvatar($user_details['first_name'], $user_details['last_name'], $user_details['email']);
    }

    /**
     * Serve not changed avatar.
     *
     * @param string $etag
     */
    private function avatarNotChanged($etag)
    {
        header('Content-Type: image/png');
        header('Content-Disposition: inline; filename=avatar.png');
        header('Cache-Control: public, max-age=315360000');
        header('Pragma: public');
        header('Etag: ' . $etag);

        $this->notModified();
    }

    /**
     * Render avatar from custom source file.
     *
     * @param string $source_file
     * @param string $tag
     * @param bool   $resize_image
     */
    private function renderAvatarFromSource($source_file, $tag, $resize_image = true)
    {
        $thumb_file = THUMBNAILS_PATH . "/upload-user-avatar-{$tag}-{$this->size}x{$this->size}-crop";

        if (!is_file($thumb_file)) {
            if ($resize_image) {
                scale_and_crop_image_alt(
                    $source_file,
                    $thumb_file,
                    $this->size * 2,
                    $this->size * 2,
                    null,
                    null,
                    IMAGETYPE_PNG
                );
            } else {
                copy($source_file, $thumb_file);
            }
        }

        if (is_file($thumb_file)) {
            header('Content-Type: image/png');
            header('Content-Disposition: inline; filename=avatar.png');
            header('Cache-Control: public, max-age=315360000');
            header('Pragma: public');
            header('Etag: ' . $tag);

            print file_get_contents($thumb_file);
            die();
        }
    }

    /**
     * Render N/A avatar.
     */
    private function renderNaAvatar()
    {
        $tag = $this->getDefaultAvatarTag('', '', 'not.available@example.com');

        if ($this->getCachedEtag() == $tag) {
            $this->avatarNotChanged($tag);
        }

        $this->renderDefaultAvatar('', '', 'not.available@example.com');
    }

    /**
     * Render default avatar.
     *
     * @param string $first_name
     * @param string $last_name
     * @param string $email
     */
    private function renderDefaultAvatar($first_name, $last_name, $email)
    {
        require_once APPLICATION_PATH . '/vendor/autoload.php';

        $text = '';

        // determine initials depending on first name and last name
        if ($first_name || $last_name) {
            if ($first_name) {
                $text .= mb_substr($first_name, 0, 1);
            }

            if ($last_name) {
                $text .= mb_substr($last_name, 0, 1);
            }
        } else {
            $email_username = explode('@', $email)[0];
            $email_username_parts = explode('.', $email_username);

            foreach ($email_username_parts as $email_username_part) {
                $text .= mb_substr($email_username_part, 0, 1);
            }
        }

        $filename = WORK_PATH . '/default_avatar_' . md5($text) . '_' . $this->size . '.png';

        if (!file_exists($filename)) {
            generate_avatar_with_initials($filename, $this->size, $text);
        }

        if (file_exists($filename)) {
            header('Content-Type: image/png');
            header('Content-Disposition: inline; filename=avatar.png');
            header('Cache-Control: public, max-age=315360000');
            header('Pragma: public');
            header('Etag: ' . $this->getDefaultAvatarTag($first_name, $last_name, $email));
            print file_get_contents($filename);
            die();
        }

        $this->notFound();
    }

    /**
     * Return default avatar tag.
     *
     * @param  string $first_name
     * @param  string $last_name
     * @param  string $email
     * @return string
     */
    private function getDefaultAvatarTag($first_name, $last_name, $email)
    {
        return md5($first_name . $last_name . $email);
    }

    /**
     * Render warehouse avatar.
     *
     * @param string $location
     * @param string $hash
     * @param array  $user_details
     */
    private function renderAvatarFromWarehouse($location, $hash, $user_details)
    {
        if ($this->getCachedEtag() == $hash) {
            $this->avatarNotChanged($hash);
        }

        $downloaded_avatar = $this->downloadAvatarFromWarehouse($location, $hash);

        if ($downloaded_avatar) {
            $this->renderAvatarFromSource($downloaded_avatar, $hash);
        } else {
            $this->makeDefaultAvatar($user_details);
        }
    }

    private function downloadAvatarFromWarehouse($location, $hash): ?string
    {
        $location = urlencode($location);

        $source_file = CACHE_PATH . "/avatar-{$location}-{$hash}";

        if (is_file($source_file)) {
            return $source_file;
        } else {
            $file = file_get_contents(
                sprintf('%s/api/v1/files/%s/%s/download', WAREHOUSE_URL, $location, $hash)
            );

            if ($file) {
                file_put_contents($source_file, $file);

                if (is_file($source_file)) {
                    return $source_file;
                }
            }

            return null;
        }
    }
}
