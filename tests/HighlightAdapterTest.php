<?php

use PHPUnit\Framework\TestCase;

class HighlightAdapterTest extends TestCase
{
    protected $kirby;

    public function setUp(): void
    {
        $this->kirby = new \Kirby\Cms\App([]);
    }

    public function testKirbyTextExplicitHighlighting()
    {
        $text = <<<'EOD'
            ```css
            .foo {
                color: var(--bar);
            }
            ```

            ```js
            export const foo = 'bar'
            ```
            EOD;

        $expectedHtml = <<<'EOD'
            <pre class="hljs"><code data-language="css"><span class="hljs-selector-class">.foo</span> {
                <span class="hljs-attribute">color</span>: <span class="hljs-built_in">var</span>(--bar);
            }</code></pre>
            <pre class="hljs"><code data-language="js"><span class="hljs-keyword">export</span> <span class="hljs-keyword">const</span> foo = <span class="hljs-string">'bar'</span></code></pre>
            EOD;

        $this->assertEquals($expectedHtml, $this->kirby->kirbytext($text));
    }

    public function testKirbyTextSkipHighlighting()
    {
        $text = <<<'EOD'
            ```
            .foo {
                color: var(--bar);
            }
            ```

            ```js
            export const foo = 'bar'
            ```
            EOD;

        $expectedHtml = <<<'EOD'
            <pre><code>.foo {
                color: var(--bar);
            }</code></pre>
            <pre class="hljs"><code data-language="js"><span class="hljs-keyword">export</span> <span class="hljs-keyword">const</span> foo = <span class="hljs-string">'bar'</span></code></pre>
            EOD;

        $this->assertEquals($expectedHtml, $this->kirby->kirbytext($text));
    }

    public function testKirbyTextAutoHighlighting()
    {
        $app = $this->kirby->clone([
            'options' => [
                'johannschopplich.highlighter.autodetect' => true
            ]
        ]);

        $text = <<<'EOD'
            ```
            .foo {
                color: var(--bar);
            }
            ```

            ```js
            export const foo = 'bar'
            ```
            EOD;

        $expectedHtml = <<<'EOD'
            <pre class="hljs"><code data-language=""><span class="hljs-selector-class">.foo</span> {
                <span class="hljs-attribute">color</span>: <span class="hljs-built_in">var</span>(--bar);
            }</code></pre>
            <pre class="hljs"><code data-language="js"><span class="hljs-keyword">export</span> <span class="hljs-keyword">const</span> foo = <span class="hljs-string">'bar'</span></code></pre>
            EOD;

        $this->assertEquals($expectedHtml, $app->kirbytext($text));
    }

    public function testUmlautsInNormalKirbyText()
    {
        $text = 'Ä, ö, ü';
        $expectedHtml = '<p>Ä, ö, ü</p>';

        $this->assertEquals($expectedHtml, $this->kirby->kirbytext($text));
    }

    public function testUmlautsInHighlightedKirbyText()
    {
        $text = <<<'EOD'
            ```
            Ä, ö, ü
            ```
            EOD;
        $expectedHtml = '<pre><code>&Auml;, &ouml;, &uuml;</code></pre>';

        $this->assertEquals($expectedHtml, $this->kirby->kirbytext($text));
    }

    public function testCodeBlockHighlighting()
    {
        $code = <<<'EOD'
            .foo {
                color: var(--bar);
            }
            EOD;

        $expectedHtml = <<<'EOD'
            <pre class="hljs"><code data-language="css"><span class="hljs-selector-class">.foo</span> {
                <span class="hljs-attribute">color</span>: <span class="hljs-built_in">var</span>(--bar);
            }</code></pre>

            EOD;

        $block = new \Kirby\Cms\Block([
            'type' => 'code',
            'content' => [
                'language' => 'css',
                'code' => $code
            ]
        ]);

        $this->assertEquals(
            $expectedHtml,
            $block->toHtml()
        );
    }

    public function testCodeBlockHighlightingWithFallback()
    {
        $code = <<<'EOD'
            .foo {
                color: var(--bar);
            }
            EOD;

        $expectedHtml = <<<'EOD'
            <pre class="hljs"><code data-language="plaintext">.foo {
                color: var(--bar);
            }</code></pre>

            EOD;

        $block = new \Kirby\Cms\Block([
            'type' => 'code',
            'content' => [
                'language' => 'not-a-language',
                'code' => $code
            ]
        ]);

        $this->assertEquals(
            $expectedHtml,
            $block->toHtml()
        );
    }
}
