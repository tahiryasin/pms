<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Module\System\Metric;

use AccountBalanceInterface;
use AccountSettingsInterface;
use ActiveCollab\Module\OnDemand\Utils\ChargableUsersResolver\ChargeableUsersResolverInterface;
use ActiveCollab\Module\OnDemand\Utils\ChargeableBeforeCoronaResolver\ChargeableUsersBeforeCoronaResolverInterface;
use Angie\Metric\Collection;
use Angie\Metric\Result\ResultInterface;
use Angie\Utils\AccountConfigReader\AccountConfigReaderInterface;
use DateValue;
use PaymentMethodInterface;

final class AccountCollection extends Collection
{
    private $account_settings;
    private $account_config_reader;
    private $account_balance;
    private $chargeable_users_before_corona_resolver;
    private $chargeable_users_resolver;

    public function __construct(
        AccountSettingsInterface $account_settings,
        AccountConfigReaderInterface $account_config_reader,
        AccountBalanceInterface $account_balance,
        ChargeableUsersBeforeCoronaResolverInterface $chargeable_users_before_corona_resolver,
        ChargeableUsersResolverInterface $chargeable_users_resolver
    ) {
        $this->account_settings = $account_settings;
        $this->account_config_reader = $account_config_reader;
        $this->account_balance = $account_balance;
        $this->chargeable_users_before_corona_resolver = $chargeable_users_before_corona_resolver;
        $this->chargeable_users_resolver = $chargeable_users_resolver;
    }

    public function getValueFor(DateValue $date): ResultInterface
    {
        $account_plan = $this->account_settings->getAccountPlan();
        $payment_method = $this->account_settings->getPaymentMethod();

        return $this->produceResult(
            [
                'status' => $this->account_settings->getAccountStatus()->getStatus(),
                'status_expires_at' => $this->account_settings->getAccountStatus()->getStatusExpiresAt()->toMySQL(),
                'plan' => $account_plan->getName(),
                'billing_period' => $account_plan->getBillingPeriod(),
                'pricing_model' => $this->account_settings->getPricingModel()->getName(),
                'chargable_users_count' => $this->account_config_reader->getChargeableUsersCountValue(),
                'payment_method' => $payment_method instanceof PaymentMethodInterface ? $payment_method->getType() : null,
                'mrr_value' => $this->account_config_reader->getMrrValue(),
                'account_balance' => $this->account_balance->getRecordedBalance(),
                'discount' => $this->account_settings->getAccountStatus()->getDiscount(),
                'add_ons' => $this->account_config_reader->getAddOns(),
                'is_paid' => $this->account_config_reader->isPaid(),
                'chargeable_users_before_covid' => $this->chargeable_users_before_corona_resolver->countChargeableBeforeCoronaPandemic(),
                'chargeable_users_after_covid' => $this->chargeable_users_resolver->countChargeableUsersEliqiableForCovidDiscount(),
            ],
            $date
        );
    }
}
