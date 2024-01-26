<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\HTML;
use XeroPHP\Application\PartnerApplication;
use XeroPHP\Application\PrivateApplication;
use XeroPHP\Application\PublicApplication;
use XeroPHP\Models\Accounting\Account;
use XeroPHP\Remote\Exception\UnauthorizedException;
use XeroPHP\Remote\Request as XeroRequest;
use XeroPHP\Remote\URL as XeroURL;

/**
 * Class XeroIntegration.
 */
class XeroIntegration extends Integration
{
    const REMOTE_DATA_CACHE_TTL = 1800;

    /**
     * Returns true if this integration is singleton (can be only one integration of this type in the system).
     *
     * @return bool
     */
    public function isSingleton()
    {
        return true;
    }

    /**
     * Return tru if this integration is in use.
     *
     * @return bool
     */
    public function isInUse(User $user = null)
    {
        return $this->hasValidAccess();
    }

    /**
     * Return true if access to Xero is valid.
     *
     * @return bool
     */
    public function hasValidAccess()
    {
        if (self::isSelfHosted()) {
            return $this->isSelfHostedAuthorized();
        }

        return $this->getAccessToken() && $this->getAccessTokenSecret();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Xero';
    }

    /**
     * Return integration short name.
     *
     * @return string
     */
    public function getShortName()
    {
        return 'xero';
    }

    /**
     * Return short integration description.
     *
     * @return string
     */
    public function getDescription()
    {
        return lang('Create Xero invoices from billable time and expenses');
    }

    /**
     * Get group of this integration.
     *
     * @return string
     */
    public function getGroup()
    {
        return 'accounting';
    }

    /**
     * @return string|null
     */
    public function getAccessToken()
    {
        return $this->getAdditionalProperty('access_token');
    }

    /**
     * @param  string $access_token
     * @return mixed
     */
    public function setAccessToken($access_token)
    {
        return $this->setAdditionalProperty('access_token', $access_token);
    }

    /**
     * @return string|null
     */
    public function getAccessTokenSecret()
    {
        return $this->getAdditionalProperty('access_token_secret');
    }

    /**
     * @return string|null
     */
    public function getConsumerKey()
    {
        return defined('XERO_CONSUMER_KEY') && !self::isSelfHosted() ? XERO_CONSUMER_KEY : $this->getAdditionalProperty('consumer_key');
    }

    /**
     * @param  string      $consumer_key
     * @return string|null
     */
    public function setConsumerKey($consumer_key)
    {
        if (self::isSelfHosted()) {
            $this->setAdditionalProperty('consumer_key', $consumer_key);
        }

        return self::getConsumerKey();
    }

    /**
     * @return string|null
     */
    public function getConsumerKeySecret()
    {
        return defined('XERO_CONSUMER_KEY_SECRET') && !self::isSelfHosted() ? XERO_CONSUMER_KEY_SECRET : $this->getAdditionalProperty('consumer_key_secret');
    }

    /**
     * @param  string      $consumer_key_secret
     * @return string|null
     */
    public function setConsumerKeySecret($consumer_key_secret)
    {
        if (self::isSelfHosted()) {
            $this->setAdditionalProperty('consumer_key_secret', $consumer_key_secret);
        }

        return self::getConsumerKeySecret();
    }

    /**
     * @param  string $access_token_secret
     * @return mixed
     */
    public function setAccessTokenSecret($access_token_secret)
    {
        return $this->setAdditionalProperty('access_token_secret', $access_token_secret);
    }

    /**
     * Set request token.
     *
     * @param  string $request_token
     * @return string
     */
    public function setRequestToken($request_token)
    {
        return $this->setAdditionalProperty('request_token', $request_token);
    }

    /**
     * Get request token.
     *
     * @return string
     */
    public function getRequestToken()
    {
        return $this->getAdditionalProperty('request_token');
    }

    /**
     * Set request token secret.
     *
     * @param  string $request_token_secret
     * @return string
     */
    public function setRequestTokenSecret($request_token_secret)
    {
        return $this->setAdditionalProperty('request_token_secret', $request_token_secret);
    }

    /**
     * Get request token secret.
     *
     * @return string
     */
    public function getRequestTokenSecret()
    {
        return $this->getAdditionalProperty('request_token_secret');
    }

    /**
     * @return int|null
     */
    public function getRealmId()
    {
        return $this->getAdditionalProperty('realm_id');
    }

    /**
     * @param $value
     * @return mixed
     */
    public function setRealmId($value)
    {
        return $this->setAdditionalProperty('realm_id', $value);
    }

    /**
     * Set authorized on.
     */
    public function setAuthorizedOn()
    {
        $this->setAdditionalProperty('authorized_on', DateTimeValue::now()->getTimestamp());
    }

    /**
     * Get authorized on.
     *
     * @return DateTimeValue
     */
    public function getAuthorizedOn()
    {
        return DateTimeValue::makeFromTimestamp($this->getAdditionalProperty('authorized_on', 0));
    }

    /**
     * Set token expires on time.
     *
     * @param $how_long int time in seconds
     */
    public function setTokenExpiresOn($how_long)
    {
        $this->setAdditionalProperty('token_expires_on', DateTimeValue::now()->getTimestamp() + $how_long);
    }

    /**
     * Get token expires on time.
     *
     * @return int
     */
    public function getTokenExpiresOn()
    {
        return $this->getAdditionalProperty('token_expires_on', 0);
    }

    /**
     * Set session handle.
     *
     * @param $session_handle string
     */
    public function setSessionHandle($session_handle)
    {
        $this->setAdditionalProperty('session_handle', $session_handle);
    }

    /**
     * Get session handle.
     *
     * @return string
     */
    public function getSessionHandle()
    {
        return $this->getAdditionalProperty('session_handle', null);
    }

    /**
     * Return if token has expired.
     *
     * @return bool
     */
    public function isTokenExpired()
    {
        if (self::isSelfHosted()) {
            return false;
        }

        return $this->getTokenExpiresOn() < DateTimeValue::now()->getTimestamp() + 100;
    }

    /**
     * @return string
     */
    private function getCallbackUrl()
    {
        return ROOT_URL . '/integrations/xero';
    }

    /**
     * @return string
     */
    public function getRsaPrivateKey()
    {
        return defined('XERO_RSA_PRIVATE_KEY') && !self::isSelfHosted() ? XERO_RSA_PRIVATE_KEY : AngieApplication::fileLocationToPath($this->getAdditionalProperty('rsa_private_key'));
    }

    /**
     * @param  string $rsa_private_key
     * @return string
     */
    public function setRsaPrivateKey($rsa_private_key)
    {
        if (self::isSelfHosted()) {
            $file = UploadedFiles::findByCode($rsa_private_key);
            if ($file instanceof UploadedFile) {
                $this->setAdditionalProperty('rsa_private_key', $file->getLocation());
                $file->keepFileOnDelete(true);
                $file->delete();
            }
        }

        return $this->getRsaPrivateKey();
    }

    /**
     * Return certificate file.
     *
     * @return string
     */
    public function getCertificateFile()
    {
        return defined('XERO_CERTIFICATE_FILE') && !self::isSelfHosted() ? XERO_CERTIFICATE_FILE : AngieApplication::fileLocationToPath($this->getAdditionalProperty('certificate_file'));
    }

    /**
     * @param $certificate_file
     * @return string
     */
    public function setCertificateFile($certificate_file)
    {
        if (self::isSelfHosted()) {
            $file = UploadedFiles::findByCode($certificate_file);
            if ($file instanceof UploadedFile) {
                $this->setAdditionalProperty('certificate_file', $file->getLocation());
                $file->keepFileOnDelete(true);
                $file->delete();
            }
        }

        return self::getCertificateFile();
    }

    /**
     * Return if is require security certificate verification.
     *
     * @return mixed
     */
    public function getSslVerifyPeer()
    {
        return defined('XERO_SSL_VERIFY_PEER') && !self::isSelfHosted() ? XERO_SSL_VERIFY_PEER : $this->getAdditionalProperty('ssl_verify_peer');
    }

    /**
     * Set if is require security certificate verification.
     *
     * @param $ssl_verify_peer
     * @return mixed
     */
    public function setSslVerifyPeer($ssl_verify_peer)
    {
        if (self::isSelfHosted()) {
            $this->setAdditionalProperty('ssl_verify_peer', $ssl_verify_peer);
        }

        return $this->getSslVerifyPeer();
    }

    /**
     * @return string
     */
    public function getEntrustSslCert()
    {
        return defined('XERO_ENTRUST_SSL_CERT') ? XERO_ENTRUST_SSL_CERT : '';
    }

    /**
     * @return string
     */
    public function getEntrustSslKey()
    {
        return defined('XERO_ENTRUST_SSL_KEY') ? XERO_ENTRUST_SSL_KEY : '';
    }

    /**
     * @return string
     */
    public function getEntrustSslKeyPassword()
    {
        return defined('XERO_ENTRUST_SSL_KEY_PASSWORD') ? XERO_ENTRUST_SSL_KEY_PASSWORD : '';
    }

    /**
     * @return mixed
     */
    public function getOrganizationName()
    {
        return $this->getAdditionalProperty('xero_organization_name');
    }

    /**
     * @param $organization_name
     */
    public function setOrganizationName($organization_name)
    {
        $this->setAdditionalProperty('xero_organization_name', $organization_name);
    }

    /**
     * @return mixed
     */
    public function getOrganizationShortCode()
    {
        return $this->getAdditionalProperty('xero_organization_short_code');
    }

    /**
     * @param $organization_short_code
     */
    public function setOrganizationShortCode($organization_short_code)
    {
        $this->setAdditionalProperty('xero_organization_short_code', $organization_short_code);
    }

    /**
     * @return mixed (Xero Account Code)
     */
    public function getDefaultXeroAccount()
    {
        return $this->getAdditionalProperty('default_xero_account');
    }

    /**
     * Set default xero account (Xero Account Code).
     *
     * @param string $value
     */
    public function setDefaultXeroAccount($value)
    {
        $this->setAdditionalProperty('default_xero_account', $value);
    }

    /**
     * Return value how taxes are represented.
     *
     * @return string
     */
    public function getShowTaxesAs()
    {
        return $this->getAdditionalProperty('show_taxes_as', \XeroPHP\Models\Accounting\Invoice::LINEAMOUNT_TYPE_EXCLUSIVE);
    }

    /**
     * Set hove taxes are represented.
     *
     * @param string $value
     */
    public function setShowTaxesAs($value)
    {
        $this->setAdditionalProperty('show_taxes_as', $value);
    }

    /**
     * @var \XeroPHP\Application
     */
    private $data_service;

    /**
     * Return data service.
     *
     * @return \XeroPHP\Application
     */
    public function dataService()
    {
        if (!$this->data_service instanceof XeroPHP\Application) {
            if (self::isSelfHosted()) {
                $this->data_service = new PrivateApplication([
                    'oauth' => [
                        'callback' => $this->getCallbackUrl(),
                        'consumer_key' => $this->getConsumerKey(),
                        'consumer_secret' => $this->getConsumerKeySecret(),
                        'rsa_private_key' => 'file://' . $this->getRsaPrivateKey(),
                    ],
                    'curl' => [
                        CURLOPT_CAINFO => $this->getCertificateFile(),
                        CURLOPT_SSL_VERIFYPEER => $this->getSslVerifyPeer(),
                        CURLOPT_USERAGENT => 'Active Collab',
                    ],
                ]);
            } else {
                $this->data_service = new PartnerApplication([
                    'oauth' => [
                        'callback' => $this->getCallbackUrl(),
                        'consumer_key' => $this->getConsumerKey(),
                        'consumer_secret' => $this->getConsumerKeySecret(),
                        'rsa_private_key' => 'file://' . $this->getRsaPrivateKey(),
                        'signature_location' => \XeroPHP\Remote\OAuth\Client::SIGN_LOCATION_QUERY,
                    ],
                    'curl' => [
                        CURLOPT_CAINFO => $this->getCertificateFile(),
                        CURLOPT_SSLCERT => $this->getEntrustSslCert(),
                        CURLOPT_SSLKEY => $this->getEntrustSslKey(),
                        CURLOPT_SSLKEYPASSWD => $this->getEntrustSslKeyPassword(),
                        CURLOPT_USERAGENT => 'Active Collab',
                    ],
                ]);

                if ($this->getAccessToken() && $this->getAccessTokenSecret()) {
                    $this->data_service->getOAuthClient()
                        ->setToken($this->getAccessToken())
                        ->setTokenSecret($this->getAccessTokenSecret());
                }
            }
        }

        return $this->data_service;
    }

    /**
     * Method use to refresh token from session handle.
     */
    private function refreshToken()
    {
        try {
            $data_service = $this->dataService();

            $data_service->getOAuthClient()
                ->setToken(null)
                ->setTokenSecret(null);

            $url = new XeroURL($data_service, XeroURL::OAUTH_ACCESS_TOKEN);
            $request = new XeroRequest($data_service, $url);

            $request->setParameter('oauth_token', $this->getAccessToken());
            $request->setParameter('oauth_session_handle', $this->getSessionHandle());

            $request->send();
            $oauth_response = $request->getResponse()->getOAuthResponse();

            $this->setTokenExpiresOn(intval($oauth_response['oauth_expires_in']));
            $this->setAccessToken($oauth_response['oauth_token']);
            $this->setAccessTokenSecret($oauth_response['oauth_token_secret']);
            $this->setSessionHandle($oauth_response['oauth_session_handle']);
            $this->save();

            // reset data service
            $this->data_service = null;
        } catch (UnauthorizedException $e) {
            $this->delete();
            throw $e;
        }
    }

    /**
     * @param \XeroPHP\Models\Accounting\Organisation $organization
     */
    public function loadOrganizationParam(XeroPHP\Models\Accounting\Organisation $organization)
    {
        $this->setOrganizationName($organization->getName());
        $this->setOrganizationShortCode($organization->getShortCode());
    }

    /**
     * Return is self hosted app authorized with xero app.
     *
     * @return bool
     */
    public function isSelfHostedAuthorized()
    {
        return $this->getAdditionalProperty('is_authorized', false);
    }

    /**
     * Authorize with Xero.
     *
     * @param  array     $params
     * @return $this
     * @throws Exception
     */
    public function authorize(array $params)
    {
        try {
            DB::beginWork('Begin: authorize xero application @ ' . __CLASS__);

            $data_service = $this->dataService();

            if ($this->isSelfHosted()) {
                $this->setRealmId(rand(1000, 9999));
                $this->setAdditionalProperty('is_authorized', true);
            } else {
                $oauth_token = array_var($params, 'oauth_token');
                $oauth_verifier = array_var($params, 'oauth_verifier');

                if (empty($oauth_token) || empty($oauth_verifier)) {
                    throw new Exception(lang('Invalid OAuth credentials!'));
                }

                $data_service->getOAuthClient()
                    ->setToken($oauth_token)
                    ->setTokenSecret($this->getRequestTokenSecret());

                $data_service->getOAuthClient()->setVerifier($oauth_verifier);

                $url = new XeroURL($this->dataService(), XeroURL::OAUTH_ACCESS_TOKEN);
                $request = new XeroRequest($this->dataService(), $url);

                $request->send();
                $oauth_response = $request->getResponse()->getOAuthResponse();

                $this->setAccessToken($oauth_response['oauth_token']);
                $this->setAccessTokenSecret($oauth_response['oauth_token_secret']);
                $this->setTokenExpiresOn(intval($oauth_response['oauth_expires_in']));

                if (!empty($params['realmId'])) {
                    $this->setRealmId($params['realmId']);
                }

                if (!empty($oauth_response['oauth_session_handle'])) {
                    $this->setSessionHandle($oauth_response['oauth_session_handle']);
                }

                $this->setAuthorizedOn();

                $data_service->getOAuthClient()
                    ->setToken($this->getAccessToken())
                    ->setTokenSecret($this->getAccessTokenSecret());
            }

            $organisations = $this->loadData(\XeroPHP\Models\Accounting\Organisation::class);

            if (count($organisations)) {
                $this->loadOrganizationParam($organisations[0]);
            } else {
                throw new Exception(lang('Missing Xero organization'));
            }

            $this->save();

            AngieApplication::memories()->forget('xero_companies');
            AngieApplication::memories()->forget('xero_accounts');

            ConfigOptions::setValue('default_accounting_app', 'xero');

            DB::commit('Done: authorize xero application @ ' . __CLASS__);

            return $this;
        } catch (Exception $e) {
            DB::rollback('Rollback: authorize xero application @ ' . __CLASS__);
            AngieApplication::log()->error('Autorizing xero integration failed', ['exception' => $e]);
            $this->delete(); // force delete if authorization failed
            throw $e;
        }
    }

    /**
     * Get request url.
     *
     * @return string
     */
    public function getRequestUrl()
    {
        $this->dataService()->getOAuthClient()
            ->setToken(null)
            ->setTokenSecret(null);

        $url = new XeroURL($this->dataService(), XeroURL::OAUTH_REQUEST_TOKEN);
        $request = new XeroRequest($this->dataService(), $url);
        $request->send();
        $oauth_response = $request->getResponse()->getOAuthResponse();

        Integrations::update($this, [
            'request_token' => $oauth_response['oauth_token'],
            'request_token_secret' => $oauth_response['oauth_token_secret'],
        ]);

        return $this->dataService()->getAuthorizeURL($oauth_response['oauth_token']);
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $result = parent::jsonSerialize();

        if (self::isSelfHosted()) {
            $result['consumer_key'] = $this->getConsumerKey();
            $result['consumer_key_secret'] = $this->getConsumerKeySecret();
        } else {
            if ($this->isTokenExpired() && $this->data_service instanceof PublicApplication) {
                $result['reconnect_url'] = $this->getRequestUrl();
            }
        }
        $result['xero_organization_name'] = $this->getOrganizationName();
        $result['xero_organization_short_code'] = $this->getOrganizationShortCode();

        $result['has_valid_access'] = $this->hasValidAccess();
        $result['default_xero_account'] = $this->getDefaultXeroAccount();
        $result['show_taxes_as'] = $this->getShowTaxesAs();

        return $result;
    }

    /**
     * Return new entity instance.
     *
     * @param  array                              $attributes
     * @return \XeroPHP\Models\Accounting\Invoice
     * @throws Exception
     */
    public function createInvoice(array $attributes = [])
    {
        $this->checkAccessToken();

        $invoice = new XeroPHP\Models\Accounting\Invoice($this->dataService());
        $invoice->setType(\XeroPHP\Models\Accounting\Invoice::INVOICE_TYPE_ACCREC);
        $invoice->setLineAmountType($this->getShowTaxesAs());
        $invoicing_default_due = ConfigOptions::getValue('invoicing_default_due', 0);
        $timestamp = DateTimeValue::now()->addDays($invoicing_default_due)->getTimestamp();
        $invoice->setDueDate(new \DateTime('@' . $timestamp));

        $client_id = isset($attributes['client_id']) ? $attributes['client_id'] : '';
        $is_client_load_by_name = isset($attributes['is_client_load_by_name']) ? $attributes['is_client_load_by_name'] : 0;

        if ($client_id > '') {
            $contact = new XeroPHP\Models\Accounting\Contact($this->dataService());
            if ($is_client_load_by_name == 1) {
                $contact->setName($client_id);
            } else {
                $contact->setContactID($client_id);
            }
            $invoice->setContact($contact);
        }

        $items = isset($attributes['items']) ? $attributes['items'] : [];
        foreach ($items as $key => $item_attributes) {
            $unit_cost = isset($item_attributes['unit_cost']) ? $item_attributes['unit_cost'] : null;
            $quantity = isset($item_attributes['quantity']) ? $item_attributes['quantity'] : null;
            $description = isset($item_attributes['description']) ? $item_attributes['description'] : '';

            if ($unit_cost === null || $quantity === null) {
                continue;
            }

            $line_item = new XeroPHP\Models\Accounting\Invoice\LineItem();
            $line_item->setLineAmount($unit_cost * $quantity);
            $line_item->setDescription($description);
            $line_item->setQuantity($quantity);
            $line_item->setUnitAmount($unit_cost);
            $line_item->setAccountCode($this->getDefaultXeroAccount());

            $invoice->addLineItem($line_item);
        }

        if (!count($invoice->getLineItems())) {
            throw new \Exception('No items attached to invoice');
        }

        try {
            $this->dataService()->save($invoice, true);
        } catch (UnauthorizedException $e) {
            $this->delete();
            throw $e;
        }

        return $invoice;
    }

    /**
     * Return collection.
     *
     * @param  string                 $entity_name
     * @param  array                  $ids
     * @param  bool                   $use_cache
     * @param  string                 $where
     * @param  int                    $sync_timestamp
     * @return \XeroPHP\Remote\Object
     */
    public function fetch($entity_name, array $ids = [], $use_cache = true, $where = null, $sync_timestamp = null)
    {
        $this->checkAccessToken();

        return AngieApplication::cache()->getByObject($this, ['xero', $this->getRealmId(), $entity_name], function () use ($entity_name, $ids, $where, $sync_timestamp) {
            $result = [];
            $namespace = explode('\\', $entity_name);
            $id_column_name = 'get' . $namespace[3] . 'ID';

            $page = 1;

            do {
                $xero_collection = $this->loadData($entity_name, $page, $where, $sync_timestamp);

                foreach ($xero_collection as $xero_entity) {
                    if (!empty($ids)) {
                        if (!in_array($xero_entity->{$id_column_name}(), $ids)) {
                            continue;
                        }
                    }
                    $result[] = $xero_entity;
                }
                ++$page;
            } while (count($xero_collection) == 100);

            return $result;
        }, empty($use_cache), self::REMOTE_DATA_CACHE_TTL);
    }

    /**
     * @return array
     */
    public function getCompanies()
    {
        $data = AngieApplication::memories()->get('xero_companies', ['timestamp' => 0, 'companies' => []]);

        if ($this->hasValidAccess() && !$this->isTokenExpired()) {
            $new_compaies = $this->loadData(\XeroPHP\Models\Accounting\Contact::class, 0, null, $data['timestamp']);

            foreach ($new_compaies as $new_company) {
                $data['companies'][$new_company->getContactID()] = $new_company->getName();
            }

            AngieApplication::memories()->set('xero_companies', [
                'timestamp' => DateTimeValue::now()->getTimestamp(),
                'companies' => $data['companies'],
            ]);
        }

        return $data['companies'];
    }

    /**
     * Return accounts.
     *
     * @return array
     */
    public function getAccounts()
    {
        $data = AngieApplication::memories()->get('xero_accounts', ['timestamp' => 0, 'accounts' => []]);

        if ($this->hasValidAccess() && !$this->isTokenExpired()) {
            $new_data = $this->loadData(\XeroPHP\Models\Accounting\Account::class, 0, null, $data['timestamp']);

            foreach ($new_data as $account_entity) {
                if ($account_entity->getType() != Account::ACCOUNT_TYPE_REVENUE) {
                    continue;
                }

                $data['accounts'][$account_entity->getAccountID()] = [
                    'code' => $account_entity->getCode(),
                    'name' => $account_entity->getName(),
                ];
            }

            AngieApplication::memories()->set('xero_accounts', [
                'timestamp' => DateTimeValue::now()->getTimestamp(),
                'accounts' => $data['accounts'],
            ]);
        }

        return array_values($data['accounts']);
    }

    /**
     * @param  string                 $entity_name
     * @param  string                 $id
     * @return \XeroPHP\Remote\Object
     */
    public function loadById($entity_name, $id)
    {
        $this->checkAccessToken();

        return $this->dataService()->loadByGUID($entity_name, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($bulk = false)
    {
        try {
            DB::beginWork('Begin: delete integration @' . __CLASS__);

            parent::delete($bulk);

            ConfigOptions::setValue('default_accounting_app', null);
            AngieApplication::cache()->removeByObject($this);

            DB::commit('Done: delete integration @' . __CLASS__);
        } catch (Exception $e) {
            DB::rollback('Rollback: delete integration @' . __CLASS__);
            throw $e;
        }
    }

    // ---------------------------------------------------
    //  Permissions
    // ---------------------------------------------------

    /**
     * Only owner can view Xero integration settings.
     *
     * @param  User $user
     * @return bool
     */
    public function canView(User $user)
    {
        return $user->isOwner();
    }

    /**
     * Only owners can update Xero integration settings.
     *
     * @param  User $user
     * @return bool
     */
    public function canEdit(User $user)
    {
        return $user->isOwner();
    }

    /**
     * Only owners can drop Xero integration settings.
     *
     * @param  User $user
     * @return bool
     */
    public function canDelete(User $user)
    {
        return $user->isOwner();
    }

    /**
     * @return bool
     */
    private static function isSelfHosted()
    {
        return !AngieApplication::isOnDemand();
    }

    /**
     * Request data from xero.
     *
     * @param  string                    $entity_name
     * @param  int                       $page
     * @param  string                    $where
     * @param  int                       $modified_after
     * @return XeroPHP\Remote\Collection
     */
    public function loadData($entity_name, $page = 0, $where = null, $modified_after = null)
    {
        try {
            $query = $this->dataService()->load($entity_name);

            if ($where) {
                $query->where($where);
            }

            if ($page) {
                $query->page($page);
            }

            if ($modified_after) {
                $query->modifiedAfter(new DateTime('@' . $modified_after));
            }

            return $query->execute();
        } catch (UnauthorizedException $e) {
            $this->delete();
            throw $e;
        }
    }

    /**
     * Return status of access token.
     *
     * @throw UnauthorizedException
     */
    private function checkAccessToken()
    {
        if ($this->isTokenExpired()) {
            $data_service = $this->dataService();

            if ($data_service instanceof PartnerApplication) {
                $this->refreshToken();
            } elseif ($data_service instanceof PublicApplication) {
                $message = lang('The access token has expired.') . ' ';
                $message .= HTML::openTag('a', ['href' => $this->getRequestUrl()], lang('Reconnect'));
                throw new UnauthorizedException($message);
            }
        }
    }
}
