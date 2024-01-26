<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\Http\Response\StaticHtmlFile;

/**
 * @package Angie\Http\Response\StaticHtmlFile
 */
class StaticHtmlFile implements StaticHtmlFileInterface
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var array
     */
    private $options;

    /**
     * StaticHtmlFile constructor.
     *
     * @param string $path
     * @param array  $options
     */
    public function __construct($path, array $options = [])
    {
        $this->path = $path;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        $content = file_get_contents($this->getPath());

        if (!empty($this->getOptions())) {
            foreach ($this->getOptions() as $key => $val) {
                $content = str_replace($key, $val, $content);
            }
        }

        return $content;
    }
}
