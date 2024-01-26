<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Foundation\Text\BodyProcessor\TagProcessor\Mentions;

use ActiveCollab\Foundation\Models\IdentifiableInterface;
use ActiveCollab\Foundation\Text\BodyProcessor\BodyProcessorInterface;
use ActiveCollab\Foundation\Text\BodyProcessor\TagProcessor\TagProcessor;
use ActiveCollab\Foundation\Text\HtmlCleaner\AllowedTag\AllowedTag;
use simple_html_dom;

class MentionsTagProcessor extends TagProcessor
{
    public function getAllowedTags(): array
    {
        return [
            new AllowedTag('span', 'data-user-id'),
        ];
    }

    public function processForStorage(simple_html_dom $dom): array
    {
        $result = [];

        $elements = $dom->find('span.new_mention');

        if ($elements) {
            foreach ($elements as $element) {
                $user_id = (int) array_var($element->attr, 'data-user-id');

                if ($user_id && empty($result[$user_id])) {
                    $result[$user_id] = new MentionArtifact($user_id);
                }

                $element->outertext = '<span class="mention">' . $element->innertext . '</span>';
            }
        }

        return array_values($result);
    }

    public function processForDisplay(simple_html_dom $dom, IdentifiableInterface $context, string $display): void
    {
        if ($display === BodyProcessorInterface::DISPLAY_EMAIL) {
            $mentions = $dom->find('span.mention');

            if (!empty($mentions)) {
                foreach ($mentions as $mention) {
                    if ($mention->innertext) {
                        $inner_text = $mention->innertext;
                    } elseif ($mention->plaintext) {
                        $inner_text = $mention->plaintext;
                    } else {
                        $inner_text = 'mention';
                    }

                    $mention->outertext = sprintf(
                        '<span style="background-color: #D9EEFF; padding: 0 2px; border: 1px solid #AEBFCC; border-radius: 3px;">@%s</span>',
                        clean($inner_text)
                    );
                }
            }
        }
    }
}
