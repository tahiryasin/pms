<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

interface IHistory
{
    public function getHistory(): array;
    public function getVerboseHistory(Language $language): array;
    public function getHistoryFields(): array;
    public function addHistoryFields(string ...$field_names): void;
    public function getLatestModification(): ?ModificationLog;
}
