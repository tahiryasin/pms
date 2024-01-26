<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Foundation\Text\BodyProcessor\ProcessedBody;

use ActiveCollab\Foundation\Text\BodyProcessor\Artifact\ArtifactInterface;

interface ProcessedBodyInterface
{
    public function getProcessedHtml(): string;

    /**
     * @return ArtifactInterface[]
     */
    public function getArtifacts(): array;

    /**
     * @param  string              $artifact_type
     * @return ArtifactInterface[]
     */
    public function getArtifactsByType(string $artifact_type): array;
}
