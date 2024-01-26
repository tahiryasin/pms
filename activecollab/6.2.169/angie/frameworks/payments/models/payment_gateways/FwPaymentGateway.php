<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Framework level payment gateway instance implementation.
 *
 * @package angie.frameworks.payments
 * @subpackage models
 */
abstract class FwPaymentGateway extends BasePaymentGateway
{
    const CARD_VISA = 'Visa';
    const CARD_MASTER = 'MasterCard';
    const CARD_DISCOVER = 'Discover';
    const CARD_AMEX = 'Amex';

    /**
     * Payment gateway type.
     *
     * @var string
     */
    public $payment_gateway_type;

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return array_merge(parent::jsonSerialize(), [
            'is_live' => $this->isLive(),
            'is_enabled' => $this->getIsEnabled(),
        ]);
    }

    /**
     * Return true if this gateway can process credit cards.
     *
     * @return bool
     */
    public function canProcessCreditCards()
    {
        return false; // Off by default
    }

    /**
     * Set security credentials.
     *
     * @param array $credentials
     */
    abstract public function setCredentials(array $credentials);

    /**
     * Return true if this gateway is not testing, but actually processing cards.
     *
     * @return bool
     */
    public function isLive()
    {
        if (AngieApplication::isEdgeChannel() || AngieApplication::isInDevelopment()) {
            return defined('PAYMENT_GATEWAY_GO_LIVE') && PAYMENT_GATEWAY_GO_LIVE;
        } else {
            return true;
        }
    }

    /**
     * Return array of supported currencies, or true if all currencies are supported.
     *
     * @return bool|array
     */
    protected function getSupportedCurrencies()
    {
        return true; // True means all
    }

    /**
     * Return true if $currency is supported by this gateway.
     *
     * @param  Currency            $currency
     * @return bool
     * @throws NotImplementedError
     */
    public function isSupportedCurrency(Currency $currency)
    {
        $supported_currencies = $this->getSupportedCurrencies();

        if ($supported_currencies && is_foreachable($supported_currencies)) {
            return in_array($currency->getCode(), $supported_currencies);
        }

        return $supported_currencies === true;
    }

    /**
     * Return true if this expense category is used for estimate.
     *
     * @return bool
     */
    public function isUsed()
    {
        return (bool) DB::executeFirstCell('SELECT COUNT(id) AS "row_count" FROM payments WHERE gateway_id = ?', $this->getId());
    }

    // ---------------------------------------------------
    //  System
    // ---------------------------------------------------

    /**
     * Delete specific object (and related objects if neccecery).
     *
     * @param  bool      $bulk
     * @throws Exception
     */
    public function delete($bulk = false)
    {
        try {
            DB::beginWork('Begin: delete payment gateway @ ' . __CLASS__);

            if ($stored_card_ids = DB::executeFirstColumn('SELECT id FROM stored_cards WHERE payment_gateway_id = ?', $this->getId())) {
                DB::execute('DELETE FROM stored_cards WHERE id IN (?)', $stored_card_ids);
                StoredCards::clearCacheFor($stored_card_ids);
            }

            parent::delete($bulk);

            DB::commit('Done: delete payment gateway @ ' . __CLASS__);
        } catch (Exception $e) {
            DB::rollback('Rollback: delete payment gateway @ ' . __CLASS__);
            throw $e;
        }
    }
}
