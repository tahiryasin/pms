<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Module\Invoicing\Utils\VariableProcessor\Factory;

use ActiveCollab\Foundation\Text\VariableProcessor\ValueResolver\DateReferenced\DateResolver;
use ActiveCollab\Foundation\Text\VariableProcessor\ValueResolver\DateReferenced\MonthDayResolver;
use ActiveCollab\Foundation\Text\VariableProcessor\ValueResolver\DateReferenced\MonthResolver;
use ActiveCollab\Foundation\Text\VariableProcessor\ValueResolver\DateReferenced\QuarterResolver;
use ActiveCollab\Foundation\Text\VariableProcessor\ValueResolver\DateReferenced\YearResolver;
use ActiveCollab\Foundation\Text\VariableProcessor\ValueResolver\ValueResolverInterface;
use ActiveCollab\Foundation\Text\VariableProcessor\VariableProcessor;
use ActiveCollab\Foundation\Text\VariableProcessor\VariableProcessorInterface;
use ActiveCollab\Foundation\Wrappers\ConfigOptions\ConfigOptionsInterface;
use ActiveCollab\Foundation\Wrappers\DataObjectPool\DataObjectPoolInterface;
use ActiveCollab\Module\Invoicing\Utils\VariableProcessor\ValueResolver\ClientNameResolver;
use ActiveCollab\Module\Invoicing\Utils\VariableProcessor\ValueResolver\PoNumberResolver;
use ActiveCollab\Module\Invoicing\Utils\VariableProcessor\ValueResolver\ProjectNameResolver;
use DateValue;
use Invoice;

class VariableProcessorFactory implements VariableProcessorFactoryInterface
{
    private $data_object_pool;
    private $config_options;
    private $default_date_format;

    public function __construct(
        DataObjectPoolInterface $data_object_pool,
        ConfigOptionsInterface $config_options,
        string $default_date_format
    )
    {
        $this->data_object_pool = $data_object_pool;
        $this->config_options = $config_options;
        $this->default_date_format = $default_date_format;
    }

    public function getAvailableVariableNamesForInvoice(): array
    {
        $result = [];

        foreach ($this->getResolversForInvoice(null) as $resolver) {
            $result = array_merge($result, $resolver->getAvailableVariableNames());
        }

        return $result;
    }

    public function createFromInvoice(Invoice $invoice): VariableProcessorInterface
    {
        return new VariableProcessor(...$this->getResolversForInvoice($invoice));
    }

    /**
     * @param  Invoice|null             $invoice
     * @return ValueResolverInterface[]
     */
    private function getResolversForInvoice(?Invoice $invoice): array
    {
        $issued_on = $invoice ? $invoice->getIssuedOn() : null;
        $due_on = $invoice ? $invoice->getDueOn() : null;

        return array_merge(
            [
                new ClientNameResolver($invoice ? $invoice->getCompanyName() : null),
                new ProjectNameResolver(
                    $invoice ? $invoice->getProjectId() : null,
                    $this->data_object_pool
                ),
                new PoNumberResolver($invoice ? $invoice->getPurchaseOrderNumber() : null),
            ],
            $this->getDateReferencedResolvers($issued_on, 'issue'),
            $this->getDateReferencedResolvers($due_on, 'due')
        );
    }

    public function getDateReferencedResolvers(?DateValue $reference_date, string $prefix): array
    {
        return [
            new YearResolver($reference_date, $prefix),
            new QuarterResolver($reference_date, $prefix),
            new MonthResolver($reference_date, $prefix),
            new MonthDayResolver($reference_date, $prefix),
            new DateResolver($reference_date, $this->config_options, $this->default_date_format, $prefix),
        ];
    }
}
