<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace Angie\Utils\AccountConfigReader;

use DateValue;

interface AccountConfigReaderInterface
{
    /**
     * @return string
     */
    public function getPlan(): string;

    /**
     * @return string
     */
    public function getBillingPeriod(): string;

    /**
     * @return float
     */
    public function getPlanPrice(): float;

    /**
     * @return string
     */
    public function getStatus(): string;

    /**
     * @return DateValue
     */
    public function getStatusExpiresOn(): DateValue;

    /**
     * @return DateValue
     */
    public function getReferenceBillingDate(): ?DateValue;

    /**
     * @return DateValue
     */
    public function getNextBillingDate(): ?DateValue;

    /**
     * @return int
     */
    public function getMaxMembers(): int;

    /**
     * @return int
     */
    public function getMaxProjects(): int;

    /**
     * @return int
     */
    public function getMaxDiskSpace(): int;

    /**
     * @return bool
     */
    public function isActivated(): bool;

    /**
     * @return bool
     */
    public function isPaid(): bool;

    /**
     * @deprecated
     * @return bool*
     */
    public function isNonProfit(): bool;

    /**
     * @return string
     */
    public function getPricingModel(): string;

    /**
     * @return array
     */
    public function getAddOns(): array;

    /**
     * @return string
     */
    public function getDiscount(): string;

    /**
     * @return float
     */
    public function getMrrValue(): float;

    /**
     * @return int
     */
    public function getChargeableUsersCountValue(): int;
}
