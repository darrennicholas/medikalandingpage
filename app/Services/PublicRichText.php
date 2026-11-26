<?php

namespace App\Services;

class PublicRichText
{
    public static function render(?string $value): string
    {
        if (blank($value)) {
            return '';
        }
        $source = new \DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $source->loadHTML('<?xml encoding="UTF-8"><div>'.$value.'</div>', LIBXML_NONET | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $output = new \DOMDocument('1.0', 'UTF-8');
        $container = $output->createElement('div');
        $output->appendChild($container);
        $copy = function (\DOMNode $node, \DOMNode $parent) use (&$copy, $output): void {
            if ($node instanceof \DOMText) {
                $parent->appendChild($output->createTextNode($node->nodeValue ?? ''));

                return;
            }
            if (! $node instanceof \DOMElement || in_array(strtolower($node->tagName), ['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math', 'template'], true)) {
                return;
            }
            $tag = strtolower($node->tagName);
            $target = $parent;
            if ($tag === 'img' && preg_match('~^/media/[0-9]+/file$~', $node->getAttribute('src'))) {
                $image = $output->createElement('img');
                $image->setAttribute('src', $node->getAttribute('src'));
                $image->setAttribute('alt', mb_substr($node->getAttribute('alt'), 0, 200));
                $image->setAttribute('loading', 'lazy');
                $parent->appendChild($image);

                return;
            }
            $colorStyle = strtolower(preg_replace('/\s+/', '', $node->getAttribute('style')) ?? '');
            if ($tag === 'span' && in_array($colorStyle, ['color:#315bd5', 'color:rgb(49,91,213)', 'color:rgb(49,91,213);'], true)) {
                $target = $output->createElement('span');
                $target->setAttribute('style', 'color:#315bd5');
                $parent->appendChild($target);
            }
            if (in_array($tag, ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 'ul', 'ol', 'li', 'a'], true)) {
                $target = $output->createElement($tag);
                $classes = array_intersect(preg_split('/\s+/', $node->getAttribute('class')) ?: [], ['ql-align-center', 'ql-align-right', 'ql-align-justify', 'ql-size-small', 'ql-size-large', 'ql-size-huge', 'ql-size-12px', 'ql-size-14px', 'ql-size-16px', 'ql-size-18px', 'ql-size-20px', 'ql-size-24px', 'ql-size-28px', 'ql-size-32px', 'ql-size-36px']);
                if ($classes !== []) {
                    $target->setAttribute('class', implode(' ', $classes));
                }
                if ($tag === 'a') {
                    $href = trim($node->getAttribute('href'));
                    if (preg_match('~^(?:https://[^\s/]+|/(?!/))~i', $href) && ! preg_match('/[\x00-\x20\x7f\\\\]/', $href)) {
                        $target->setAttribute('href', $href);
                    }
                }
                $parent->appendChild($target);
            }
            if ($tag === 'span' && $target === $parent) {
                $classes = array_intersect(preg_split('/\s+/', $node->getAttribute('class')) ?: [], ['ql-size-small', 'ql-size-large', 'ql-size-huge', 'ql-size-12px', 'ql-size-14px', 'ql-size-16px', 'ql-size-18px', 'ql-size-20px', 'ql-size-24px', 'ql-size-28px', 'ql-size-32px', 'ql-size-36px']);
                if ($classes !== []) {
                    $target = $output->createElement('span');
                    $target->setAttribute('class', implode(' ', $classes));
                    $parent->appendChild($target);
                }
            }
            foreach ($node->childNodes as $child) {
                $copy($child, $target);
            }
        };
        foreach ($source->documentElement?->childNodes ?? [] as $child) {
            $copy($child, $container);
        }
        $result = '';
        foreach ($container->childNodes as $child) {
            $result .= $output->saveHTML($child);
        }

        return $result;
    }
}
