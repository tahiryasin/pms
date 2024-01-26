<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Foundation\Text\HtmlCleaner;

use ActiveCollab\Foundation\Text\HtmlCleaner\AllowedTag\AllowedTag;
use ActiveCollab\Foundation\Text\HtmlCleaner\AllowedTag\AllowedTagInterface;
use HTMLPurifier;
use HTMLPurifier_Config;
use simple_html_dom;

class HtmlCleaner implements HtmlCleanerInterface
{
    public function __construct(AllowedTagInterface ...$extra_allowed_tags)
    {
        foreach (self::DEFAULT_ALLOWED_TAGS as $tag_name => $attributes) {
            $this->allowed_tags[$tag_name] = new AllowedTag($tag_name, ...$attributes);
        }

        foreach ($extra_allowed_tags as $allowed_tag) {
            $this->allowTag($allowed_tag);
        }
    }

    public function cleanUp(string $html, callable $extra_dom_manipulation = null): string
    {
        $html = trim($html);

        if (strlen_utf($html) > 0) {
            // Strip raw embeded images
            $html = preg_replace(
                '/<img[^>]+src[\\s=\'"]+data\:(image\/.*)\;base64\,([^"\'>\\s]+)[^>]+>/is',
                '',
                $html
            );

            // Strips images with webkit-fake-url://
            $html = preg_replace(
                '/<img[^>]+src[\\s=\'"]+webkit-fake-url\:\/\/[^"\'>\\s]+[^>]+>/is',
                '',
                $html
            );

            $html = $this->getHtmlPurifier()->purify($html, $this->getHtmlPurifierConfig());

            $dom = $this->htmlToDom($html);

            if ($dom) {
                // Remove Apple style class SPAN-s
                $elements = $dom->find('span[class=Apple-style-span]');
                if (is_foreachable($elements)) {
                    foreach ($elements as $element) {
                        $element->outertext = $element->plaintext;
                    }
                }

                // Remove empty paragraphs
                foreach ($dom->find('p') as $element) {
                    $cleaned_up_content = trim(str_replace('&nbsp;', ' ', strip_tags($element->innertext)));

                    // Empty paragraph (non-breaking spaces are converted to spaces so trim can remove them)?
                    if (empty($cleaned_up_content)) {
                        if (strpos($element->innertext, 'img')) {
                            continue;
                        }

                        $element->outertext = '';
                    }
                }

                if ($extra_dom_manipulation) {
                    call_user_func($extra_dom_manipulation, $dom);
                }

                $html = (string) $dom;
            }

            return $html;
        }

        return '';
    }

    private $html_purifier;
    private $html_purifier_config;

    private function getHtmlPurifier(): HTMLPurifier
    {
        if (empty($this->html_purifier)) {
            $this->html_purifier = new HTMLPurifier();
        }

        return $this->html_purifier;
    }

    private function getHtmlPurifierConfig(): HTMLPurifier_Config
    {
        if (empty($this->html_purifier_config)) {
            $this->html_purifier_config = HTMLPurifier_Config::createDefault();

            // Enable likification.
            $this->html_purifier_config->set('AutoFormat.Linkify', true);
            $this->html_purifier_config->set('AutoFormat.PurifierLinkify', true);

            // Allow tags and attributes.
            $formatted_whitelisted_tags = [];

            foreach ($this->getAllowedTags() as $allowed_tag) {
                $formatted_whitelisted_tags[] = $allowed_tag->getTagName();

                foreach ($allowed_tag->getAllowedAttributes() as $allowed_attribute) {
                    $formatted_whitelisted_tags[] = sprintf(
                        '%s[%s]',
                        $allowed_tag->getTagName(),
                        $allowed_attribute
                    );
                }
            }

            $this->html_purifier_config->set('HTML.Allowed', implode(',', $formatted_whitelisted_tags));

            $definition = $this->html_purifier_config->getHTMLDefinition(true);

            foreach ($this->getAllowedTags() as $allowed_tag) {
                foreach ($allowed_tag->getAllowedAttributes() as $allowed_attribute) {
                    $definition->addAttribute($allowed_tag->getTagName(), $allowed_attribute, 'Text');
                }
            }
        }

        return $this->html_purifier_config;
    }

    private function htmlToDom(string $html): simple_html_dom
    {
        $dom = new simple_html_dom(
            null,
            true,
            true,
            'UTF-8',
            "\r\n"
        );
        $dom->load($html, true, true);

        return $dom;
    }

    /**
     * @var AllowedTagInterface[]
     */
    private $allowed_tags = [];

    public function getAllowedTags(): array
    {
        return $this->allowed_tags;
    }

    public function allowTag(AllowedTagInterface $allowed_tag): void
    {
        if ($this->isTagAllowed($allowed_tag->getTagName())) {
            $this->allowed_tags[$allowed_tag->getTagName()]->allowAttributes(...$allowed_tag->getAllowedAttributes());
        } else {
            $this->allowed_tags[$allowed_tag->getTagName()] = $allowed_tag;
        }

        $this->html_purifier_config = null;
    }

    public function isTagAllowed(string $tag_name): bool
    {
        return !empty($this->allowed_tags[$tag_name]);
    }

    public function isTagAttributeAllowed(string $tag_name, string $attribute_name): bool
    {
        return $this->isTagAllowed($tag_name) && $this->allowed_tags[$tag_name]->isAttributeAllowed($attribute_name);
    }
}
