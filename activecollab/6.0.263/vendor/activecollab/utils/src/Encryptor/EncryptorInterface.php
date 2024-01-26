<?php

/*
 * This file is part of the Active Collab Utils project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\Encryptor;

/**
 * @package ActiveCollab\Encryptor
 */
interface EncryptorInterface
{
    /**
     * @param  mixed  $value
     * @return string
     */
    public function encrypt($value);

    /**
     * @param  string $value
     * @return mixed
     */
    public function decrypt($value);
}
