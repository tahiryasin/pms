<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Framework level payments manager class.
 *
 * @package angie.frameworks.payments
 * @subpackage models
 */
class FwPayments extends BasePayments
{
    /**
     * Return new collection.
     *
     * @param  string            $collection_name
     * @param  User|null         $user
     * @return ModelCollection
     * @throws InvalidParamError
     */
    public static function prepareCollection($collection_name, $user)
    {
        if ($collection_name == 'recent_payments') {
            $collection = parent::prepareCollection($collection_name, $user);

            $collection->setOrderBy('created_on DESC');
            $collection->setPagination(1, 300);

            return $collection;
        } else {
            throw new InvalidParamError('collection_name', $collection_name);
        }
    }

    public static function create(array $attributes, bool $save = true, bool $announce = true)
    {
        $parent = isset($attributes['parent_type']) && isset($attributes['parent_id'])
            ? DataObjectPool::get($attributes['parent_type'], $attributes['parent_id'])
            : null;

        if ($parent instanceof IPayments) {
            if (empty($attributes['paid_on'])) {
                $attributes['paid_on'] = DateValue::now();
            }

            $attributes['currency_id'] = $parent->getCurrency()->getId();

            try {
                DB::beginWork('Begin: create a new payment @ ' . __CLASS__);

                $payment = parent::create($attributes, $save, $announce); // @TODO Announcement should be done after new payment is recorded

                if ($payment instanceof Payment && $payment->getStatus() == Payment::STATUS_PAID) {
                    $parent->recordNewPayment($payment);
                }

                DB::commit('Done: create a new payment @ ' . __CLASS__);

                return $payment;
            } catch (Exception $e) {
                DB::rollback('Rollback: create a new payment @ ' . __CLASS__);
                throw $e;
            }
        } else {
            throw new InvalidInstanceError('parent', $parent, 'IPayments');
        }
    }

    /**
     * Update an instance.
     *
     * @param  Payment|DataObject $instance
     * @param  array              $attributes
     * @param  bool               $save
     * @return Payment
     * @throws Exception
     */
    public static function &update(DataObject &$instance, array $attributes, $save = true)
    {
        try {
            DB::beginWork('Begin: update payment @ ' . __CLASS__);

            $parent = $instance->getParent();

            if ($parent instanceof IPayments && isset($attributes['amount']) && $attributes['amount'] > $instance->getAmount()) {
                $diff = $attributes['amount'] - $instance->getAmount();

                if ($diff > $parent->getBalanceDue()) {
                    throw new InvalidParamError('attributes[amount]', $attributes['amount'], 'Overpay is not allowed');
                }
            }

            parent::update($instance, $attributes, $save);

            if ($parent instanceof IPayments) {
                $parent->recordPaymentUpdate($instance);
            }

            DB::commit('Done: update payment @ ' . __CLASS__);
        } catch (Exception $e) {
            DB::rollback('Rollback: update payment @ ' . __CLASS__);
            throw $e;
        }

        return $instance;
    }

    /**
     * Scrap an instance.
     *
     * @param  Payment|DataObject $instance
     * @param  bool               $force_delete
     * @return bool
     * @throws Exception
     */
    public static function scrap(DataObject &$instance, $force_delete = false)
    {
        try {
            DB::beginWork('Begin: remove payment @ ' . __CLASS__);

            $parent = $instance->getParent();

            parent::scrap($instance, true);

            if ($parent instanceof IPayments) {
                $parent->recordPaymentRemoval();
            }

            DB::commit('Done: remove payment @ ' . __CLASS__);
        } catch (Exception $e) {
            DB::rollback('Rollback: remove payment @ ' . __CLASS__);
            throw $e;
        }
    }

    // ---------------------------------------------------
    //  Gateway slots
    // ---------------------------------------------------

    /**
     * Return true if we have a configured gateway (that can receive payments for a given object).
     *
     * @param  IPayments|null $to_receive_payment_for
     * @return bool
     */
    public static function hasConfiguredGateway($to_receive_payment_for = null)
    {
        $paypal = Payments::getPayPalGateway($to_receive_payment_for);
        $credit_card = Payments::getCreditCardGateway($to_receive_payment_for);

        return ($paypal instanceof PaymentGateway && $paypal->getIsEnabled()) || ($credit_card instanceof PaymentGateway && $credit_card->getIsEnabled());
    }

    /**
     * Return credit card gateway.
     *
     * @param  IPayments|null $to_receive_payment_for
     * @return PaymentGateway
     */
    public static function getPayPalGateway($to_receive_payment_for = null)
    {
        return self::getGatewayById(ConfigOptions::getValue('paypal_payment_gateway_id'), $to_receive_payment_for);
    }

    /**
     * Set a gateway that we will use to process PayPal transactions.
     *
     * @param  PaypalExpressCheckoutGateway $gateway
     * @throws InvalidInstanceError
     */
    public static function setPayPalGateway($gateway)
    {
        if ($gateway instanceof PaypalExpressCheckoutGateway && $gateway->isLoaded()) {
            ConfigOptions::setValue('paypal_payment_gateway_id', $gateway->getId());
        } elseif ($gateway === null) {
            ConfigOptions::setValue('paypal_payment_gateway_id', null);
        } else {
            throw new InvalidInstanceError('gateway', $gateway, 'PaypalExpressCheckoutGateway');
        }
    }

    /**
     * Update PayPal express checkout gateway.
     *
     * @param  PaypalExpressCheckoutGateway $with
     * @return PaypalExpressCheckoutGateway
     * @throws Exception
     */
    public static function updatePaypalGateway(PaypalExpressCheckoutGateway $with)
    {
        $current_gateway = Payments::getPayPalGateway();

        // We have a gateway set? Check if these are the same gateways before proceeding
        if ($current_gateway instanceof PaypalExpressCheckoutGateway) {
            if ($current_gateway->is($with) && $current_gateway->getIsEnabled() === $with->getIsEnabled()) {
                return $current_gateway;
            } else {
                try {
                    DB::beginWork('Begin: swap payment gateway @ ' . __CLASS__);

                    $current_gateway->delete();
                    $with->save();

                    Payments::setPayPalGateway($with);

                    DB::commit('Done: swap payment gateway @ ' . __CLASS__);
                } catch (Exception $e) {
                    DB::rollback('Rollback: swap payment gateway @ ' . __CLASS__);
                    throw $e;
                }
            }

            // No gateway set? Easy
        } else {
            if ($with->isNew()) {
                $with->save();
            }

            Payments::setPayPalGateway($with);
        }

        return $with;
    }

    /**
     * Return credit card payment gateway if exits.
     *
     * (If Config Options for value 'credit_card_gateway_id' Empty then get from PaymentsGateway table) Used for form on Open and Edit
     *
     * @return DataObject|ICardProcessingPaymentGateway|PaymentGateway
     * @throws ConfigOptionDnxError
     * @throws InvalidParamError
     * @throws \Angie\Error
     */
    public static function getPaymentCreditCardGateway()
    {
        if (empty(ConfigOptions::getValue('credit_card_gateway_id'))) {
            $from_sql = self::findOneBySql('SELECT * FROM payment_gateways WHERE type != ?', 'PaypalExpressCheckoutGateway');
            if (empty($from_sql)) {
                return null;
            }
            $credit_card = DataObjectPool::get('PaymentGateway', $from_sql->getId());
        } else {
            $credit_card = self::getCreditCardGateway();
        }

        return $credit_card;
    }

    /**
     * Return credit card gateway.
     *
     * @param  IPayments|null                               $to_receive_payment_for
     * @return PaymentGateway|ICardProcessingPaymentGateway
     */
    public static function getCreditCardGateway($to_receive_payment_for = null)
    {
        return self::getGatewayById(ConfigOptions::getValue('credit_card_gateway_id'), $to_receive_payment_for);
    }

    /**
     * Set a gateway that we will use to process credit cards.
     *
     * @param  PaymentGateway|null  $gateway
     * @throws InvalidInstanceError
     */
    public static function setCreditCardGateway($gateway)
    {
        if ($gateway instanceof PaymentGateway && $gateway->isLoaded() && $gateway->getIsEnabled()) {
            ConfigOptions::setValue('credit_card_gateway_id', $gateway->getId());
        } elseif ($gateway === null || !$gateway->getIsEnabled()) {
            ConfigOptions::setValue('credit_card_gateway_id', null);
        } else {
            throw new InvalidInstanceError('gateway', $gateway, 'PaymentGateway');
        }
    }

    /**
     * Update credit card gateway.
     *
     * @param  PaymentGateway       $with
     * @return PaymentGateway
     * @throws Exception
     * @throws InvalidInstanceError
     */
    public static function updateCreditCardGateway(PaymentGateway $with)
    {
        $current_gateway = Payments::getPaymentCreditCardGateway();

        // We have a gateway set? Check if these are the same gateways before proceeding
        if ($current_gateway instanceof PaymentGateway) {
            if ($current_gateway->is($with) && $current_gateway->getIsEnabled() === $with->getIsEnabled()) {
                return $current_gateway;
            } else {
                try {
                    DB::beginWork('Begin: swap payment gateway @ ' . __CLASS__);

                    $current_gateway->delete();
                    $with->save();

                    Payments::setCreditCardGateway($with);

                    DB::commit('Done: swap payment gateway @ ' . __CLASS__);
                } catch (Exception $e) {
                    DB::rollback('Rollback: swap payment gateway @ ' . __CLASS__);
                    throw $e;
                }
            }

            // No gateway set? Easy
        } else {
            if ($with->isNew()) {
                $with->save();
            }

            Payments::setCreditCardGateway($with);
        }

        return $with;
    }

    /**
     * Find gateway by a given gateway ID and optionally check if we can use it to process payments for $to_receive_payment_for.
     *
     * @param  int|null            $gateway_id
     * @param  IPayments|null      $to_receive_payment_for
     * @return PaymentGateway|null
     */
    private static function getGatewayById($gateway_id, $to_receive_payment_for = null)
    {
        if ($gateway_id) {
            /** @var PaymentGateway|null $gateway */
            if ($gateway = DataObjectPool::get('PaymentGateway', $gateway_id)) {
                if ($to_receive_payment_for instanceof IPayments && !$gateway->isSupportedCurrency($to_receive_payment_for->getCurrency())) {
                    return null;
                }

                return $gateway;
            }
        }

        return null;
    }

    /**
     * Sum payments by parent.
     *
     * @param  IPayments $parent
     * @return float
     */
    public static function sumByParent(IPayments $parent)
    {
        return (float) DB::executeFirstCell('SELECT SUM(amount) FROM payments WHERE parent_type = ? AND parent_id = ? AND status = ?', get_class($parent), $parent->getId(), Payment::STATUS_PAID);
    }

    // ---------------------------------------------------
    //  Old API
    // ---------------------------------------------------

    /**
     * Return payment statuses.
     *
     * @return array
     */
    public static function getStatuses()
    {
        return [
            Payment::STATUS_PAID => lang('Paid'),
            Payment::STATUS_CANCELED => lang('Canceled'),
            Payment::STATUS_PENDING => lang('Pending'),
            Payment::STATUS_DELETED => lang('Deleted'),
        ];
    }

    /**
     * Return payments by company.
     *
     * @param  Company $company
     * @return array
     */
    public function findByCompany(Company $company)
    {
        return Payments::findBySQL('SELECT payments.* FROM invoices, payments WHERE payments.parent_id = invoices.id AND invoices.company_id = ? ORDER BY payments.paid_on DESC', $company->getId());
    }

    /**
     * Find payment by token.
     *
     * @param  string  $token
     * @return Payment
     */
    public static function findByToken($token)
    {
        /** @var Payment[] $payments */
        if ($payments = Payments::find(['conditions' => ['method = ?', 'paypal'], 'order' => 'created_on DESC'])) {
            foreach ($payments as $payment) {
                if ($payment->getAdditionalProperty('token') == $token) {
                    return $payment;
                }
            }
        }

        return null;
    }
}
