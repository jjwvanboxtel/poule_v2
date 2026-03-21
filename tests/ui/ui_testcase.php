<?php
/**
 * Base test case for UI regression suites in the poule_v2 application.
 *
 * Provides shared helper methods for asserting server-rendered HTML output
 * so that downstream UI tests stay concise and consistent.
 *
 * Usage:
 *   require_once('./ui/ui_testcase.php');
 *
 *   class TestOfMyScreenRendering extends UiTestCase {
 *       function testTitlePanelIsPresent() {
 *           $html = '<h1 class="page-title">Competities</h1>';
 *           $this->assertContainsTag($html, 'h1', 'Competities');
 *       }
 *   }
 *
 * @package   poule_v2
 * @author    Jaap van Boxtel
 * @copyright 2024
 */

require_once('./simpletest/autorun.php');

/**
 * Base class for all UI regression tests.
 *
 * Extends SimpleTest's UnitTestCase with HTML-specific assertion helpers
 * that keep UI specs readable without requiring a running HTTP server.
 */
class UiTestCase extends UnitTestCase
{
    // ------------------------------------------------------------------
    // HTML assertion helpers
    // ------------------------------------------------------------------

    /**
     * Assert that a HTML string contains a tag with the given content.
     *
     * @param string $html        The HTML string to search in.
     * @param string $tag         The tag name to look for (e.g. 'h1', 'td').
     * @param string $content     The text content expected inside the tag.
     * @param string $message     Optional failure message.
     */
    function assertContainsTag($html, $tag, $content, $message = '')
    {
        $pattern = '/<' . preg_quote($tag, '/') . '[^>]*>.*?' . preg_quote($content, '/') . '.*?<\/' . preg_quote($tag, '/') . '>/si';
        $result  = (bool) preg_match($pattern, $html);
        $msg     = $message ?: "Expected <{$tag}> containing \"{$content}\" in rendered HTML.";
        $this->assertTrue($result, $msg);
    }

    /**
     * Assert that a HTML string does NOT contain a tag with the given content.
     *
     * @param string $html        The HTML string to search in.
     * @param string $tag         The tag name to look for.
     * @param string $content     The text content that must NOT appear.
     * @param string $message     Optional failure message.
     */
    function assertNotContainsTag($html, $tag, $content, $message = '')
    {
        $pattern = '/<' . preg_quote($tag, '/') . '[^>]*>.*?' . preg_quote($content, '/') . '.*?<\/' . preg_quote($tag, '/') . '>/si';
        $result  = (bool) preg_match($pattern, $html);
        $msg     = $message ?: "Expected <{$tag}> containing \"{$content}\" to be absent from rendered HTML.";
        $this->assertFalse($result, $msg);
    }

    /**
     * Assert that a HTML string contains an element with the given CSS class.
     *
     * @param string $html        The HTML string to search in.
     * @param string $cssClass    The CSS class name to look for.
     * @param string $message     Optional failure message.
     */
    function assertContainsClass($html, $cssClass, $message = '')
    {
        $pattern = '/class=["\'][^"\']*\b' . preg_quote($cssClass, '/') . '\b[^"\']*["\']/i';
        $result  = (bool) preg_match($pattern, $html);
        $msg     = $message ?: "Expected CSS class \"{$cssClass}\" to be present in rendered HTML.";
        $this->assertTrue($result, $msg);
    }

    /**
     * Assert that a HTML string does NOT contain an element with the given
     * CSS class.
     *
     * @param string $html        The HTML string to search in.
     * @param string $cssClass    The CSS class name that must be absent.
     * @param string $message     Optional failure message.
     */
    function assertNotContainsClass($html, $cssClass, $message = '')
    {
        $pattern = '/class=["\'][^"\']*\b' . preg_quote($cssClass, '/') . '\b[^"\']*["\']/i';
        $result  = (bool) preg_match($pattern, $html);
        $msg     = $message ?: "Expected CSS class \"{$cssClass}\" to be absent from rendered HTML.";
        $this->assertFalse($result, $msg);
    }

    /**
     * Assert that a HTML string contains an <a> tag pointing to the given href.
     *
     * @param string $html        The HTML string to search in.
     * @param string $href        The href value (or substring) to look for.
     * @param string $message     Optional failure message.
     */
    function assertContainsLink($html, $href, $message = '')
    {
        $pattern = '/<a\s[^>]*href=["\'][^"\']*' . preg_quote($href, '/') . '[^"\']*["\']/i';
        $result  = (bool) preg_match($pattern, $html);
        $msg     = $message ?: "Expected a link with href containing \"{$href}\" to be present.";
        $this->assertTrue($result, $msg);
    }

    /**
     * Assert that the rendered HTML contains the given substring.
     *
     * Convenience wrapper around assertTrue/assertPattern for plain-text
     * content checks that do not require tag context.
     *
     * @param string $html        The HTML string to search in.
     * @param string $needle      The substring to look for.
     * @param string $message     Optional failure message.
     */
    function assertHtmlContains($html, $needle, $message = '')
    {
        $result = (strpos($html, $needle) !== false);
        $msg    = $message ?: "Expected HTML to contain \"{$needle}\".";
        $this->assertTrue($result, $msg);
    }

    /**
     * Assert that the rendered HTML does NOT contain the given substring.
     *
     * @param string $html        The HTML string to search in.
     * @param string $needle      The substring that must be absent.
     * @param string $message     Optional failure message.
     */
    function assertHtmlNotContains($html, $needle, $message = '')
    {
        $result = (strpos($html, $needle) !== false);
        $msg    = $message ?: "Expected HTML NOT to contain \"{$needle}\".";
        $this->assertFalse($result, $msg);
    }
}
?>
