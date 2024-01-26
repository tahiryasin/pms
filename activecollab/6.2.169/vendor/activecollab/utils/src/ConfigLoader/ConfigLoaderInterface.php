<?php

/*
 * This file is part of the Active Collab Utils project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\ConfigLoader;

interface ConfigLoaderInterface
{
    public function isLoaded();

    public function &load();

    public function hasValue($option_name);

    public function getValue($option_name, $default = null);

    public function &requirePresence(...$config_options);

    public function &requireValue(...$config_options);

    public function &requirePresenceWhen($option, $has_value, ...$require_config_options);

    public function &requireValueWhen($option, $has_value, ...$require_config_options);
}
