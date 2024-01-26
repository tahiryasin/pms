<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

use ActiveCollab\Foundation\Urls\Router\UrlMatcher\UrlMatcherInterface;

$this->map(
    'user_time_records',
    'users/:user_id/time-records',
    [
        'controller' => 'user_time_records',
        'action' => [
            'GET' => 'index',
        ],
    ],
    [
        'user_id' => UrlMatcherInterface::MATCH_ID,
    ]
);

$this->map(
    'user_time_records_filtered_by_date',
    'users/:user_id/time-records/filtered-by-date',
    [
        'controller' => 'user_time_records',
        'action' => [
            'GET' => 'filtered_by_date',
        ],
    ],
    [
        'user_id' => UrlMatcherInterface::MATCH_ID,
    ]
);

$this->mapResource(
    'job_types',
    null,
    function ($collection) {
        $this->map(
            "$collection[name]_default",
            "$collection[path]/default",
            [
                'controller' => $collection['controller'],
                'action' => [
                    'GET' => 'view_default',
                    'PUT' => 'set_default',
                ],
            ],
            $collection['requirements']
        );

        $this->map(
            "$collection[name]_batch_edit",
            "$collection[path]/edit-batch",
            [
                'controller' => $collection['controller'],
                'action' => [
                    'PUT' => 'batch_edit',
                ],
            ],
            $collection['requirements']
        );
    }
);

$this->mapResource(
    'expense_categories',
    null,
    function ($collection) {
        $this->map(
            "$collection[name]_default",
            "$collection[path]/default",
            [
                'controller' => $collection['controller'],
                'action' => [
                    'GET' => 'view_default',
                    'PUT' => 'set_default',
                ],
            ],
            $collection['requirements']
        );

        $this->map(
            "$collection[name]_batch_edit",
            "$collection[path]/edit-batch",
            [
                'controller' => $collection['controller'],
                'action' => [
                    'PUT' => 'batch_edit',
                ],
            ],
            $collection['requirements']
        );
    }
);

$this->map(
    'stopwatches_index',
    'stopwatches',
    [
        'controller' => 'stopwatch',
        'action' => [
            'GET' => 'index',
        ],
    ]
);

$this->map(
    'stopwatches_start',
    'stopwatches/start',
    [
        'controller' => 'stopwatch',
        'action' => [
            'POST' => 'start',
        ],
    ]
);

$this->map(
    'stopwatches_pause',
    'stopwatches/pause/:id',
    [
        'controller' => 'stopwatch',
        'action' => [
            'PUT' => 'pause',
        ],
    ],
    [
        'id' => UrlMatcherInterface::MATCH_ID,
    ]
);

$this->map(
    'stopwatches_resume',
    'stopwatches/resume/:id',
    [
        'controller' => 'stopwatch',
        'action' => [
            'PUT' => 'resume',
        ],
    ],
    [
        'id' => UrlMatcherInterface::MATCH_ID,
    ]
);
