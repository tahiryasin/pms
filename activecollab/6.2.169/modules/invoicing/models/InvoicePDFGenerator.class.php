<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

require_once InvoicingModule::PATH . '/models/InvoiceTCPDF.class.php';

/**
 * Invoice PDF Generator.
 *
 * @author godza
 * @package activeCollab.modules.invoicing
 * @subpackage invoicing
 */
class InvoicePDFGenerator
{
    /**
     * saves the invoice.
     *
     * @param IInvoice $invoice
     * @param string   $filename
     */
    public static function save($invoice, $filename)
    {
        $generator = new InvoiceTCPDF($invoice);
        $generator->generate();
        $generator->Output($filename, 'F');
    }

    /**
     * Downloads the invoice.
     *
     * @param IInvoice $invoice
     * @param string   $filename
     */
    public static function download($invoice, $filename = null)
    {
        $generator = new InvoiceTCPDF($invoice);
        $generator->generate();
        $generator->Output($filename, 'D');
    }

    /**
     * Displays the invoice inline.
     *
     * @param IInvoice $invoice
     * @param string   $filename
     */
    public static function inline($invoice, $filename = null)
    {
        $generator = new InvoiceTCPDF($invoice);
        $generator->generate();
        $generator->Output($filename, 'I');
    }
}
