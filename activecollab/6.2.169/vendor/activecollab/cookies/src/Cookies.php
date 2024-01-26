<?php

/*
 * This file is part of the Active Collab Cookies project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\Cookies;

use ActiveCollab\Cookies\Adapter\Adapter;
use ActiveCollab\Cookies\Adapter\AdapterInterface;
use ActiveCollab\CurrentTimestamp\CurrentTimestamp;
use ActiveCollab\CurrentTimestamp\CurrentTimestampInterface;
use ActiveCollab\Encryptor\EncryptorInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @package ActiveCollab\Cookies
 */
class Cookies implements CookiesInterface
{
    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * @var CurrentTimestamp
     */
    private $current_timestamp;

    /**
     * @param AdapterInterface|null          $adapter
     * @param CurrentTimestampInterface|null $current_timestamp
     */
    public function __construct(AdapterInterface $adapter = null, CurrentTimestampInterface $current_timestamp = null)
    {
        $this->adapter = $adapter ? $adapter : new Adapter();
        $this->current_timestamp = $current_timestamp;

        if (empty($this->current_timestamp)) {
            $this->current_timestamp = new CurrentTimestamp();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function exists(ServerRequestInterface $request, $name)
    {
        return $this->adapter->exists($request, $this->getPrefixedName($name));
    }

    /**
     * {@inheritdoc}
     */
    public function get(ServerRequestInterface $request, $name, $default = null, array $settings = [])
    {
        if ($this->exists($request, $name)) {
            $value = $this->adapter->get($request, $this->getPrefixedName($name), $default);

            $decrypt = array_key_exists('decrypt', $settings) ? (bool) $settings['decrypt'] : true;

            if ($decrypt && $this->encryptor) {
                $value = $this->encryptor->decrypt($value);
            }

            return $value;
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function set(ServerRequestInterface $request, ResponseInterface $response, $name, $value, array $settings = [])
    {
        $settings['domain'] = $this->getDomain();
        $settings['path'] = $this->getPath();
        $settings['secure'] = $this->getSecure();

        if (empty($settings['ttl'])) {
            $settings['ttl'] = $this->getDefaultTtl();
        }

        if (empty($settings['http_only'])) {
            $settings['http_only'] = false;
        }

        $encrypt = array_key_exists('encrypt', $settings) ? $settings['encrypt'] : true;

        if ($encrypt && $this->encryptor) {
            $value = $this->encryptor->encrypt($value);
        }

        $settings['expires'] = $this->current_timestamp->getCurrentTimestamp() + $settings['ttl'];

        return $this->adapter->set($request, $response, $this->getPrefixedName($name), $value, $settings);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(ServerRequestInterface $request, ResponseInterface $response, $name)
    {
        return $this->adapter->remove($request, $response, $this->getPrefixedName($name));
    }

    /**
     * {@inheritdoc}
     */
    private function getPrefixedName($name)
    {
        return $this->getPrefix() . $name;
    }

    // ---------------------------------------------------
    //  Configuration
    // ---------------------------------------------------

    /**
     * Default TTL (14 days).
     *
     * @var int
     */
    private $default_ttl = 1209600;

    /**
     * @return int
     */
    public function getDefaultTtl()
    {
        return $this->default_ttl;
    }

    /**
     * Set default cookie TTL (time to live).
     *
     * @param  int   $value
     * @return $this
     */
    public function defaultTtl($value)
    {
        $this->default_ttl = $value;

        return $this;
    }

    /**
     * @var string
     */
    private $domain;

    /**
     * {@inheritdoc}
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * {@inheritdoc}
     */
    public function domain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * @var string
     */
    private $path = '/';

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
    public function path($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @var bool
     */
    private $secure = true;

    /**
     * {@inheritdoc}
     */
    public function getSecure()
    {
        return $this->secure;
    }

    /**
     * {@inheritdoc}
     */
    public function secure($secure)
    {
        $this->secure = (bool) $secure;

        return $this;
    }

    /**
     * @var string
     */
    private $prefix;

    /**
     * {@inheritdoc}
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * {@inheritdoc}
     */
    public function prefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function configureFromUrl($url)
    {
        $parts = parse_url($url);

        if (!empty($parts['scheme'])) {
            $this->secure(strtolower($parts['scheme']) === 'https');
        }

        $this->domain($parts['host']);

        if (empty($parts['path'])) {
            if ($this->getPath() != '/') {
                $this->path('/');
            }
        } else {
            $this->path('/' . trim($parts['path'], '/'));
        }

        if (empty($this->getPrefix())) {
            $this->prefix(md5($url));
        }

        return $this;
    }

    /**
     * @var EncryptorInterface|null
     */
    private $encryptor;

    /**
     * @return EncryptorInterface|null
     */
    public function getEncryptor()
    {
        return $this->encryptor;
    }

    /**
     * {@inheritdoc}
     */
    public function encryptor(EncryptorInterface $encryptor = null)
    {
        $this->encryptor = $encryptor;

        return $this;
    }
}
