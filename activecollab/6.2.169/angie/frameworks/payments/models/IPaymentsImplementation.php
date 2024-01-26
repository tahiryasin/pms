<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Payments implementation that can be attached to any object.
 *
 * @package angie.frameworks.payments
 * @subpackage models
 */
trait IPaymentsImplementation
{
    /**
     * Say hello to the parent object.
     */
    public function IPaymentsImplementation()
    {
        $this->registerEventHandler('on_before_delete', function () {
            $this->releasePayments();
        });
    }

    /**
     * Return payments recorded for the parent object.
     *
     * @return Payment[]
     */
    public function getPayments()
    {
        return Payments::findBy(['parent_type' => get_class($this), 'parent_id' => $this->getId()]);
    }

    /**
     * Release payments.
     */
    protected function releasePayments()
    {
        if ($payment_ids = DB::executeFirstColumn('SELECT id FROM payments WHERE ' . Payments::parentToCondition($this))) {
            DB::execute('DELETE FROM payments WHERE id IN (?)', $payment_ids);
            Payments::clearCacheFor($payment_ids);
        }
    }

    /**
     * Return description.
     *
     * @return mixed
     */
    public function getDescription()
    {
        if (method_exists($this, 'getName')) {
            return $this->getName();
        } else {
            return get_class($this) . ': ' . $this->getId();
        }
    }

    // ---------------------------------------------------
    //  Making payments
    // ---------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function payAfterPayPalPayment($amount, $params)
    {
        $gateway = Payments::getCreditCardGateway($this);

        if ($gateway instanceof PaymentGateway) {
            $payment = Payments::create([
                'parent_type' => get_class($this),
                'parent_id' => $this->getId(),
                'amount' => $amount,
                'currency_id' => $this->getCurrency()->getId(),
                'status' => Payment::STATUS_PAID,
                'paid_on' => $params['paid_on'],
                'method' => Payment::CREDIT_CARD,
            ]);

            $name_on_card = null;
            if ($params['email'] && is_valid_email($params['email'])) {
                $name_on_card = $params['name'];
                $creator = Users::findByEmail($params['email'], true) instanceof User ? Users::findByEmail($params['email'], true) : new AnonymousUser($name_on_card, $params['email']);
                $payment->setCreatedBy($creator);
            }
            $payment->setAdditionalProperty('name_on_card', $name_on_card); //just in case that we have different 'name on card' and existing user 'display name'
            $payment->setAdditionalProperty('transaction_id', $params['transaction_id']);
            $payment->setAdditionalProperty('hash', sha1($params['paid_on'] + microtime()));
            $payment->save();

            $this->notifyPayer($payment);

            return $payment;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function payWithCreditCard($amount, $token, $email = null)
    {
        $gateway = Payments::getCreditCardGateway($this);

        if ($gateway instanceof ICardProcessingPaymentGateway) {
            $response = $gateway->processCreditCard($amount, $this->getCurrency(), $token);

            $payment = Payments::create([
                'parent_type' => get_class($this),
                'parent_id' => $this->getId(),
                'amount' => $amount,
                'currency_id' => $this->getCurrency()->getId(),
                'status' => Payment::STATUS_PAID,
                'paid_on' => $response->getPaidOn(),
                'method' => Payment::CREDIT_CARD,
            ]);

            $name_on_card = 'NN'; // TODO replace data. Is this really needed?

            if ($email && is_valid_email($email)) {
                $creator = Users::findByEmail($email, true) instanceof User ? Users::findByEmail($email, true) : new AnonymousUser($name_on_card, $email);
                $payment->setCreatedBy($creator);
            }

            $payment->setAdditionalProperty('name_on_card', $name_on_card); //just in case that we have different 'name on card' and existing user 'display name'
            $payment->setAdditionalProperty('transaction_id', $response->getTransactionId());
            $payment->setAdditionalProperty('hash', $this->getResponseHash($response));
            $payment->save();

            $this->notifyPayer($payment);

            return $payment;
        } else {
            throw new InvalidInstanceError('gateway', $gateway, 'Expected instance of gateway that can process credit cards');
        }
    }

    /**
     * Return hash unique to payment timestamp.
     *
     * @param  PaymentGatewayResponse $response
     * @return string
     */
    private function getResponseHash(PaymentGatewayResponse $response)
    {
        if ($paid_on = $response->getPaidOn()) {
            return sha1($response->getPaidOn()->toMySQL() . '-' . microtime(true));
        } else {
            return sha1(microtime(true));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function initWithPayPal($amount)
    {
        $paypal = Payments::getPayPalGateway($this);

        if ($paypal instanceof PaypalExpressCheckoutGateway) {
            $response = $paypal->makeInitialRequest($amount, $this->getCurrency(), $this->getDescription());

            $payment = Payments::create([
                'parent_type' => get_class($this),
                'parent_id' => $this->getId(),
                'amount' => $response->getAmount(),
                'currency_id' => $this->getCurrency()->getId(),
                'status' => Payment::STATUS_PENDING,
                'paid_on' => $response->getPaidOn(),
                'method' => Payment::PAYPAL,
            ]);

            $hash_string = ($response->getPaidOn() ? $response->getPaidOn()->getTimestamp() : '') . microtime();

            $payment->setAdditionalProperty('hash', sha1($hash_string));
            $payment->setAdditionalProperty('transaction_id', $response->getTransactionId());
            $payment->setAdditionalProperty('token', $response->getToken());
            $payment->save();

            return $paypal->getCompletePaymentUrl($payment);
        } else {
            throw new InvalidInstanceError('paypal', $paypal, 'Expected instance of gateway that can process paypal express payments');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function completeWithPayPal(Payment $payment, $payer_id)
    {
        $paypal = Payments::getPayPalGateway($this);

        if ($paypal instanceof PaypalExpressCheckoutGateway) {
            $paypal->completePayment($payment, $payer_id);
            $payment = Payments::update($payment, ['status' => Payment::STATUS_PAID]);

            $this->notifyPayer($payment);

            return $payment;
        } else {
            throw new InvalidInstanceError('paypal', $paypal, 'Expected instance of gateway that can process paypal express payments');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function cancelPayPalPayment(Payment $payment)
    {
        $paypal = Payments::getPayPalGateway($this);

        if ($paypal instanceof PaypalExpressCheckoutGateway) {
            $payment = Payments::update($payment, [
                'status' => Payment::STATUS_CANCELED,
            ]);

            return $payment;
        } else {
            throw new InvalidInstanceError('paypal', $paypal, 'Expected instance of gateway that can process paypal express payments');
        }
    }

    /**
     * Notify payer.
     *
     * @param Payment $payment
     */
    private function notifyPayer(Payment $payment)
    {
        if ($payment->getStatus() == Payment::STATUS_PAID && $payment->getCreatedBy() instanceof IUser) {
            AngieApplication::notifications()
                ->notifyAbout('payment_received', $payment)
                ->sendToUsers($payment->getCreatedBy(), true);
        }
    }

    // ---------------------------------------------------
    //  Expectations
    // ---------------------------------------------------

    /**
     * Return object ID.
     *
     * @return int
     */
    abstract public function getId();

    /**
     * @return Currency
     */
    abstract public function getCurrency();

    /**
     * Register an internal event handler.
     *
     * @param $event
     * @param $handler
     * @throws InvalidParamError
     */
    abstract protected function registerEventHandler($event, $handler);

    /**
     * Return public page, where IPayment object can be paid or downloaded.
     *
     * @return string
     */
    abstract public function getPublicUrl();
}
