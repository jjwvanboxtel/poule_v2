<?php
/**
 * Shared markup assertions for alerts, forms, and list/overview tables in poule_v2.
 *
 * Extends {@see UiTestCase} with helpers that cover the three core markup
 * regions present on almost every module screen:
 *
 *   1. Alerts / status messages – both Bootstrap-aligned utility classes
 *      (`.alert`, `.alert-success`, etc.) and legacy inline message `<div>`s.
 *   2. Input forms – form presence, action/method verification, field and
 *      label assertions, submit/cancel buttons, and error-state indicators.
 *   3. List / overview tables – `table.list` structure, header columns, data
 *      row counts, action links, and add-link detection.
 *
 * Usage:
 *   require_once('./ui/ui_markup_assertions.php');
 *
 *   class TestOfMyFormRendering extends UiMarkupAssertions
 *   {
 *       function testSubmitButtonIsPresent()
 *       {
 *           $html = '<form action="" method="post">'
 *                 . '<input type="submit" name="submit" value="Save" />'
 *                 . '</form>';
 *           $this->assertFormPresent($html);
 *           $this->assertFormSubmitButton($html, 'Save');
 *       }
 *   }
 *
 * @package   poule_v2
 * @author    Jaap van Boxtel
 * @copyright 2024
 */

require_once('./simpletest/autorun.php');
require_once('./ui/ui_testcase.php');

/**
 * Assertion helpers for alert, form, and list markup regions.
 *
 * All assertions operate on a raw HTML string – no HTTP request is made –
 * which allows fast, focused unit-style tests for server-rendered output.
 */
class UiMarkupAssertions extends UiTestCase
{
    // ------------------------------------------------------------------
    // Alert / status message assertions
    // ------------------------------------------------------------------

    /**
     * Assert that at least one Bootstrap-aligned `.alert` element is present.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $message Optional failure message.
     */
    function assertAlertPresent($html, $message = '')
    {
        $msg = $message ?: 'Expected an .alert element to be present.';
        $this->assertContainsClass($html, 'alert', $msg);
    }

    /**
     * Assert that no `.alert` element is present.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $message Optional failure message.
     */
    function assertNoAlert($html, $message = '')
    {
        $msg = $message ?: 'Expected no .alert element to be present.';
        $this->assertNotContainsClass($html, 'alert', $msg);
    }

    /**
     * Assert that a `.alert-success` element is present.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $message Optional failure message.
     */
    function assertSuccessAlert($html, $message = '')
    {
        $msg = $message ?: 'Expected an .alert-success element to be present.';
        $this->assertContainsClass($html, 'alert-success', $msg);
    }

    /**
     * Assert that a `.alert-danger` element is present.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $message Optional failure message.
     */
    function assertDangerAlert($html, $message = '')
    {
        $msg = $message ?: 'Expected an .alert-danger element to be present.';
        $this->assertContainsClass($html, 'alert-danger', $msg);
    }

    /**
     * Assert that a `.alert-warning` element is present.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $message Optional failure message.
     */
    function assertWarningAlert($html, $message = '')
    {
        $msg = $message ?: 'Expected an .alert-warning element to be present.';
        $this->assertContainsClass($html, 'alert-warning', $msg);
    }

    /**
     * Assert that a `.alert-info` element is present.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $message Optional failure message.
     */
    function assertInfoAlert($html, $message = '')
    {
        $msg = $message ?: 'Expected an .alert-info element to be present.';
        $this->assertContainsClass($html, 'alert-info', $msg);
    }

    /**
     * Assert that an `.alert` element contains the given text.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $text    The expected text substring inside the alert.
     * @param string $message Optional failure message.
     */
    function assertAlertContains($html, $text, $message = '')
    {
        $pattern = '/<[^>]+class=["\'][^"\']*\balert\b[^"\']*["\'][^>]*>'
                 . '.*?'
                 . preg_quote($text, '/')
                 . '.*?<\//si';
        $result  = (bool) preg_match($pattern, $html);
        $msg     = $message ?: "Expected an alert element to contain \"{$text}\".";
        $this->assertTrue($result, $msg);
    }

    // ------------------------------------------------------------------
    // Form assertions
    // ------------------------------------------------------------------

    /**
     * Assert that a `<form>` element is present.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $message Optional failure message.
     */
    function assertFormPresent($html, $message = '')
    {
        $msg = $message ?: 'Expected a <form> element to be present.';
        $this->assertHtmlContains($html, '<form', $msg);
    }

    /**
     * Assert that a `<form>` element is absent.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $message Optional failure message.
     */
    function assertNoForm($html, $message = '')
    {
        $msg = $message ?: 'Expected no <form> element to be present.';
        $this->assertHtmlNotContains($html, '<form', $msg);
    }

    /**
     * Assert that the form's `action` attribute contains the given substring.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $action  Expected substring in the form action URL.
     * @param string $message Optional failure message.
     */
    function assertFormAction($html, $action, $message = '')
    {
        $pattern = '/<form\s[^>]*action=["\'][^"\']*'
                 . preg_quote($action, '/')
                 . '[^"\']*["\']/i';
        $result  = (bool) preg_match($pattern, $html);
        $msg     = $message ?: "Expected a form with action containing \"{$action}\".";
        $this->assertTrue($result, $msg);
    }

    /**
     * Assert that the form uses the given HTTP method.
     *
     * Comparison is case-insensitive.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $method  Expected HTTP method (e.g. 'post', 'get').
     * @param string $message Optional failure message.
     */
    function assertFormMethod($html, $method, $message = '')
    {
        $pattern = '/<form\s[^>]*method=["\']'
                 . preg_quote(strtolower($method), '/')
                 . '["\'][^>]*/i';
        $result  = (bool) preg_match($pattern, strtolower($html));
        $msg     = $message ?: "Expected a form with method \"{$method}\".";
        $this->assertTrue($result, $msg);
    }

    /**
     * Assert that a form field (input, select, or textarea) with the given
     * `name` attribute exists.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $name    The `name` attribute value to look for.
     * @param string $message Optional failure message.
     */
    function assertFormField($html, $name, $message = '')
    {
        $pattern = '/<(input|select|textarea)[^>]+name=["\']'
                 . preg_quote($name, '/')
                 . '["\'][^>]*/si';
        $result  = (bool) preg_match($pattern, $html);
        $msg     = $message ?: "Expected a form field with name \"{$name}\".";
        $this->assertTrue($result, $msg);
    }

    /**
     * Assert that a hidden field with the given `name` attribute exists.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $name    The `name` attribute value to look for.
     * @param string $message Optional failure message.
     */
    function assertHiddenField($html, $name, $message = '')
    {
        $pattern = '/<input[^>]+type=["\']hidden["\'][^>]+name=["\']'
                 . preg_quote($name, '/')
                 . '["\'][^>]*/i';
        $altPattern = '/<input[^>]+name=["\']'
                    . preg_quote($name, '/')
                    . '["\'][^>]+type=["\']hidden["\'][^>]*/i';
        $result = (bool) preg_match($pattern, $html)
               || (bool) preg_match($altPattern, $html);
        $msg    = $message ?: "Expected a hidden field with name \"{$name}\".";
        $this->assertTrue($result, $msg);
    }

    /**
     * Assert that a submit button (`type="submit" name="submit"`) is present.
     *
     * Optionally verify that its `value` attribute matches `$value`.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $value   Optional expected button label.
     * @param string $message Optional failure message.
     */
    function assertFormSubmitButton($html, $value = '', $message = '')
    {
        if ($value !== '')
        {
            $pattern    = '/<input[^>]+type=["\']submit["\'][^>]+value=["\']'
                        . preg_quote($value, '/')
                        . '["\'][^>]*/i';
            $altPattern = '/<input[^>]+value=["\']'
                        . preg_quote($value, '/')
                        . '["\'][^>]+type=["\']submit["\'][^>]*/i';
            $result = (bool) preg_match($pattern, $html)
                   || (bool) preg_match($altPattern, $html);
            $msg    = $message ?: "Expected a submit button with value \"{$value}\".";
        }
        else
        {
            $result = (bool) preg_match('/<input[^>]+type=["\']submit["\']/i', $html);
            $msg    = $message ?: 'Expected a submit button to be present.';
        }

        $this->assertTrue($result, $msg);
    }

    /**
     * Assert that a cancel/back button (`type="button"`) is present.
     *
     * The application renders cancel actions as `<input type="button" onclick="…">`.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $message Optional failure message.
     */
    function assertFormCancelButton($html, $message = '')
    {
        $result = (bool) preg_match('/<input[^>]+type=["\']button["\']/i', $html);
        $msg    = $message ?: 'Expected a cancel/back button (input[type="button"]) to be present.';
        $this->assertTrue($result, $msg);
    }

    /**
     * Assert that a visible label or span containing `$labelText` exists.
     *
     * The application uses `<span>` inside `<td>` for field labels; later
     * templates may use proper `<label>` elements.
     *
     * @param string $html      The rendered HTML to inspect.
     * @param string $labelText The expected label text (substring match).
     * @param string $message   Optional failure message.
     */
    function assertFormLabel($html, $labelText, $message = '')
    {
        $pattern = '/<(label|span)[^>]*>.*?'
                 . preg_quote($labelText, '/')
                 . '.*?<\/(label|span)>/si';
        $result  = (bool) preg_match($pattern, $html);
        $msg     = $message ?: "Expected a form label containing \"{$labelText}\".";
        $this->assertTrue($result, $msg);
    }

    /**
     * Assert that at least one field carries the `.error` validation class.
     *
     * The application sets `.error` on `input`, `select`, and `textarea`
     * elements when server-side validation fails.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $message Optional failure message.
     */
    function assertInputErrorState($html, $message = '')
    {
        $pattern = '/<(input|select|textarea)[^>]+class=["\'][^"\']*\berror\b[^"\']*["\']/i';
        $result  = (bool) preg_match($pattern, $html);
        $msg     = $message ?: 'Expected at least one field with .error class to indicate validation failure.';
        $this->assertTrue($result, $msg);
    }

    /**
     * Assert that no field carries the `.error` validation class.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $message Optional failure message.
     */
    function assertNoInputErrorState($html, $message = '')
    {
        $pattern = '/<(input|select|textarea)[^>]+class=["\'][^"\']*\berror\b[^"\']*["\']/i';
        $result  = (bool) preg_match($pattern, $html);
        $msg     = $message ?: 'Expected no field with .error class.';
        $this->assertFalse($result, $msg);
    }

    // ------------------------------------------------------------------
    // List / overview table assertions
    // ------------------------------------------------------------------

    /**
     * Assert that a `table.list` element is present.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $message Optional failure message.
     */
    function assertListTable($html, $message = '')
    {
        $pattern = '/<table[^>]+class=["\'][^"\']*\blist\b[^"\']*["\']/i';
        $result  = (bool) preg_match($pattern, $html);
        $msg     = $message ?: 'Expected a table.list element to be present.';
        $this->assertTrue($result, $msg);
    }

    /**
     * Assert that the list table contains a `<th>` with the given text.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $heading Expected text in the column header.
     * @param string $message Optional failure message.
     */
    function assertListTableHeader($html, $heading, $message = '')
    {
        $pattern = '/<th[^>]*>.*?'
                 . preg_quote($heading, '/')
                 . '.*?<\/th>/si';
        $result  = (bool) preg_match($pattern, $html);
        $msg     = $message ?: "Expected a table header column containing \"{$heading}\".";
        $this->assertTrue($result, $msg);
    }

    /**
     * Assert that the list table contains at least `$minRows` data rows
     * (rows carrying the `even` or `odd` CSS class).
     *
     * @param string $html    The rendered HTML to inspect.
     * @param int    $minRows Minimum expected number of data rows.
     * @param string $message Optional failure message.
     */
    function assertListTableRowCount($html, $minRows, $message = '')
    {
        preg_match_all(
            '/<tr\s[^>]*class=["\'][^"\']*\b(even|odd)\b[^"\']*["\']/i',
            $html,
            $matches
        );
        $count = count($matches[0]);
        $msg   = $message
               ?: "Expected at least {$minRows} data row(s) in table.list, found {$count}.";
        $this->assertTrue($count >= $minRows, $msg);
    }

    /**
     * Assert that a link with `$href` exists inside a `table.list`.
     *
     * Useful for checking that edit/delete action links are rendered for
     * every data row.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $href    URL (or substring) expected in the `href` attribute.
     * @param string $message Optional failure message.
     */
    function assertListTableActionLink($html, $href, $message = '')
    {
        $pattern = '/<table[^>]+class=["\'][^"\']*\blist\b[^"\']*["\'][^>]*>'
                 . '.*?<a\s[^>]*href=["\'][^"\']*'
                 . preg_quote($href, '/')
                 . '[^"\']*["\']/si';
        $result  = (bool) preg_match($pattern, $html);
        $msg     = $message
               ?: "Expected an action link with href containing \"{$href}\" inside table.list.";
        $this->assertTrue($result, $msg);
    }

    /**
     * Assert that an "add" link (`option=add`) is present in the overview.
     *
     * Module overview screens expose an add link when the current user has
     * CRUD_CREATE rights.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $message Optional failure message.
     */
    function assertOverviewAddLink($html, $message = '')
    {
        $pattern = '/<a\s[^>]*href=["\'][^"\']*option=add[^"\']*["\']/i';
        $result  = (bool) preg_match($pattern, $html);
        $msg     = $message ?: 'Expected an "add" link (option=add) to be present in the overview.';
        $this->assertTrue($result, $msg);
    }

    /**
     * Assert that the overview contains no data rows — i.e. the list is empty.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $message Optional failure message.
     */
    function assertEmptyOverview($html, $message = '')
    {
        $hasRows = (bool) preg_match(
            '/<tr\s[^>]*class=["\'][^"\']*\b(even|odd)\b[^"\']*["\']/i',
            $html
        );
        $msg = $message ?: 'Expected an empty overview (no even/odd data rows in table.list).';
        $this->assertFalse($hasRows, $msg);
    }
}
?>
