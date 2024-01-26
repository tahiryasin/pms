<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Stripe\Charge;
use Stripe\Error\Card;
use Stripe\Stripe;

/**
 * Stripe payment class.
 *
 * @package angie.framework.payments
 * @subpackage models
 */
class StripeGateway extends PaymentGateway implements ICardProcessingPaymentGateway
{
    use ICardProcessingPaymentGatewayImplementation;

    /**
     * Accepted currencies.
     *
     * @var array
     */
    public $supported_currencies = 'all';

    /**
     * Process credit card and return payment instance.
     *
     * @param  float                  $amount
     * @param  Currency               $currency
     * @param  string                 $token
     * @param  string|null            $comment
     * @return PaymentGatewayResponse
     * @throws PaymentGatewayError
     */
    public function processCreditCard($amount, Currency $currency, $token, $comment = null)
    {
        $this->prepareAndValidatePaymentData($amount, $currency);

        Stripe::setApiKey($this->getApiKey());

        try {
            $response = Charge::create([
                'amount' => $amount,
                'currency' => $currency->getCode(),
                'source' => $token,
                'description' => ($comment) ? $comment : '',
            ]);
        } catch (Card $e) {
            throw new PaymentGatewayError();
        }

        if ($response instanceof Charge) {
            if ($response->failure_message && $response->failure_code) {
                throw new PaymentGatewayError('Error #' . $response->failure_code . ': ' . $response->failure_message);
            }

            return new PaymentGatewayResponse(($this->isZeroCurrency($currency) ? $amount : $amount / 100), $response->id);
        } else {
            throw new PaymentGatewayError();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getToken($invoice)
    {
        return $this->getPublicKey();
    }

    /**
     * Prepare amount - return amount in cents.
     *
     * @param  float    $amount
     * @param  Currency $currency
     * @return int
     */
    public function prepareAmount($amount, Currency $currency)
    {
        return $this->isZeroCurrency($currency) ? ceil($amount) : ceil(round_up($amount) * 100);
    }

    /**
     * Return true if $currency is zero currency and should not be modified when amount is sent to Stripe.
     *
     * @param  Currency $currency
     * @return bool
     */
    private function isZeroCurrency(Currency $currency)
    {
        return in_array($currency->getCode(), ['BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'VND', 'VUV', 'XAF', 'XOF', 'XPF']);
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return array_merge(parent::jsonSerialize(), [
            'api_key' => $this->getApiKey(),
            'public_key' => $this->getPublicKey(),
        ]);
    }

    // ---------------------------------------------------
    //  Gateway configuration
    // ---------------------------------------------------

    /**
     * Set security credentials.
     *
     * @param  array             $credentials
     * @throws InvalidParamError
     */
    public function setCredentials(array $credentials)
    {
        if (isset($credentials['api_key']) && $credentials['api_key'] && isset($credentials['public_key']) && $credentials['public_key']) {
            $this->setApiKey($credentials['api_key']);
            $this->setPublicKey($credentials['public_key']);
        } else {
            throw new InvalidParamError('credentials', $credentials, 'API key and public key are required');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function is($var)
    {
        return $var instanceof self && $var->getApiKey() === $this->getApiKey();
    }

    /**
     * Get payment gateway api_key.
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->getAdditionalProperty('api_key');
    }

    /**
     * Set payment gateway api_key.
     *
     * @param string $value
     */
    public function setApiKey($value)
    {
        $this->setAdditionalProperty('api_key', $value);
    }

    /**
     * Get payment gateway public_key.
     *
     * @return string
     */
    public function getPublicKey()
    {
        return $this->getAdditionalProperty('public_key');
    }

    /**
     * Set payment gateway public_key.
     *
     * @param string $value
     */
    public function setPublicKey($value)
    {
        $this->setAdditionalProperty('public_key', $value);
    }

    /**
     * @param ValidationErrors $errors
     */
    public function validate(ValidationErrors &$errors)
    {
        $this->getApiKey() or $errors->fieldValueIsRequired('api_key');

        parent::validate($errors);
    }
}
