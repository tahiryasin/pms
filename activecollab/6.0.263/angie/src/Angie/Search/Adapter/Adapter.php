<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\Search\Adapter;

use ActiveCollab\JobsQueue\DispatcherInterface;
use Psr\Log\LoggerInterface;

/**
 * @package Angie\Search\Adapter
 */
abstract class Adapter implements AdapterInterface
{
    /**
     * @var array
     */
    private $hosts;

    /**
     * @var int
     */
    private $shards;

    /**
     * @var int
     */
    private $replicas;

    /**
     * @var string
     */
    private $index_name;

    /**
     * @var string
     */
    private $document_type;

    /**
     * @var int
     */
    private $tenant_id;

    /**
     * @var bool
     */
    private $is_on_demand;

    /**
     * @var DispatcherInterface
     */
    private $jobs;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param array               $hosts
     * @param int                 $shards
     * @param int                 $replicas
     * @param string              $index_name
     * @param string              $document_type
     * @param int                 $tenant_id
     * @param DispatcherInterface $jobs
     * @param LoggerInterface     $logger
     * @param bool                $is_on_demand
     */
    public function __construct(
        array $hosts,
        $shards,
        $replicas,
        $index_name,
        $document_type,
        $tenant_id,
        DispatcherInterface $jobs,
        LoggerInterface $logger,
        $is_on_demand = false
    )
    {
        $this->hosts = $hosts;
        $this->shards = $shards;
        $this->replicas = $replicas;
        $this->index_name = $index_name;
        $this->document_type = $document_type;
        $this->tenant_id = $tenant_id;
        $this->jobs = $jobs;
        $this->logger = $logger;
        $this->is_on_demand = $is_on_demand;
    }

    /**
     * @return array
     */
    public function getHosts()
    {
        return $this->hosts;
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexName()
    {
        return $this->index_name;
    }

    /**
     * @return string
     */
    public function getDocumentType()
    {
        return $this->document_type;
    }

    /**
     * @return int
     */
    public function getTenantId()
    {
        return $this->tenant_id;
    }

    /**
     * {@inheritdoc}
     */
    public function getDocumentMapping()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getNumberOfShards()
    {
        return $this->shards;
    }

    /**
     * {@inheritdoc}
     */
    public function getNumberOfReplicas()
    {
        return $this->replicas;
    }

    /**
     * @return bool
     */
    protected function isOnDemand()
    {
        return $this->is_on_demand;
    }

    /**
     * Return true if this adapter is ready to be used.
     *
     * @return bool
     */
    protected function isReady()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getJobsDispatcher()
    {
        return $this->jobs;
    }
}
