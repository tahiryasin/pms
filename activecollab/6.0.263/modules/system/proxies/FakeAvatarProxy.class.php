<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Forward fake user avatar proxy.
 *
 * @package ActiveCollab.modules.system
 * @subpackage proxies
 */
class FakeAvatarProxy extends ProxyRequestHandler
{
    /**
     * Fake name of the assignee.
     *
     * @var string
     */
    protected $fake_assignee_name;

    /**
     * Fake email of the assignee.
     *
     * @var string
     */
    protected $fake_assignee_email;

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
        $this->fake_assignee_name = isset($params['fake_assignee_name']) ? (string) $params['fake_assignee_name'] : null;
        $this->fake_assignee_email = isset($params['fake_assignee_email']) ? (string) $params['fake_assignee_email'] : null;
        $this->size = isset($params['size']) && $params['size'] ? (int) $params['size'] : 0;
        if (!in_array($this->size, $this->sizes)) {
            $this->size = 40;
        }
    }

    /**
     * Forward fake avatar.
     */
    public function execute()
    {
        require_once ANGIE_PATH . '/functions/general.php';
        require_once ANGIE_PATH . '/functions/web.php';
        require_once ANGIE_PATH . '/functions/files.php';

        if ($this->fake_assignee_name && $this->fake_assignee_email) {
            $source_file = APPLICATION_PATH . "/modules/system/resources/sample_projects/avatars/{$this->fake_assignee_name}.png";

            if (is_file($source_file)) {
                $tag = $this->getTagFromSourceFile($source_file);

                return $this->renderFakeAvatarFromSource($source_file, $tag);
            } else {
                return $this->makeDefaultFakeAvatar($this->fake_assignee_name, $this->fake_assignee_email);
            }
        } elseif ($this->fake_assignee_name && !$this->fake_assignee_email) {
            return $this->makeDefaultFakeAvatar($this->fake_assignee_name, '');
        } elseif (!$this->fake_assignee_name && $this->fake_assignee_email) {
            return $this->makeDefaultFakeAvatar('', $this->fake_assignee_email);
        } else {
            return $this->renderFakeNaAvatar();
        }
    }

    /**
     * Return tag from assignee name and email.
     *
     * @param $fake_assignee_name
     * @param $fake_assignee_email
     * @return string
     */
    public function getTagFromNameAndEmail($fake_assignee_name, $fake_assignee_email)
    {
        return md5($fake_assignee_name . $fake_assignee_email);
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
     * Render avatar from custom source file.
     *
     * @param string $source_file
     * @param string $tag
     * @param bool   $resize_image
     */
    private function renderFakeAvatarFromSource($source_file, $tag, $resize_image = true)
    {
        if ($this->getCachedEtag() == $tag) {
            $this->avatarNotChanged($tag);
        }

        $thumb_file = THUMBNAILS_PATH . "/fake-user-avatar-{$tag}-{$this->size}x{$this->size}-crop";

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
     * Render fake N/A avatar.
     */
    private function renderFakeNaAvatar()
    {
        $tag = $this->getTagFromNameAndEmail('', 'not.available@example.com');

        if ($this->getCachedEtag() == $tag) {
            $this->avatarNotChanged($tag);
        }

        $this->renderDefaultFakeAvatar('', 'not.available@example.com');
    }

    /**
     * Render default fake avatar.
     *
     * @param string $fake_assignee_name
     * @param string $fake_assignee_email
     */
    private function renderDefaultFakeAvatar($fake_assignee_name, $fake_assignee_email)
    {
        require_once APPLICATION_PATH . '/vendor/autoload.php';

        $text = '';

        if ($fake_assignee_name) {
            $text .= mb_substr($fake_assignee_name, 0, 1);
        } else {
            $email_username = explode('@', $fake_assignee_email)[0];
            $email_username_parts = explode('.', $email_username);

            foreach ($email_username_parts as $email_username_part) {
                $text .= mb_substr($email_username_part, 0, 1);
            }
        }

        $filename = WORK_PATH . '/default_fake_avatar_' . strtolower(\Angie\Inflector::transliterate($text)) . '_' . $this->size . '.png';

        if (!file_exists($filename)) {
            generate_avatar_with_initials($filename, $this->size, $text);
        }

        if (file_exists($filename)) {
            header('Content-Type: image/png');
            header('Content-Disposition: inline; filename=avatar.png');
            header('Cache-Control: public, max-age=315360000');
            header('Pragma: public');
            header('Etag: ' . $this->getTagFromNameAndEmail($fake_assignee_name, $fake_assignee_email));
            print file_get_contents($filename);
            die();
        }

        $this->notFound();
    }

    /**
     * Make default fake user avatar.
     *
     * @param $fake_assignee_name
     * @param $fake_assignee_email
     */
    private function makeDefaultFakeAvatar($fake_assignee_name, $fake_assignee_email)
    {
        $tag = $this->getTagFromNameAndEmail($fake_assignee_name, $fake_assignee_email);

        if ($this->getCachedEtag() == $tag) {
            $this->avatarNotChanged($tag);
        }

        $this->renderDefaultFakeAvatar($fake_assignee_name, $fake_assignee_email);
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
}
