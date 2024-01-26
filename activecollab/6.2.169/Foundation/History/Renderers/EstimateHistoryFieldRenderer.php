<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Foundation\History\Renderers;

use Language;

class EstimateHistoryFieldRenderer implements HistoryFieldRendererInterface
{
    public function render($old_value, $new_value, Language $language): ?string
    {
        if ($old_value && $new_value) {
            return lang(
                'Estimate changed from <b>:old_value</b> to <b>:new_value</b> hours',
                [
                    'old_value' => $old_value,
                    'new_value' => $new_value, ],
                true,
                $language
            );
        }

        if ($new_value) {
            return lang(
                'Estimate set to <b>:new_value</b> hours',
                [
                    'new_value' => $new_value,
                ],
                true,
                $language
            );
        }

        if ($old_value) {
            return lang('Estimate removed', null, true, $language);
        }

        return null;
    }
}
