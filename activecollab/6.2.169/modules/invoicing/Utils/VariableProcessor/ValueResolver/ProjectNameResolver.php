<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Module\Invoicing\Utils\VariableProcessor\ValueResolver;

use ActiveCollab\Foundation\Localization\LanguageInterface;
use ActiveCollab\Foundation\Text\VariableProcessor\ValueResolver\ValueResolverInterface;
use ActiveCollab\Foundation\Wrappers\DataObjectPool\DataObjectPoolInterface;
use Project;

class ProjectNameResolver implements ValueResolverInterface
{
    private $project_id;
    private $data_object_pool;

    public function __construct(?int $project_id, DataObjectPoolInterface $data_object_pool)
    {
        $this->project_id = $project_id;
        $this->data_object_pool = $data_object_pool;
    }

    public function getAvailableVariableNames(): array
    {
        return [
            'project-name',
        ];
    }

    public function getVariableReplacements(LanguageInterface $language): array
    {
        $project = $this->data_object_pool->get(Project::class, $this->project_id);

        return [
            'project-name' => $project instanceof Project ? $project->getName() : 'Unknown Project',
        ];
    }
}
