<?php

/*
 * This file is part of the Active Collab DateValue project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\DateValue;

use DateTimeInterface;
use JsonSerializable;

/**
 * @package ActiveCollab\DateValue
 */
interface DateValueInterface extends DateTimeInterface, JsonSerializable
{
}
