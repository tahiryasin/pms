<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

use ActiveCollab\Module\System\Events\Maintenance\DailyMaintenanceEventInterface;

class InvoicingModule extends AngieModule
{
    const NAME = 'invoicing';
    const PATH = __DIR__;

    protected $name = 'invoicing';
    protected $version = '5.0';

    public function init()
    {
        parent::init();

        DataObjectPool::registerTypeLoader(
            Invoice::class,
            function ($ids) {
                return Invoices::findByIds($ids);
            }
        );

        DataObjectPool::registerTypeLoader(
            InvoiceItem::class,
            function ($ids) {
                return InvoiceItems::findByIds($ids);
            }
        );

        DataObjectPool::registerTypeLoader(
            Estimate::class,
            function ($ids) {
                return Estimates::findByIds($ids);
            }
        );

        DataObjectPool::registerTypeLoader(
            RecurringProfile::class,
            function ($ids) {
                return RecurringProfiles::findByIds($ids);
            }
        );

        DataObjectPool::registerTypeLoader(
            TaxRate::class,
            function ($ids) {
                return TaxRates::findByIds($ids);
            }
        );

        DataObjectPool::registerTypeLoader(
            InvoiceItemTemplate::class,
            function ($ids) {
                return InvoiceItemTemplates::findByIds($ids);
            }
        );

        DataObjectPool::registerTypeLoader(
            InvoiceNoteTemplate::class,
            function ($ids) {
                return InvoiceNoteTemplates::findByIds($ids);
            }
        );

        DataObjectPool::registerTypeLoader(
            QuickbooksInvoice::class,
            function ($ids) {
                return QuickbooksInvoices::findByIds($ids);
            }
        );

        DataObjectPool::registerTypeLoader(
            XeroInvoice::class,
            function ($ids) {
                return XeroInvoices::findByIds($ids);
            }
        );
    }

    /**
     * Define module classes.
     */
    public function defineClasses()
    {
        require_once __DIR__ . '/resources/autoload_model.php';

        AngieApplication::setForAutoload([
            'IInvoice' => __DIR__ . '/models/IInvoice.class.php',
            'IInvoiceImplementation' => __DIR__ . '/models/IInvoiceImplementation.class.php',

            'IInvoiceExport' => __DIR__ . '/models/IInvoiceExport.class.php',

            'InvoiceTemplate' => __DIR__ . '/models/InvoiceTemplate.class.php',
            'InvoicePDFGenerator' => __DIR__ . '/models/InvoicePDFGenerator.class.php',

            'IRoundFieldValueToDecimalPrecisionImplementation' => __DIR__ . '/models/IRoundFieldValueToDecimalPrecisionImplementation.class.php',

            // Invoice Based On
            'IInvoiceBasedOn' => __DIR__ . '/models/invoice_based_on/IInvoiceBasedOn.php',
            'IInvoiceBasedOnImplementation' => __DIR__ . '/models/invoice_based_on/IInvoiceBasedOnImplementation.php',
            'IInvoiceBasedOnTrackedDataImplementation' => __DIR__ . '/models/invoice_based_on/IInvoiceBasedOnTrackedDataImplementation.php',
            'IInvoiceBasedOnTrackingFilterResultImplementation' => __DIR__ . '/models/invoice_based_on/IInvoiceBasedOnTrackingFilterResultImplementation.php',

            'InvoicesFilter' => __DIR__ . '/models/reports/InvoicesFilter.php',
            'InvoicePaymentsFilter' => __DIR__ . '/models/reports/InvoicePaymentsFilter.php',

            // Search
            'InvoicesSearchBuilder' => __DIR__ . '/models/search_builders/InvoicesSearchBuilder.php',
            'EstimatesSearchBuilder' => __DIR__ . '/models/search_builders/EstimatesSearchBuilder.php',

            'BaseInvoiceSearchDocument' => __DIR__ . '/models/search_documents/BaseInvoiceSearchDocument.php',
            'InvoiceSearchDocument' => __DIR__ . '/models/search_documents/InvoiceSearchDocument.php',
            'EstimateSearchDocument' => __DIR__ . '/models/search_documents/EstimateSearchDocument.php',

            // Notifications
            'InvoiceNotification' => __DIR__ . '/notifications/InvoiceNotification.class.php',
            'SendInvoiceNotification' => __DIR__ . '/notifications/SendInvoiceNotification.class.php',
            'InvoicePaidNotification' => __DIR__ . '/notifications/InvoicePaidNotification.class.php',
            'InvoiceCanceledNotification' => __DIR__ . '/notifications/InvoiceCanceledNotification.class.php',
            'InvoiceReminderNotification' => __DIR__ . '/notifications/InvoiceReminderNotification.class.php',

            'EstimateNotification' => __DIR__ . '/notifications/EstimateNotification.class.php',
            'SendEstimateNotification' => __DIR__ . '/notifications/SendEstimateNotification.class.php',
            'EstimateUpdatedNotification' => __DIR__ . '/notifications/EstimateUpdatedNotification.class.php',

            'RecurringProfileNotification' => __DIR__ . '/notifications/RecurringProfileNotification.class.php',
            'InvoiceGeneratedViaRecurringProfileNotification' => __DIR__ . '/notifications/InvoiceGeneratedViaRecurringProfileNotification.class.php',
            'DraftInvoiceCreatedViaRecurringProfileNotification' => __DIR__ . '/notifications/DraftInvoiceCreatedViaRecurringProfileNotification.class.php',

            // Quickbooks
            'QuickbooksInvoice' => __DIR__ . '/models/quickbooks_invoices/QuickbooksInvoice.class.php',
            'QuickbooksInvoices' => __DIR__ . '/models/quickbooks_invoices/QuickbooksInvoices.class.php',

            // Quickbooks Integration
            'QuickbooksIntegration' => __DIR__ . '/models/integrations/QuickbooksIntegration.php',

            // Xero Invoice
            'XeroInvoice' => __DIR__ . '/models/xero_invoices/XeroInvoice.class.php',
            'XeroInvoices' => __DIR__ . '/models/xero_invoices/XeroInvoices.class.php',

            // Xero Integration
            'XeroIntegration' => __DIR__ . '/models/integrations/XeroIntegration.php',
        ]);
    }

    public function defineHandlers()
    {
        $this->listen('on_daily_maintenance');

        $this->listen('on_rebuild_activity_logs');

        $this->listen('on_object_from_notification_context');
        $this->listen('on_notification_context_view_url');

        $this->listen('on_history_field_renderers');

        $this->listen('on_protected_config_options');
        $this->listen('on_initial_settings');
        $this->listen('on_resets_initial_settings_timestamp');

        $this->listen('on_visible_object_paths');

        $this->listen('on_search_rebuild_index');
        $this->listen('on_user_access_search_filter');

        $this->listen('on_trash_sections');

        $this->listen('on_available_integrations');
        $this->listen('on_extra_stats');
    }

    public function defineListeners(): array
    {
        return [
            DailyMaintenanceEventInterface::class => AngieApplication::recurringInvoicesDispatcher(),
        ];
    }

    public function install()
    {
        recursive_mkdir(WORK_PATH . '/invoices', 0777, WORK_PATH);

        parent::install();
    }
}
