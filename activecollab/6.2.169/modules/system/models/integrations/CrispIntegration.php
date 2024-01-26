<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\Utils\ConstantResolver;
use Angie\Utils\ConstantResolverInterface;

class CrispIntegration extends Integration
{
    const LIVE_CHAT_STATE = 'live_chat_state';
    const LIVE_CHAT_ENABLED = 'enabled';
    const LIVE_CHAT_DISABLED = 'disabled';

    /**
     * @var ConstantResolverInterface
     */
    private $constant_resolver;

    /**
     * @var AccountSettingsInterface
     */
    private $account_settings;

    /**
     * @param  User $user
     * @return bool
     */
    public function canView(User $user)
    {
        return !$user->isClient();
    }

    /**
     * @return bool
     */
    public function isSingleton()
    {
        return true;
    }

    /**
     * @return ConstantResolverInterface
     */
    public function getConstantResolver()
    {
        if (!$this->constant_resolver) {
            $this->constant_resolver = new ConstantResolver('CRISP_WEBSITE_ID', false);
        }

        return $this->constant_resolver;
    }

    public function getAccountSettings()
    {
        if (!$this->account_settings) {
            $this->account_settings = AngieApplication::accountSettings();
        }

        return $this->account_settings;
    }

    /**
     * @param AccountSettingsInterface $account_settings
     */
    public function setAccountSettings($account_settings)
    {
        $this->account_settings = $account_settings;
    }

    /**
     * @return string
     */
    public function getWebsiteId()
    {
        return $this->getConstantResolver()->getValueForConstant('CRISP_WEBSITE_ID');
    }

    /**
     * @param ConstantResolverInterface $constant_resolver
     */
    public function setConstantResolver($constant_resolver)
    {
        $this->constant_resolver = $constant_resolver;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return lang('Crisp chat application');
    }

    /**
     * @param  User|null $user
     * @return bool
     */
    public function isInUse(User $user = null)
    {
        return true;
    }

    /**
     * @return string
     */
    public function getShortName()
    {
        return 'crisp';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return lang('Crisp Integration');
    }

    public function isAvailableForSelfHosted() {
        return false;
    }

    /**
     * @param  User  $user
     * @return array
     */
    public function getNotifications(User $user)
    {
        return [
            CrispNotificationInterface::LIVE_CHAT_NOTIFICATION_FOR_EXISTING_USERS => AngieApplication::CrispUserNotifications($user)->resolveNotification(CrispNotificationForExistingUser::class)->getState(),
            CrispNotificationForNewUser::LIVE_CHAT_NOTIFICATION_FOR_NEW_USERS => AngieApplication::CrispUserNotifications($user)->resolveNotification(CrispNotificationForNewUser::class)->getState(),
        ];
    }

    /**
     * @return OnboardingSurveyInterface|null
     */
    public function getOnboardingData()
    {
        return AngieApplication::onboardingSurvey()->isLeadSurveySubmitted() ? AngieApplication::onboardingSurvey() : null;
    }

    /**
     * @return array
     */
    public function getAccountData()
    {
        $data = [
            'account_status' => $this->getAccountSettings()->getAccountStatus()->getVerboseStatus(),
            'is_paid' => $this->getAccountSettings()->getAccountStatus()->isPaid(),
        ];

        if ($this->getAccountSettings()->getAccountStatus()->isPaid()) {
            $data = array_merge($data, [
                'account_plan' => $this->getAccountSettings()->getAccountPlan()->getName(),
                'account_period' => $this->getAccountSettings()->getAccountPlan()->getBillingPeriod(),
                'account_verbose_status' => sprintf(
                    '%s %s %s',
                    $this->getAccountSettings()->getAccountPlan()->getName(),
                    $this->getAccountSettings()->getAccountPlan()->getBillingPeriod(),
                    $this->getAccountSettings()->getAccountStatus()->getVerboseStatus()
                ),
            ]);
        }

        return $data;
    }

    /**
     * @param  User       $user
     * @param             $crisp_status
     * @return array|null
     */
    public function getCrispData(User $user, $crisp_status)
    {
        if ($crisp_status === self::LIVE_CHAT_ENABLED) {
            $first_owner = Users::findFirstOwner();

            $account_id = AngieApplication::getAccountId();

            return [
                'account_id' => $account_id,
                'account_info_url' => 'https://activecollab.com/admin/accounts/' . $account_id,
                'full_name' => $user->getFullName(),
                'email' => $user->getEmail(),
                'system_role' => $user->getType(),
                'account_created_at' => AngieApplication::shepherdAccountConfig()->getCreatedAt($account_id)->format('Y-m-d'),
                'language' => $user->getLanguage()->getName(),
                'onbording_survey' => $this->getOnboardingData(),
                'is_first_owner' => $user->getId() === $first_owner->getId(),
                'crisp_website_id' => $this->getWebsiteId(),
            ];
        }

        return null;
    }

    /**
     * @param  User  $user
     * @return array
     */
    public function getDataForUser(User $user)
    {
        $account_status = $this->getAccountData();
        $crisp_status = $this->getLiveChatState($user);

        return array_merge([
            'crisp_status' => $crisp_status,
            'notifications' => $this->getNotifications($user),
            'crisp_data' => $this->getCrispData($user, $crisp_status),
        ], $account_status);
    }

    /**
     * @param  User   $user
     * @return string
     */
    public function enableCrisp(User $user)
    {
        return $this->setLiveChatState($user, self::LIVE_CHAT_ENABLED);
    }

    /**
     * @param  User   $user
     * @return string
     */
    public function disableCrisp(User $user)
    {
        $notification = AngieApplication::CrispUserNotifications($user)->resolveNotification(CrispNotificationForNewUser::SLUG);

        if ($notification->getState() === CrispNotificationInterface::NOTIFICATION_STATUS_DISABLED) {
            $notification->enable();
        }

        return $this->setLiveChatState($user, self::LIVE_CHAT_DISABLED);
    }

    /**
     * @param  User   $user
     * @param  string $notification_slug
     * @return mixed
     */
    public function enableNotification(User $user, $notification_slug)
    {
        return AngieApplication::CrispUserNotifications($user)->resolveNotification($notification_slug)->enable();
    }

    /**
     * @param  User   $user
     * @param  string $notification_slug
     * @return mixed
     */
    public function dismissNotification(User $user, $notification_slug)
    {
        return AngieApplication::CrispUserNotifications($user)->resolveNotification($notification_slug)->dismiss();
    }

    /**
     * @param  User   $user
     * @return string
     */
    private function getLiveChatState(User $user)
    {
        return ConfigOptions::getValueFor(self::LIVE_CHAT_STATE, $user);
    }

    /**
     * @param  User   $user
     * @param  string $live_chat_state
     * @return string
     */
    private function setLiveChatState(User $user, $live_chat_state)
    {
        ConfigOptions::setValueFor(self::LIVE_CHAT_STATE, $user, $live_chat_state);

        return $this->getLiveChatState($user);
    }
}
