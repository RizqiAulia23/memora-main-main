<?php

namespace App\Services;

class RichTextSanitizer
{
    private const ALLOWED_TAGS = '<p><br><strong><b><em><i><u><s><blockquote><ul><ol><li><h2><h3><h4><a><code><pre>';

    private const SAFE_SCHEMES = ['http', 'https', 'mailto', 'tel'];

    public function sanitize(?string $html): string
    {
        if ($html === null) {
            return '';
        }

        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $html = strip_tags($html, self::ALLOWED_TAGS);
        $html = preg_replace('/\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? $html;
        $html = preg_replace_callback(
            '/href\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i',
            fn (array $matches): string => $this->safeHref($matches[0], $matches[1]),
            $html,
        ) ?? $html;
        $html = preg_replace('#<(script|style|iframe|object|embed)[^>]*>.*?</\1>#is', '', $html) ?? $html;
        $html = preg_replace_callback(
            '/style\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i',
            function (array $matches): string {
                $value = trim($matches[1], "\"'");

                if (preg_match('/(url\s*\(|javascript:|expression\(|@import|behavior:)/i', $value)) {
                    return '';
                }

                return $matches[0];
            },
            $html,
        ) ?? $html;

        return trim($html);
    }

    private function safeHref(string $attribute, string $value): string
    {
        $value = trim($value, "\"'");

        if ($value === '' || str_starts_with($value, '#') || str_starts_with($value, '/')) {
            return $attribute;
        }

        if (preg_match('/^\s*([a-z][a-z0-9+.-]*)\s*:/i', $value, $scheme)) {
            return in_array(strtolower($scheme[1]), self::SAFE_SCHEMES, true) ? $attribute : '';
        }

        return $attribute;
    }
}
