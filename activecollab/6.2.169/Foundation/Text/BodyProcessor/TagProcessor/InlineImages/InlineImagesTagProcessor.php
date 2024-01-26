<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Foundation\Text\BodyProcessor\TagProcessor\InlineImages;

use ActiveCollab\Foundation\Models\IdentifiableInterface;
use ActiveCollab\Foundation\Text\BodyProcessor\BodyProcessorInterface;
use ActiveCollab\Foundation\Text\BodyProcessor\TagProcessor\TagProcessor;
use ActiveCollab\Foundation\Text\HtmlCleaner\AllowedTag\AllowedTag;
use ActiveCollab\Module\System\Utils\InlineImageDetailsResolver\InlineImageDetailsResolverInterface;
use Exception;
use InvalidParamError;
use simple_html_dom;
use Thumbnails;

class InlineImagesTagProcessor extends TagProcessor
{
    private $inline_image_details_resolver;

    public function __construct(InlineImageDetailsResolverInterface $inline_image_details_resolver)
    {
        $this->inline_image_details_resolver = $inline_image_details_resolver;
    }

    public function getAllowedTags(): array
    {
        return [
            new AllowedTag('img', 'image-type', 'object-id'),
        ];
    }

    public function processForStorage(simple_html_dom $dom): array
    {
        $result = [];

        $elements = $dom->find('img[image-type=attachment]');

        if ($elements) {
            foreach ($elements as $element) {
                $uploaded_image_code = array_var($element->attr, 'object-id');

                if ($uploaded_image_code && strlen($uploaded_image_code) == 40) {
                    $result[] = new InlineImageArtifact($uploaded_image_code);
                }
            }
        }

        return $result;
    }

    public function processForDisplay(simple_html_dom $dom, IdentifiableInterface $context, string $display): void
    {
        $inline_image_placeholder_placeholders = $dom->find('img[image-type=attachment]');
        if (!empty($inline_image_placeholder_placeholders)) {
            $parent_type = $context->getType();
            $parent_id = $context->getId();

            [
                $max_inline_object_width,
                $max_inline_object_height,
            ] = $this->getAttachmentDimensions($display);

            foreach ($inline_image_placeholder_placeholders as $inline_image_placeholder) {
                $image_id = array_var($inline_image_placeholder->attr, 'object-id', null);

                if ($image_id) {
                    try {
                        $inline_image = $this->inline_image_details_resolver->getDetailsByParent(
                            $image_id,
                            $parent_type,
                            $parent_id
                        );

                        if (empty($inline_image['id'])) {
                            throw new InvalidParamError(
                                'id',
                                array_key_exists('id', $inline_image) ? $inline_image['id'] : null
                            );
                        }

                        if (empty($inline_image['name'])) {
                            throw new InvalidParamError(
                                'name',
                                array_key_exists('name', $inline_image) ? $inline_image['name'] : null
                            );
                        }

                        if (empty($inline_image['thumbnail_url'])) {
                            throw new InvalidParamError(
                                'thumbnail_url',
                                array_key_exists('thumbnail_url', $inline_image)
                                    ? $inline_image['thumbnail_url']
                                    : null
                            );
                        }

                        $inline_image['thumbnail_url'] = str_replace(
                            '--WIDTH--',
                            $max_inline_object_width,
                            $inline_image['thumbnail_url']
                        );
                        $inline_image['thumbnail_url'] = str_replace(
                            '--HEIGHT--',
                            $max_inline_object_height,
                            $inline_image['thumbnail_url']
                        );
                        $inline_image['thumbnail_url'] = str_replace(
                            '--SCALE--',
                            Thumbnails::SCALE,
                            $inline_image['thumbnail_url']
                        );

                        if ($inline_image_placeholder->parent
                            && $inline_image_placeholder->parent->tag
                            && $inline_image_placeholder->parent->tag == 'a'
                        ) {
                            $inline_image_placeholder->outertext = sprintf(
                                '<div class="rich_text_inline_image_wrapper"><img src="%s" alt="%s" attachment-id="%d" /></div>',
                                clean($inline_image['thumbnail_url']),
                                clean($inline_image['name']),
                                $inline_image['id']
                            );
                        } else {
                            if (!isset($inline_image['download_url'])) {
                                throw new InvalidParamError('download_url', $inline_image['download_url']);
                            }
                            $inline_image_placeholder->outertext = sprintf(
                                '<div class="rich_text_inline_image_wrapper"><a href="%s" target="_blank"><img src="%s" alt="%s" attachment-id="%d" /></a></div>',
                                clean($inline_image['download_url']),
                                clean($inline_image['thumbnail_url']),
                                clean($inline_image['name']),
                                $inline_image['id']
                            );
                        }
                    } catch (Exception $e) {
                        $inline_image_placeholder->outertext = '';
                    }
                } else {
                    $inline_image_placeholder->outertext = '';
                }
            }
        }
    }

    private function getAttachmentDimensions(string $display): array
    {
        if ($display === BodyProcessorInterface::DISPLAY_EMAIL) {
            $max_inline_object_width = 500;
            $max_inline_object_height = 500;
        } else {
            $max_inline_object_width = 800;
            $max_inline_object_height = 800;
        }

        return [
            $max_inline_object_width,
            $max_inline_object_height,
        ];
    }
}
