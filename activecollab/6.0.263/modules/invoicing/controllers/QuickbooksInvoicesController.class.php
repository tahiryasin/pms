<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

AngieApplication::useController('auth_required', SystemModule::NAME);

use Angie\Http\Request;
use Angie\Http\Response;

/**
 * Quickbooks invoices controller.
 *
 * @package activeCollab.modules.invoicing
 * @subpackage controllers
 */
class QuickbooksInvoicesController extends AuthRequiredController
{
    /**
     * @var QuickbooksInvoice
     */
    protected $active_quickbooks_invoice;

    /**
     * {@inheritdoc}
     */
    protected function __before(Request $request, $user)
    {
        $before_result = parent::__before($request, $user);

        if ($before_result !== null) {
            return $before_result;
        }

        if ($user instanceof User && $user->isFinancialManager()) {
            $this->active_quickbooks_invoice = DataObjectPool::get('QuickbooksInvoice', $request->getId('quickbooks_invoice_id'));

            if (empty($this->active_quickbooks_invoice)) {
                $this->active_quickbooks_invoice = new QuickbooksInvoice();
            }
        } else {
            return Response::FORBIDDEN;
        }
    }

    /**
     * Display quickbooks invoices page.
     *
     * @param  Request              $request
     * @param  User                 $user
     * @return ModelCollection|void
     */
    public function index(Request $request, User $user)
    {
        return QuickbooksInvoices::prepareCollection('active_remote_invoices', $user);
    }

    /**
     * Return quickbooks invoice.
     *
     * @return QuickbooksInvoice
     */
    public function view()
    {
        return $this->active_quickbooks_invoice;
    }

    /**
     * Create new quickbooks invoice.
     *
     * @param Request $request
     * @param User    $user
     */
    public function add(Request $request, User $user)
    {
        return QuickbooksInvoices::canAdd($user) ? QuickbooksInvoices::create($request->post()) : Response::FORBIDDEN;
    }

    /**
     * Update quickbooks invoice.
     *
     * @param  Request        $request
     * @param  User           $user
     * @return DataObject|int
     */
    public function edit(Request $request, User $user)
    {
        return $this->active_quickbooks_invoice->isLoaded() ? QuickbooksInvoices::update($this->active_quickbooks_invoice, $request->put()) : Response::NOT_FOUND;
    }

    /**
     * Sync quickbooks invoices.
     *
     * @param  Request             $request
     * @param  User                $user
     * @return QuickbooksInvoice[]
     */
    public function sync(Request $request, User $user)
    {
        return QuickbooksInvoices::sync();
    }
}
