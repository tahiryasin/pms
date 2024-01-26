<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Quickbooks\Data\Entity;
use ActiveCollab\Quickbooks\DataService\OAuth2Client;
use ActiveCollab\Quickbooks\DataService\ResourceClient;
use ActiveCollab\Quickbooks\Quickbooks;
use League\OAuth1\Client\Credentials\TemporaryCredentials;
use League\OAuth1\Client\Credentials\TokenCredentials;

/**
 * Class QuickbooksIntegration.
 */
class QuickbooksIntegration extends Integration
{
    const REMOTE_DATA_CACHE_TTL = 86400;
    const DAYS_BEFORE_ACCESS_TOKEN_EXPIRE = 180;
    const MIN_DAYS_TO_RECONNECT = 40;

    const AUTHORIZATION_TYPE_OAUTH_1 = 'oauth1';
    const AUTHORIZATION_TYPE_OAUTH_2 = 'oauth2';

    protected $oauth;
    protected $data_service;

    public function isSingleton()
    {
        return true;
    }

    public function isInUse(User $user = null)
    {
        return $this->hasValidAccess();
    }

    public function hasValidAccess()
    {
        if ($this->isOAuth2Authorization()) {
            return $this->getAccessToken() && $this->getRefreshToken() && !$this->isAccessTokenExpired();
        } else {
            return $this->getAccessToken() && $this->getAccessTokenSecret() && !$this->isAccessTokenExpired();
        }
    }

    public function getName()
    {
        return 'QuickBooks';
    }

    public function getShortName()
    {
        return 'quickbooks';
    }

    public function getDescription()
    {
        return lang('Create QuickBooks invoices from billable time and expenses');
    }

    public function getGroup()
    {
        return 'accounting';
    }

    public function setAuthorizationType($authorization_type)
    {
        return $this->setAdditionalProperty('authorization_type', $authorization_type);
    }

    public function getAuthorizationType()
    {
        return $this->getAdditionalProperty('authorization_type', self::AUTHORIZATION_TYPE_OAUTH_1);
    }

    public function isOAuth2Authorization()
    {
        return $this->getAuthorizationType() === self::AUTHORIZATION_TYPE_OAUTH_2;
    }

    public function getAccessToken()
    {
        return $this->getAdditionalProperty('access_token');
    }

    public function setAccessToken($access_token)
    {
        return $this->setAdditionalProperty('access_token', $access_token);
    }

    public function getAccessTokenSecret()
    {
        return $this->getAdditionalProperty('access_token_secret');
    }

    public function setAccessTokenSecret($access_token_secret)
    {
        return $this->setAdditionalProperty('access_token_secret', $access_token_secret);
    }

    public function getAccessTokenExpiration()
    {
        return $this->getAdditionalProperty('access_token_expiration');
    }

    public function setAccessTokenExpiration($access_token_expiration)
    {
        return $this->setAdditionalProperty('access_token_expiration', $access_token_expiration);
    }

    public function getRefreshToken()
    {
        return $this->getAdditionalProperty('refresh_token');
    }

    public function setRefreshToken($refresh_token)
    {
        return $this->setAdditionalProperty('refresh_token', $refresh_token);
    }

    public function getRefreshTokenExpiration()
    {
        return $this->getAdditionalProperty('refresh_token_expiration');
    }

    public function setRefreshTokenExpiration($refresh_token_expiration)
    {
        return $this->setAdditionalProperty('refresh_token_expiration', $refresh_token_expiration);
    }

    public function getRequestToken()
    {
        return $this->getAdditionalProperty('request_token');
    }

    public function setRequestToken($request_token)
    {
        return $this->setAdditionalProperty('request_token', $request_token);
    }

    public function getRequestTokenSecret()
    {
        return $this->getAdditionalProperty('request_token_secret');
    }

    public function setRequestTokenSecret($request_token_secret)
    {
        return $this->setAdditionalProperty('request_token_secret', $request_token_secret);
    }

    public function getConsumerKey()
    {
        return defined('QUICKBOOKS_CONSUMER_KEY') && AngieApplication::isOnDemand()
            ? QUICKBOOKS_CONSUMER_KEY
            : $this->getAdditionalProperty('consumer_key');
    }

    public function setConsumerKey($consumer_key)
    {
        if (!AngieApplication::isOnDemand()) {
            $this->setAdditionalProperty('consumer_key', $consumer_key);
        }

        return self::getConsumerKey();
    }

    public function getConsumerKeySecret()
    {
        return defined('QUICKBOOKS_CONSUMER_KEY_SECRET') && AngieApplication::isOnDemand()
            ? QUICKBOOKS_CONSUMER_KEY_SECRET
            : $this->getAdditionalProperty('consumer_key_secret');
    }

    public function setConsumerKeySecret($consumer_key_secret)
    {
        if (!AngieApplication::isOnDemand()) {
            $this->setAdditionalProperty('consumer_key_secret', $consumer_key_secret);
        }

        return self::getConsumerKeySecret();
    }

    public function getRealmId()
    {
        return $this->getAdditionalProperty('realm_id');
    }

    public function setRealmId($value)
    {
        return $this->setAdditionalProperty('realm_id', $value);
    }

    public function setAuthorizedOn()
    {
        $this->setAdditionalProperty('authorized_on', DateTimeValue::now()->getTimestamp());
    }

    public function getAuthorizedOn()
    {
        return DateTimeValue::makeFromTimestamp($this->getAdditionalProperty('authorized_on', 0));
    }

    private function getCallbackUrl()
    {
        return ROOT_URL . '/integrations/quickbooks';
    }

    private function getTokenCredentials()
    {
        $tokenCredentials = new TokenCredentials();

        $tokenCredentials->setIdentifier($this->getAccessToken());
        $tokenCredentials->setSecret($this->getAccessTokenSecret());

        return $tokenCredentials;
    }

    public function dataService()
    {
        if (empty($this->data_service)) {
            if ($this->isOAuth2Authorization()) {
                $this->data_service = new ResourceClient(
                    $this->getConsumerKey(),
                    $this->getConsumerKeySecret(),
                    AngieApplication::isInDevelopment() ? 'Development' : 'Production',
                    $this->getAccessToken(),
                    $this->getRefreshToken(),
                    $this->getRealmId()
                );
            } else {
                $data_service = 'ActiveCollab\\Quickbooks\\' . (AngieApplication::isInDevelopment() ? 'Sandbox' : 'DataService');

                $this->data_service = new $data_service(
                    $this->getConsumerKey(),
                    $this->getConsumerKeySecret(),
                    $this->getAccessToken(),
                    $this->getAccessTokenSecret(),
                    $this->getRealmId()
                );
            }
        }

        return $this->data_service;
    }

    public function oauth()
    {
        if (empty($this->oauth)) {
            if ($this->isOAuth2Authorization()) {
                $this->oauth = new OAuth2Client(
                    $this->getConsumerKey(),
                    $this->getConsumerKeySecret(),
                    $this->getCallbackUrl(),
                    AngieApplication::isInDevelopment() ? 'Development' : 'Production'
                );
            } else {
                $this->oauth = new Quickbooks([
                    'identifier' => $this->getConsumerKey(),
                    'secret' => $this->getConsumerKeySecret(),
                    'callback_uri' => $this->getCallbackUrl(),
                ]);
            }
        }

        return $this->oauth;
    }

    public function authorize(array $params)
    {
        return $this->isOAuth2Authorization()
            ? $this->authorizeOAuth2($params)
            : $this->authorizeOAuth1($params);
    }

    private function authorizeOAuth1(array $params)
    {
        $oauth_token = array_var($params, 'oauth_token');
        $oauth_verifier = array_var($params, 'oauth_verifier');

        $temp_credentials = new TemporaryCredentials();
        $temp_credentials->setIdentifier($this->getRequestToken());
        $temp_credentials->setSecret($this->getRequestTokenSecret());

        try {
            DB::beginWork('Begin: authorize quickbooks application @ ' . __CLASS__);

            $token_credentials = $this->oauth()->getTokenCredentials($temp_credentials, $oauth_token, $oauth_verifier);

            $this->setAccessToken($token_credentials->getIdentifier());
            $this->setAccessTokenSecret($token_credentials->getSecret());
            $this->setRealmId(array_var($params, 'realmId'));
            $this->setAuthorizedOn();
            $this->save();

            ConfigOptions::setValue('default_accounting_app', 'quickbooks');

            DB::commit('Done: authorize quickbooks application @ ' . __CLASS__);
        } catch (Exception $e) {
            DB::rollback('Rollback: authorize quickbooks application @ ' . __CLASS__);
            throw $e;
        }

        return $this;
    }

    private function authorizeOAuth2(array $params)
    {
        $code = array_var($params, 'code');
        $realm_id = array_var($params, 'realmId');

        try {
            DB::beginWork('Begin: authorize quickbooks application @ ' . __CLASS__);

            $access_token_obj = $this->oauth()->getAuthorizationToken($code, $realm_id);

            $access_token_obj->getAccessTokenExpiresAt();

            $this->setAccessToken($access_token_obj->getAccessToken());
            $this->setAccessTokenExpiration($access_token_obj->getAccessTokenExpiresAt());
            $this->setRefreshToken($access_token_obj->getRefreshToken());
            $this->setRefreshTokenExpiration($access_token_obj->getRefreshTokenExpiresAt());
            $this->setRealmId($realm_id);
            $this->setAuthorizedOn();
            $this->save();

            ConfigOptions::setValue('default_accounting_app', 'quickbooks');

            DB::commit('Done: authorize quickbooks application @ ' . __CLASS__);
        } catch (Exception $e) {
            DB::rollback('Rollback: authorize quickbooks application @ ' . __CLASS__);
            throw $e;
        }

        return $this;
    }

    public function getRequestUrl()
    {
        try {
            if ($this->isOAuth2Authorization()) {
                return $this->oauth()->getAuthorizationUrl();
            } else {
                $temp_credentials = $this->oauth()->getTemporaryCredentials();

                $this->setRequestToken($temp_credentials->getIdentifier());
                $this->setRequestTokenSecret($temp_credentials->getSecret());
                $this->save();

                return $this->oauth()->getAuthorizationUrl($temp_credentials);
            }
        } catch (Exception $e) {
            throw new Exception(lang("Can't connect - please check your customer keys."));
        }
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $result = parent::jsonSerialize();

        if (!AngieApplication::isOnDemand()) {
            $result['consumer_key'] = $this->getConsumerKey();
            $result['consumer_key_secret'] = $this->getConsumerKeySecret();
        }

        $result['has_valid_access'] = $this->hasValidAccess();
        $result['realm_id'] = $this->getRealmId();

        return $result;
    }

    /**
     * Return new entity instance.
     *
     * @param  array                                $attributes
     * @return \ActiveCollab\Quickbooks\Data\Entity
     * @throws Exception
     */
    public function createInvoice(array $attributes = [])
    {
        $items = isset($attributes['items']) ? $attributes['items'] : [];
        $client_id = isset($attributes['client_id']) ? $attributes['client_id'] : 0;

        $data = [
            'Line' => [],
            'CustomerRef' => [
                'value' => $client_id,
            ],
            'GlobalTaxCalculation' => 'NotApplicable',
        ];

        $preferences = $this->getPreferences();

        if (isset($preferences['SalesFormsPrefs']['CustomTxnNumbers']) && $preferences['SalesFormsPrefs']['CustomTxnNumbers']) {
            $data['DocNumber'] = $this->getNextInvoiceDocNumber();
        }

        if (isset($preferences['SalesFormsPrefs'])) {
            $sales_forms_prefs = $preferences['SalesFormsPrefs'];
            if (isset($sales_forms_prefs['DefaultTerms']) && isset($sales_forms_prefs['DefaultTerms']['value'])) {
                $data['SalesTermRef'] = [
                    'value' => $sales_forms_prefs['DefaultTerms']['value'],
                ];
            }
        }

        $client = $this->dataService()->setEntity('Customer')->read($client_id);

        if ($client instanceof Entity) {
            $client_raw_data = $client->getRawData();

            if (isset($client_raw_data['PrimaryEmailAddr']) && isset($client_raw_data['PrimaryEmailAddr']['Address'])) {
                $data['BillEmail'] = [
                    'Address' => $client_raw_data['PrimaryEmailAddr']['Address'],
                ];
            }

            if (isset($client_raw_data['SalesTermRef']) && isset($client_raw_data['SalesTermRef']['value'])) {
                $data['SalesTermRef'] = [
                    'value' => $client_raw_data['SalesTermRef']['value'],
                ];
            }
        }

        foreach ($items as $key => $item_attributes) {
            $unit_cost = isset($item_attributes['unit_cost']) ? $item_attributes['unit_cost'] : null;
            $quantity = isset($item_attributes['quantity']) ? $item_attributes['quantity'] : null;
            $description = isset($item_attributes['description']) ? $item_attributes['description'] : '';

            if ($unit_cost === null || $quantity === null) {
                continue;
            }

            $line = [
                'Amount' => $unit_cost * $quantity,
                'DetailType' => 'SalesItemLineDetail',
                'Description' => $description,
                'SalesItemLineDetail' => [
                    'Qty' => $quantity,
                    'UnitPrice' => $unit_cost,
                ],
            ];

            if (isset($attributes['line_num'])) {
                $line['LineNum'] = $attributes['line_num'];
            }

            $data['Line'][] = $line;
        }

        if (!count($data['Line'])) {
            throw new Exception('No items attached to invoice');
        }

        return $this->dataService()->setEntity('Invoice')->create($data);
    }

    /**
     * Return collection.
     *
     * @param  string                                      $entity_name
     * @param  array                                       $ids
     * @param  bool                                        $use_cache
     * @return \ActiveCollab\Quickbooks\Data\QueryResponse
     */
    public function fetch($entity_name, array $ids = [], $use_cache = true)
    {
        return AngieApplication::cache()->getByObject($this, ['quickbooks', $this->getRealmId(), $entity_name], function () use ($entity_name, $ids) {
            $this->dataService()->setEntity($entity_name);
            $query = "select * from {$entity_name}";

            if (!empty($ids)) {
                $ids = implode(',', array_map(
                    function ($id) {
                        return "'" . (string) $id . "'";
                    },
                    $ids
                ));
                $query .= ' where id in (' . $ids . ')';
            }

            $query .= ' STARTPOSITION 1 MAXRESULTS 1000';

            return $this->dataService()->query($query);
        }, empty($use_cache), self::REMOTE_DATA_CACHE_TTL);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($bulk = false)
    {
        // can't disconnect qb that's fine, leave it
        if (!$this->isAccessTokenExpired()) {
            try {
                if ($this->isOAuth2Authorization()) {
                    $this->oauth()->revokeAccessToken($this->getAccessToken());
                } else {
                    $this->oauth()->disconnect($this->getTokenCredentials());
                }
            } catch (\Exception $e) {
                unset($e);
            }
        }

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

    public function isAccessTokenExpired()
    {
        return $this->getAuthorizedOn()->daysBetween(DateValue::now()) > self::DAYS_BEFORE_ACCESS_TOKEN_EXPIRE;
    }

    public function needReconnect()
    {
        $days = $this->getAuthorizedOn()->daysBetween(DateValue::now());

        return $days >= self::MIN_DAYS_TO_RECONNECT && $days <= self::DAYS_BEFORE_ACCESS_TOKEN_EXPIRE;
    }

    public function reconnect()
    {
        return $this->isOAuth2Authorization()
            ? $this->reconnectOAuth2()
            : $this->reconnectOAuth1();
    }

    private function reconnectOAuth1()
    {
        $response = $this->oauth()->reconnect($this->getTokenCredentials());

        if ($response->hasError()) {
            throw new Exception($response->getErrorMessage());
        }

        $this->setAccessToken($response->getOAuthToken());
        $this->setAccessTokenSecret($response->getOAuthTokenSecret());
        $this->setAuthorizedOn();
        $this->save();

        return $this;
    }

    private function reconnectOAuth2()
    {
        try {
            $access_token_obj = $this->oauth()->refreshAccessToken($this->getRefreshToken());

            $this->setAccessToken($access_token_obj->getAccessToken());
            $this->setAccessTokenExpiration($access_token_obj->getAccessTokenExpiresAt());
            $this->setRefreshToken($access_token_obj->getRefreshToken());
            $this->setRefreshTokenExpiration($access_token_obj->getRefreshTokenExpiresAt());
            $this->setAuthorizedOn();
            $this->save();

            return $this;
        } catch (\Exception $e) {
            AngieApplication::log()->error(
                'Quickbooks reconnect failed wiht error {error}',
                [
                    'error' => $e->getMessage(),
                ]
            );

            throw new Exception($e);
        }
    }

    // ---------------------------------------------------
    //  Permissions
    // ---------------------------------------------------

    /**
     * Only owner can view Quickbooks integration settings.
     *
     * @param  User $user
     * @return bool
     */
    public function canView(User $user)
    {
        return $user->isOwner();
    }

    /**
     * Only owners can update Quickbooks integration settings.
     *
     * @param  User $user
     * @return bool
     */
    public function canEdit(User $user)
    {
        return $user->isOwner();
    }

    /**
     * Only owners can drop Quickbooks integration settings.
     *
     * @param  User $user
     * @return bool
     */
    public function canDelete(User $user)
    {
        return $user->isOwner();
    }

    /**
     * Return QB account preferences.
     *
     * @return array
     */
    public function getPreferences()
    {
        return $this->dataService()->query('select * from Preferences')->getIterator()[0]->getRawData();
    }

    /**
     * Return next QB invoice DocNumber value.
     *
     * @return string
     */
    public function getNextInvoiceDocNumber()
    {
        // no option to be one query :(
        $invoice_data = $this->getLastDocumentNumberFromTable('Invoice');
        $creditmemo_data = $this->getLastDocumentNumberFromTable('Creditmemo');

        $number = $invoice_data['DocNumber'];

        if ($creditmemo_data['Id'] > $invoice_data['Id']) {
            $number = $creditmemo_data['DocNumber'];
        }

        return preg_replace_callback('/[0-9]+/', function ($matches) {
            $last_match = end($matches);

            return ++$last_match;
        }, $number);
    }

    /**
     * Return last document number.
     *
     * @param  string $table_name
     * @return array
     */
    private function getLastDocumentNumberFromTable($table_name)
    {
        $result = [
            'Id' => 0,
            'DocNumber' => '0',
        ];
        $query = "select DocNumber, Id from $table_name WHERE DocNumber > '0' ORDERBY Id DESC STARTPOSITION 1 MAXRESULTS 1";

        $query_result = $this->dataService()->query($query);

        if ($query_result->count() > 0) {
            $data = $query_result->getIterator()[0]->getRawData();

            $result['Id'] = isset($data['Id']) ? intval($data['Id']) : 0;
            $result['DocNumber'] = isset($data['DocNumber']) ? $data['DocNumber'] : '0';
        }

        return $result;
    }
}
