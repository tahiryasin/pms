<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Payments framework model definition.
 *
 * @package angie.frameworks.payments
 * @subpackage resources
 */
class PaymentsFrameworkModel extends AngieFrameworkModel
{
    /**
     * Construct payments framework model definition.
     *
     * @param PaymentsFramework $parent
     */
    public function __construct(PaymentsFramework $parent)
    {
        parent::__construct($parent);

        $this->addModel(DB::createTable('payment_gateways')->addColumns([
            new DBIdColumn(),
            DBTypeColumn::create(),
            new DBAdditionalPropertiesColumn(),
            DBBoolColumn::create('is_enabled'),
        ]))->setTypeFromField('type');

        $this->addModel(DB::createTable('stored_cards')->addColumns([
            new DBIdColumn(),
            DBFkColumn::create('payment_gateway_id', 0, true),
            DBStringColumn::create('gateway_card_id', DBStringColumn::MAX_LENGTH, ''),
            DBEnumColumn::create('brand', ['visa', 'amex', 'mastercard', 'discover', 'jcb', 'diners', 'other'], 'other'),
            DBStringColumn::create('last_four_digits', 4),
            DBIntegerColumn::create('expiration_month', DBColumn::NORMAL, 0)->setUnsigned(true),
            DBIntegerColumn::create('expiration_year', DBColumn::NORMAL, 0)->setUnsigned(true),
            DBUserColumn::create('card_holder', true),
            DBStringColumn::create('address_line_1'),
            DBStringColumn::create('address_line_2'),
            DBStringColumn::create('address_zip'),
            DBStringColumn::create('address_city'),
            DBStringColumn::create('address_country'),
        ])->addIndices([
            DBIndex::create('gateway_card_id', DBIndex::UNIQUE),
            DBIndex::create('expiration', DBIndex::KEY, ['expiration_month', 'expiration_year']),
        ]));

        $this->addModel(DB::createTable('payments')->addColumns([
            new DBIdColumn(),
            new DBParentColumn(),
            new DBMoneyColumn('amount', 0),
            DBFkColumn::create('currency_id', 0, true),
            DBEnumColumn::create('status', ['paid', 'pending', 'deleted', 'canceled'], 'pending'),
            new DBCreatedOnByColumn(true),
            new DBUpdatedOnColumn(),
            DBDateColumn::create('paid_on'),
            DBTextColumn::create('comment'),
            DBEnumColumn::create('method', ['paypal', 'credit_card', 'custom'], 'custom'),
            new DBAdditionalPropertiesColumn(),
        ])->addIndices([
            DBIndex::create('status'),
            DBIndex::create('paid_on'),
        ]))->setOrderBy('created_on');
    }

    /**
     * Load initial framework data.
     */
    public function loadInitialData()
    {
        $this->addConfigOption('paypal_payment_gateway_id');
        $this->addConfigOption('credit_card_gateway_id');

        parent::loadInitialData();
    }
}
