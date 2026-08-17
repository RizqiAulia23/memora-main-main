<?php

namespace Tests\Feature;

use App\Services\RichTextSanitizer;
use PHPUnit\Framework\TestCase;

class RichTextSanitizerTest extends TestCase
{
    private function sanitize(?string $html): string
    {
        return (new RichTextSanitizer)->sanitize($html);
    }

    public function test_plain_javascript_href_is_stripped(): void
    {
        $sanitized = $this->sanitize('<a href="javascript:alert(1)">click</a>');

        $this->assertStringNotContainsString('href', $sanitized);
        $this->assertStringContainsString('click', $sanitized);
    }

    public function test_entity_encoded_javascript_href_is_stripped(): void
    {
        $sanitized = $this->sanitize('<a href="java&#x73;cript:alert(document.cookie)">click</a>');

        $this->assertStringNotContainsString('href', $sanitized);
        $this->assertStringContainsString('click', $sanitized);
    }

    public function test_entity_encoded_event_handler_is_stripped(): void
    {
        $sanitized = $this->sanitize('<p on&#109;ouseover="alert(1)">hi</p>');

        $this->assertStringNotContainsString('onmouseover', $sanitized);
        $this->assertStringNotContainsString('alert', $sanitized);
    }

    public function test_entity_encoded_script_tag_is_removed(): void
    {
        $sanitized = $this->sanitize('<p>hi</p>&lt;script&gt;alert(1)&lt;/script&gt;');

        $this->assertStringNotContainsString('<script', $sanitized);
    }

    public function test_data_uri_href_is_stripped(): void
    {
        $sanitized = $this->sanitize('<a href="data:text/html;base64,PHNjcmlwdD4=">x</a>');

        $this->assertStringNotContainsString('href', $sanitized);
    }

    public function test_vbscript_href_is_stripped(): void
    {
        $sanitized = $this->sanitize('<a href="vbscript:msgbox(1)">x</a>');

        $this->assertStringNotContainsString('href', $sanitized);
    }

    public function test_safe_hrefs_are_kept(): void
    {
        $sanitized = $this->sanitize(
            '<a href="https://example.com">a</a> <a href="mailto:x@y.z">b</a> <a href="/memories">c</a> <a href="#top">d</a>'
        );

        $this->assertStringContainsString('https://example.com', $sanitized);
        $this->assertStringContainsString('mailto:x@y.z', $sanitized);
        $this->assertStringContainsString('href="/memories"', $sanitized);
        $this->assertStringContainsString('href="#top"', $sanitized);
    }

    public function test_style_with_javascript_url_is_stripped(): void
    {
        $sanitized = $this->sanitize('<p style="background-image:url(javascript:alert(1))">Hello</p><p style="color:red">Still red</p>');

        $this->assertStringNotContainsString('javascript:', $sanitized);
        $this->assertStringContainsString('Still red', $sanitized);
        $this->assertStringContainsString('color:red', $sanitized);
    }

    public function test_null_and_plain_text_pass_through(): void
    {
        $this->assertSame('', $this->sanitize(null));
        $this->assertSame('hello world', $this->sanitize('hello world'));
    }
}
