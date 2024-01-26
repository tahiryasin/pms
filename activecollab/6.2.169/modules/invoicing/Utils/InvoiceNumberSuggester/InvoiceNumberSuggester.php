<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Module\Invoicing\Utils\InvoiceNumberSuggester;

use ActiveCollab\Module\Invoicing\Utils\ExistingInvoiceNumbersResolver\ExistingInvoiceNumbersResolverInterface;

class InvoiceNumberSuggester implements InvoiceNumberSuggesterInterface
{
    private $existing_invoice_numbers_resolver;

    public function __construct(ExistingInvoiceNumbersResolverInterface $existing_invoice_numbers_resolver)
    {
        $this->existing_invoice_numbers_resolver = $existing_invoice_numbers_resolver;
    }

    public function suggest(bool $keep_as_existing = true): string
    {
        $existing_invoice_numbers = $this->existing_invoice_numbers_resolver->getExistingInvoiceNumbers();

        $last_invoice_number = empty($existing_invoice_numbers)
            ? ''
            : $existing_invoice_numbers[count($existing_invoice_numbers) - 1];

        $suggested_invoice_number = $this->doSuggest($last_invoice_number);

        while (in_array($suggested_invoice_number, $existing_invoice_numbers)) {
            $suggested_invoice_number = $this->doSuggest($suggested_invoice_number);
        }

        return $suggested_invoice_number;
    }

    /**
     * Prepare the suggested next invoice number based on last invoice number.
     *
     * @param  string $last_invoice_number
     * @return string
     */
    private function doSuggest(string $last_invoice_number)
    {
        if (empty($last_invoice_number)) {
            return '0001';
        }

        $results = [];

        // if last invoice is in form of number
        if (preg_match('/^([A-z \-\/]*)([0-9]+)$/', $last_invoice_number, $results)) {
            return $this->suggestNumber($results[1], $results[2]);
        }

        $results = [];

        // if invoice is in form of 12-2014 or 12/2014
        if (preg_match('/^([A-z \-\/]*)([0-9]*)([ ]*[\-\/][ ]*)(20[0-9][0-9])$/', $last_invoice_number, $results)) {
            return $this->suggestNumberYear($results[1], $results[2], $results[3], $results[4]);
        }

        // if invoice is in form of 2014-12 or 2014/12
        if (preg_match('/^([A-z \-\/]*)(20[0-9][0-9])([ ]*[\-\/][ ]*)([0-9]*)$/', $last_invoice_number, $results)) {
            return $this->suggestYearNumber($results[1], $results[2], $results[3], $results[4]);
        }

        // Last line of defensive pattern (increment last number, or append -1)
        if (strpos($last_invoice_number, '-') !== false) {
            $number_bits = explode('-', $last_invoice_number);
        } elseif (strpos($last_invoice_number, '/') !== false) {
            $number_bits = explode('/', $last_invoice_number);
        } else {
            $number_bits = [$last_invoice_number];
        }

        $last_num = end($number_bits);

        if (ctype_digit($last_num)) {
            $last_invoice_number = $this->suggestByIncrementingLastNumber($last_invoice_number, $last_num);
        } else {
            $last_invoice_number .= '-1';
        }

        return $last_invoice_number;
    }

    /**
     * Suggest invoice number for simple numeric values:.
     *
     * - 001 -> 002
     * - 099 -> 100
     * - 1 -> 2
     *
     * @param  string $prefix
     * @param  string $last_invoice_number
     * @return string
     */
    private function suggestNumber($prefix, $last_invoice_number)
    {
        $suggested_invoice_number = (string) ((int) $last_invoice_number + 1);

        $last_invoice_number_len = mb_strlen($last_invoice_number);
        $suggested_invoice_number_len = mb_strlen($suggested_invoice_number);

        if ($last_invoice_number_len > $suggested_invoice_number_len) {
            $suggested_invoice_number = str_pad(
                $suggested_invoice_number,
                $last_invoice_number_len,
                '0',
                STR_PAD_LEFT
            );
        }

        return $prefix . $suggested_invoice_number;
    }

    /**
     * @param  string $prefix
     * @param  string $number
     * @param  string $separator
     * @param  string $year
     * @return string
     */
    private function suggestNumberYear($prefix, $number, $separator, $year)
    {
        return $prefix . $this->suggestNumber('', $number) . $separator . $year;
    }

    /**
     * @param  string $prefix
     * @param  string $year
     * @param  string $separator
     * @param  string $number
     * @return string
     */
    private function suggestYearNumber($prefix, $year, $separator, $number)
    {
        return $prefix . $year . $separator . $this->suggestNumber('', $number);
    }

    /**
     * @param  string $last_invoice_number
     * @param  string $last_num
     * @return string
     */
    private function suggestByIncrementingLastNumber($last_invoice_number, $last_num)
    {
        return rtrim($last_invoice_number, $last_num) . $this->suggestNumber('', $last_num);
    }
}
