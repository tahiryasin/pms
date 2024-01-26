<?php

/*
 * This file is part of the Active Collab Utils project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\Encryptor;

use InvalidArgumentException;

/**
 * Encrypt and decrypt values.
 *
 * Inspired by Nelmio Security Bundle encryptor:
 * https://github.com/nelmio/NelmioSecurityBundle/blob/master/Encrypter.php
 *
 * and refactored to use OpenSSL based on this article:
 * https://paragonie.com/blog/2015/05/if-you-re-typing-word-mcrypt-into-your-code-you-re-doing-it-wrong
 *
 * @package ActiveCollab\Encryptor
 */
class Encryptor implements EncryptorInterface
{
    const METHOD = 'aes-256-cbc';

    /**
     * @var string
     */
    private $key;

    /**
     * @var int
     */
    private $iv_size;

    /**
     * @param string $key
     */
    public function __construct($key)
    {
        if (!is_string($key) || empty($key)) {
            throw new InvalidArgumentException('Key needs to be a non-empty string value');
        }

        $this->key = $key;
        $this->iv_size = openssl_cipher_iv_length(self::METHOD);
    }

    /**
     * @param  mixed  $value
     * @return string
     */
    public function encrypt($value)
    {
        if (!is_string($value)) {
            $value = (string) $value;
        }

        $iv = openssl_random_pseudo_bytes($this->iv_size);

        return base64_encode(openssl_encrypt($value, self::METHOD, $this->key, OPENSSL_RAW_DATA, $iv)) . ':' . base64_encode($iv);
    }

    /**
     * @param  string $value
     * @return mixed
     */
    public function decrypt($value)
    {
        if (empty($value)) {
            throw new InvalidArgumentException('Value is required.');
        }

        $separated_data = explode(':', $value);

        if (count($separated_data) != 2) {
            throw new InvalidArgumentException('Separator not found in the encrypted data.');
        }

        return openssl_decrypt(base64_decode($separated_data[0], true), self::METHOD, $this->key, OPENSSL_RAW_DATA, base64_decode($separated_data[1], true));
    }
}
