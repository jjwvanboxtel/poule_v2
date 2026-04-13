<?php
/**
 * Shared shell assertions for UI regression testing in poule_v2.
 *
 * Extends {@see UiTestCase} with helpers for asserting the presence and
 * correctness of the application shell structure:
 *   - outer container and layout columns
 *   - navigation bar (primary menu and login/logout area)
 *   - page header / hero band
 *   - contextual sidebar and submenu
 *   - title panels and page-heading elements
 *
 * Usage:
 *   require_once('./ui/ui_shell_assertions.php');
 *
 *   class TestOfMyShellRendering extends UiShellAssertions
 *   {
 *       function testNavMenuIsPresent()
 *       {
 *           $html = '<div id="container"><div id="menu-wrapper">'
 *                 . '<div id="menu"><ul><li><a href="?com=1">Competitions</a></li></ul></div>'
 *                 . '</div></div>';
 *           $this->assertShellContainer($html);
 *           $this->assertNavMenu($html);
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
 * Assertion helpers for the application shell structure.
 *
 * All assertions operate on a raw HTML string – no HTTP request is made –
 * which allows fast, focused unit-style tests for server-rendered output.
 */
class UiShellAssertions extends UiTestCase
{
    // ------------------------------------------------------------------
    // Shell container and layout
    // ------------------------------------------------------------------

    /**
     * Assert that the outermost `#container` wrapper is present.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $message Optional failure message.
     */
    function assertShellContainer($html, $message = '')
    {
        $msg = $message ?: 'Expected shell container (#container) to be present.';
        $this->assertHtmlContains($html, 'id="container"', $msg);
    }

    /**
     * Assert that the `#menu-wrapper` navigation band is present.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $message Optional failure message.
     */
    function assertMenuWrapper($html, $message = '')
    {
        $msg = $message ?: 'Expected navigation band (#menu-wrapper) to be present.';
        $this->assertHtmlContains($html, 'id="menu-wrapper"', $msg);
    }

    /**
     * Assert that the primary content column `#column2` is present.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $message Optional failure message.
     */
    function assertContentColumn($html, $message = '')
    {
        $msg = $message ?: 'Expected primary content column (#column2) to be present.';
        $this->assertHtmlContains($html, 'id="column2"', $msg);
    }

    /**
     * Assert that the sidebar column `#column1` is present.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $message Optional failure message.
     */
    function assertSidebarColumn($html, $message = '')
    {
        $msg = $message ?: 'Expected sidebar column (#column1) to be present.';
        $this->assertHtmlContains($html, 'id="column1"', $msg);
    }

    /**
     * Assert that the copyright footer bar is present.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $message Optional failure message.
     */
    function assertCopyrightFooter($html, $message = '')
    {
        $msg = $message ?: 'Expected copyright footer (#copyright) to be present.';
        $this->assertHtmlContains($html, 'id="copyright"', $msg);
    }

    // ------------------------------------------------------------------
    // Navigation assertions
    // ------------------------------------------------------------------

    /**
     * Assert that the primary navigation element `#menu` is present.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $message Optional failure message.
     */
    function assertNavMenu($html, $message = '')
    {
        $msg = $message ?: 'Expected primary navigation (#menu) to be present.';
        $this->assertHtmlContains($html, 'id="menu"', $msg);
    }

    /**
     * Assert that the login/logout navigation area `#login` is present.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $message Optional failure message.
     */
    function assertLoginNav($html, $message = '')
    {
        $msg = $message ?: 'Expected login/logout nav area (#login) to be present.';
        $this->assertHtmlContains($html, 'id="login"', $msg);
    }

    /**
     * Assert that at least one navigation link pointing to `$href` exists in
     * either the primary menu or the login nav area.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $href    URL (or substring) expected in the `href` attribute.
     * @param string $message Optional failure message.
     */
    function assertNavLinkPresent($html, $href, $message = '')
    {
        $msg = $message ?: "Expected a navigation link with href containing \"{$href}\".";
        $this->assertContainsLink($html, $href, $msg);
    }

    /**
     * Assert that at least one navigation item carries the active state
     * class (`current_page_item`).
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $message Optional failure message.
     */
    function assertActiveNavItem($html, $message = '')
    {
        $pattern = '/class=["\'][^"\']*\bcurrent_page_item\b[^"\']*["\']/i';
        $result  = (bool) preg_match($pattern, $html);
        $msg     = $message ?: 'Expected an active navigation item (.current_page_item) to be marked.';
        $this->assertTrue($result, $msg);
    }

    /**
     * Assert that no navigation item is marked as active.
     * Useful for screens where no primary navigation item should be highlighted.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $message Optional failure message.
     */
    function assertNoActiveNavItem($html, $message = '')
    {
        $pattern = '/class=["\'][^"\']*\bcurrent_page_item\b[^"\']*["\']/i';
        $result  = (bool) preg_match($pattern, $html);
        $msg     = $message ?: 'Expected no active navigation item (.current_page_item).';
        $this->assertFalse($result, $msg);
    }

    // ------------------------------------------------------------------
    // Header / hero assertions
    // ------------------------------------------------------------------

    /**
     * Assert that the page header / hero band `#header` is present.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $message Optional failure message.
     */
    function assertPageHeader($html, $message = '')
    {
        $msg = $message ?: 'Expected page header (#header) to be present.';
        $this->assertHtmlContains($html, 'id="header"', $msg);
    }

    /**
     * Assert that the `<h1>` application title inside the header is present.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $message Optional failure message.
     */
    function assertH1Present($html, $message = '')
    {
        $msg = $message ?: 'Expected an <h1> element to be present.';
        $this->assertHtmlContains($html, '<h1', $msg);
    }

    /**
     * Assert that the competition logo image inside the header is present.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $message Optional failure message.
     */
    function assertLogoPresent($html, $message = '')
    {
        $pattern = '/<img[^>]+class=["\'][^"\']*\blogo\b[^"\']*["\']/i';
        $result  = (bool) preg_match($pattern, $html);
        $msg     = $message ?: 'Expected a logo image (.logo) inside the header.';
        $this->assertTrue($result, $msg);
    }

    // ------------------------------------------------------------------
    // Sidebar / submenu assertions
    // ------------------------------------------------------------------

    /**
     * Assert that the contextual submenu `#submenu` is present.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $message Optional failure message.
     */
    function assertSidebar($html, $message = '')
    {
        $msg = $message ?: 'Expected contextual sidebar (#submenu) to be present.';
        $this->assertHtmlContains($html, 'id="submenu"', $msg);
    }

    /**
     * Assert that a link with `$href` appears inside the contextual submenu.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $href    URL (or substring) expected in the `href` attribute.
     * @param string $message Optional failure message.
     */
    function assertSidebarLink($html, $href, $message = '')
    {
        $pattern = '/<[^>]*id=["\']submenu["\'][^>]*>.*?<a\s[^>]*href=["\'][^"\']*'
                 . preg_quote($href, '/')
                 . '[^"\']*["\']/si';
        $result  = (bool) preg_match($pattern, $html);
        $msg     = $message ?: "Expected a submenu link with href containing \"{$href}\".";
        $this->assertTrue($result, $msg);
    }

    // ------------------------------------------------------------------
    // Title panel assertions
    // ------------------------------------------------------------------

    /**
     * Assert that a legacy `.title` panel is present.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $message Optional failure message.
     */
    function assertTitlePanel($html, $message = '')
    {
        $msg = $message ?: 'Expected a .title panel to be present.';
        $this->assertContainsClass($html, 'title', $msg);
    }

    /**
     * Assert that a `.title` panel contains an `<h2>` with the given text.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $text    Expected heading text (exact substring match).
     * @param string $message Optional failure message.
     */
    function assertTitlePanelContains($html, $text, $message = '')
    {
        $pattern = '/<div[^>]+class=["\'][^"\']*\btitle\b[^"\']*["\'][^>]*>'
                 . '.*?<h2[^>]*>.*?'
                 . preg_quote($text, '/')
                 . '.*?<\/h2>/si';
        $result  = (bool) preg_match($pattern, $html);
        $msg     = $message ?: "Expected .title panel to contain heading \"{$text}\".";
        $this->assertTrue($result, $msg);
    }

    /**
     * Assert that the Bootstrap-aligned `.page-title` element is present.
     * This class is defined in the T006 CSS utility layer.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $message Optional failure message.
     */
    function assertPageTitleClass($html, $message = '')
    {
        $msg = $message ?: 'Expected a .page-title element to be present.';
        $this->assertContainsClass($html, 'page-title', $msg);
    }

    /**
     * Assert that the Bootstrap-aligned `.card-title` element is present.
     *
     * @param string $html    The rendered HTML to inspect.
     * @param string $message Optional failure message.
     */
    function assertCardTitleClass($html, $message = '')
    {
        $msg = $message ?: 'Expected a .card-title element to be present.';
        $this->assertContainsClass($html, 'card-title', $msg);
    }
}
?>
