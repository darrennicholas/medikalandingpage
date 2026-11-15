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
            if (in_array($tag, ['p', 'br', 'strong', 'b', 'em', 'i', 'ul', 'ol', 'li', 'a'], true)) {
                $target = $output->createElement($tag);
                if ($tag === 'a') {
                    $href = trim($node->getAttribute('href'));
                    if (preg_match('~^(?:https://[^\s/]+|/(?!/))~i', $href) && ! preg_match('/[\x00-\x20\x7f\\\\]/', $href)) {
                        $target->setAttribute('href', $href);
                    }
                }
                $parent->appendChild($target);
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
