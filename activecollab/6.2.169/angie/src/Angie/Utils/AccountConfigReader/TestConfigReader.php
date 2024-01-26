<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace Angie\Utils\AccountConfigReader;

use ActiveCollab\Module\OnDemand\Models\Pricing\PricingModelInterface;
use ClassicAccountStatusInterface;
use DateValue;

class TestConfigReader implements AccountConfigReaderInterface
{
    /**
     * @var string
     */
    private $plan;

    /**
     * @var float
     */
    private $plan_price;

    /**
     * @var string
     */
    private $billing_period;

    /**
     * @var int
     */
    private $status;

    /**
     * @var string|null
     */
    private $status_expires_on;

    /**
     * @var string|null
     */
    private $next_billing_date;

    /**
     * @var string|null
     */
    private $reference_billing_date;

    /**
     * @var bool
     */
    private $is_activated;

    /**
     * @var int
     */
    private $max_members;

    /**
     * @var int
     */
    private $max_disk_space;

    /**
     * @var int
     */
    private $max_projects;

    /**
     * @deprecated
     * @var bool
     */
    private $is_non_profit;

    /**
     * @var string
     */
    private $pricing_model;

    /**
     * @var array
     */
    private $add_ons;

    /**
     * @var float
     */
    private $mrr_value;

    /**
     * @var int
     */
    private $chargeable_users_count;
    /**
     * @var string
     */
    private $discount;

    public function __construct(
        string $plan = 'XL',
        float $plan_price = 0.0,
        string $billing_period = 'monthly',
        int $status = ClassicAccountStatusInterface::CLASSIC_STATUS_ACTIVE_FREE,
        ?string $status_expires_on = null,
        ?string $next_billing_date = null,
        int $max_members = 0,
        int $max_disk_space = 0,
        int $max_projects = 0,
        bool $is_non_profit = false,
        string $pricing_model = PricingModelInterface::PRICING_MODEL_PLANS_2013,
        array $add_ons = [],
        ?string $reference_billing_date = null,
        float $mrr_value = 0.0,
        int $chargeable_users_count = 0,
        ?string $discount = null
    ) {
        $this->plan = $plan;
        $this->plan_price = $plan_price;
        $this->billing_period = $billing_period;
        $this->status = $status;
        $this->status_expires_on = $status_expires_on;
        $this->max_members = $max_members;
        $this->max_disk_space = $max_disk_space;
        $this->max_projects = $max_projects;
        $this->next_billing_date = $next_billing_date;
        $this->is_non_profit = $is_non_profit;
        $this->pricing_model = $pricing_model;
        $this->add_ons = $add_ons;
        $this->reference_billing_date = $reference_billing_date;
        $this->mrr_value = $mrr_value;
        $this->chargeable_users_count = $chargeable_users_count;
        $this->discount = $discount;
    }

    /**
     * @return string
     */
    public function getPlan(): string
    {
        return $this->plan;
    }

    /**
     * @return float
     */
    public function getPlanPrice(): float
    {
        return $this->plan_price;
    }

    /**
     * @return string
     */
    public function getBillingPeriod(): string
    {
        return $this->billing_period;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->getStatusFromClassicStatus();
    }

    /**
     * @return DateValue
     */
    public function getStatusExpiresOn(): DateValue
    {
        return new DateValue($this->status_expires_on);
    }

    /**
     * @return DateValue|null
     */
    public function getReferenceBillingDate(): ?DateValue
    {
        return new DateValue($this->reference_billing_date);
    }

    /**
     * @return DateValue|null
     */
    public function getNextBillingDate(): ?DateValue
    {
        return new DateValue($this->next_billing_date);
    }

    /**
     * @return bool
     */
    public function isActivated(): bool
    {
        return $this->getIsActivatedFromClassicStatus();
    }

    /**
     * @return int
     */
    public function getMaxMembers(): int
    {
        return $this->max_members;
    }

    /**
     * @return int
     */
    public function getMaxDiskSpace(): int
    {
        return $this->max_disk_space;
    }

    /**
     * @return int
     */
    public function getMaxProjects(): int
    {
        return $this->max_projects;
    }

    /**
     * @return bool
     */
    public function isPaid(): bool
    {
        return $this->getIsPaidFromClassicStatus();
    }

    /**
     * @deprecated
     * @return bool
     */
    public function isNonProfit(): bool
    {
        return $this->is_non_profit;
    }

    /**
     * @return string
     */
    public function getPricingModel(): string
    {
        return $this->pricing_model;
    }

    /**
     * @return array
     */
    public function getAddOns(): array
    {
        return $this->add_ons;
    }

    /**
     * @return string
     */
    public function getDiscount(): string
    {
        return (string) $this->discount;
    }

    /**
     * @return float
     */
    public function getMrrValue(): float
    {
        return $this->mrr_value;
    }

    /**
     * @return int
     */
    public function getChargeableUsersCountValue(): int
    {
        return $this->chargeable_users_count;
    }

    private function getStatusFromClassicStatus(): string
    {
        switch ($this->status) {
            case ClassicAccountStatusInterface::CLASSIC_STATUS_PENDING_ACTIVATION:
            case ClassicAccountStatusInterface::CLASSIC_STATUS_ACTIVE_FREE:
                return ClassicAccountStatusInterface::STATUS_TRIAL;
            case ClassicAccountStatusInterface::CLASSIC_STATUS_ACTIVE_PAID:
                return ClassicAccountStatusInterface::STATUS_ACTIVE;
            case ClassicAccountStatusInterface::CLASSIC_STATUS_FAILED_PAYMENT:
                return ClassicAccountStatusInterface::STATUS_FAILED_PAYMENT;
            case ClassicAccountStatusInterface::CLASSIC_STATUS_SUSPENDED_FREE:
            case ClassicAccountStatusInterface::CLASSIC_STATUS_SUSPENDED_PAID:
                return ClassicAccountStatusInterface::STATUS_SUSPENDED;
            case ClassicAccountStatusInterface::CLASSIC_STATUS_RETIRED_FREE:
            case ClassicAccountStatusInterface::CLASSIC_STATUS_RETIRED_PAID:
            case ClassicAccountStatusInterface::CLASSIC_STATUS_PENDING_DELETION:
            default:
                return ClassicAccountStatusInterface::STATUS_RETIRED;
        }
    }

    private function getIsActivatedFromClassicStatus(): bool
    {
        return !in_array(
            $this->status,
            [
                ClassicAccountStatusInterface::CLASSIC_STATUS_PENDING_ACTIVATION,
                ClassicAccountStatusInterface::CLASSIC_STATUS_ACTIVE_FREE,
                ClassicAccountStatusInterface::CLASSIC_STATUS_SUSPENDED_FREE,
                ClassicAccountStatusInterface::CLASSIC_STATUS_RETIRED_FREE,
            ]
        );
    }

    private function getIsPaidFromClassicStatus(): bool
    {
        return in_array(
            $this->status,
            [
                ClassicAccountStatusInterface::CLASSIC_STATUS_ACTIVE_PAID,
                ClassicAccountStatusInterface::CLASSIC_STATUS_FAILED_PAYMENT,
                ClassicAccountStatusInterface::CLASSIC_STATUS_SUSPENDED_PAID,
                ClassicAccountStatusInterface::CLASSIC_STATUS_RETIRED_PAID,
            ]
        );
    }
}
