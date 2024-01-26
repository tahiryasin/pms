<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

AngieApplication::useController('fw_payments', PaymentsFramework::NAME);

/**
 * Payments  controller.
 *
 * @package activeCollab.modules.system
 * @subpackage controllers
 */
class PaymentsController extends FwPaymentsController
{
}
