<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie;

use AngieApplication;
use Exception;
use JsonSerializable;

/**
 * Foundation class of all Angie exceptions.
 *
 * @package angie
 */
class Error extends Exception implements JsonSerializable
{
    /**
     * Error message.
     *
     * @var string
     */
    protected $message;

    /**
     * Error line.
     *
     * @var int
     */
    protected $line;

    /**
     * Error file.
     *
     * @var string
     */
    protected $file;

    /**
     * Additional error parameters.
     *
     * @var array
     */
    protected $additional = null;

    /**
     * Construct error object.
     *
     * @param string          $message
     * @param array|null      $additional
     * @param |Exception|null $previous
     */
    public function __construct($message, array $additional = null, $previous = null)
    {
        $this->additional = $additional;
        parent::__construct($message, 0, $previous);
    }

    /**
     * Return error params (name -> value pairs).
     *
     * General params are file and line where error was thrown. Subclasses may
     * have their own error parameters
     *
     * @return array
     */
    public function getParams()
    {
        return is_array($this->additional) && count($this->additional) ? $this->additional : [];
    }

    /**
     * Return specific parameter.
     *
     * @param  string $name
     * @return mixed
     */
    public function getParam($name)
    {
        return array_var($this->additional, $name);
    }

    /**
     * Return array or property => value pairs that describes this object.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return array_merge([
            'type' => get_class($this),
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'trace' => AngieApplication::isInDebugMode() || AngieApplication::isInDevelopment() ? $this->getTraceAsString() : null,
            'previous' => $this->getPrevious(),
        ], $this->getParams());
    }
}
