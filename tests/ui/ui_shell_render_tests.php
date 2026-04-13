<?php
/**
 * Shell render regression tests for poule_v2 (T008).
 *
 * Tests the server-rendered HTML output of templates/orange/index.tpl.php
 * to lock down placeholder usage, hero context and sidebar output before
 * the shell refactor (T009).
 *
 * These tests operate purely on HTML strings — no HTTP server is required.
 * The orange shell template is loaded from disk and rendered with controlled
 * placeholder values, then asserted using the helpers from UiShellAssertions.
 *
 * Test classes
 *   TestOfShellPlaceholders  — every placeholder token is replaced correctly
 *   TestOfShellStructure     — all structural shell elements are present
 *   TestOfHeroContext        — header/hero renders title, subtitle and logo
 *   TestOfShellNavigation    — nav menu links and active-state marking
 *   TestOfSidebarOutput      — sidebar column, submenu and information links
 *
 * @package   poule_v2
 * @author    Jaap van Boxtel
 * @copyright 2024
 */

require_once('./simpletest/autorun.php');
require_once('./ui/ui_shell_assertions.php');

// ---------------------------------------------------------------------------
// Template rendering helpers
// ---------------------------------------------------------------------------

/**
 * Return the default placeholder values used by renderShell().
 *
 * Centralising the defaults here means that both renderShell() and
 * TestOfShellPlaceholders share a single source of truth for the full
 * set of recognised placeholder tokens.
 *
 * @return array<string,string>  Map of placeholder token → default value.
 */
function shellDefaults()
{
    return array(
        '{TITLE}'         => 'Test Titel',
        '{TEMPLATE_NAME}' => 'orange',
        '{HEADERS}'       => '',
        '{MENU}'          => '<ul><li><a href="/?com=1">Competities</a></li></ul>',
        '{LOGIN}'         => '<a href="/?com=2&amp;option=login">Inloggen</a>',
        '{SUB_TITLE}'     => '',
        '{LOGO}'          => '',
        '{INFORMATION}'   => '',
        '{CONTENT}'       => '<p>Hoofdinhoud</p>',
    );
}

/**
 * Render the orange shell template with the supplied placeholder replacements.
 *
 * Loads templates/orange/index.tpl.php, strips the PHP die() guard that
 * prevents direct file access, and substitutes every placeholder token.
 * Any token not supplied in $replacements receives a sensible default so
 * tests only need to provide the values they care about.
 *
 * @param  array $replacements  Map of placeholder token → replacement string,
 *                              e.g. ['{TITLE}' => 'Mijn Competitie'].
 * @return string               The fully rendered HTML string.
 */
function renderShell($replacements = array())
{
    $values  = array_merge(shellDefaults(), $replacements);
    $tplPath = dirname(__FILE__) . '/../../templates/orange/index.tpl.php';
    $tpl     = file_get_contents($tplPath);

    // Strip the PHP die() guard that prevents direct file access.
    $tpl = preg_replace('/^<\?php die\(\); \?>\s*/s', '', $tpl);

    return str_replace(array_keys($values), array_values($values), $tpl);
}

// ===========================================================================
// 1. Placeholder substitution
// ===========================================================================

/**
 * Verify that every placeholder token is removed from the rendered output.
 *
 * An unreplaced token (e.g. the literal string '{CONTENT}') in the final
 * HTML indicates a missing substitution and would be visible to end-users.
 */
class TestOfShellPlaceholders extends UiShellAssertions
{
    function testNoUnreplacedTokensRemainInRenderedOutput()
    {
        $html   = renderShell();
        $tokens = array_keys(shellDefaults());

        foreach ($tokens as $token) {
            $this->assertHtmlNotContains(
                $html,
                $token,
                "Placeholder {$token} was not replaced in the rendered shell."
            );
        }
    }

    function testTitleTokenAppearsInBothTitleTagAndH1()
    {
        $html = renderShell(array('{TITLE}' => 'Mijn Competitie'));

        $this->assertContainsTag($html, 'title', 'Mijn Competitie');
        $this->assertContainsTag($html, 'h1', 'Mijn Competitie');
    }

    function testTemplateNameTokenAppearsInStylesheetHref()
    {
        $html = renderShell(array('{TEMPLATE_NAME}' => 'orange'));

        $this->assertHtmlContains(
            $html,
            'href="templates/orange/template.css"',
            'Expected the stylesheet href to include the template name.'
        );
    }

    function testMenuTokenContentAppearsInsideMenuElement()
    {
        $menuHtml = '<ul><li><a href="/?com=5">Spellen</a></li></ul>';
        $html     = renderShell(array('{MENU}' => $menuHtml));

        $this->assertHtmlContains($html, 'id="menu"');
        $this->assertHtmlContains(
            $html,
            'Spellen',
            'Expected menu item label to appear inside #menu.'
        );
    }

    function testLoginTokenContentAppearsInsideLoginElement()
    {
        $loginHtml = '<a href="/?com=2&amp;option=login">Inloggen</a>';
        $html      = renderShell(array('{LOGIN}' => $loginHtml));

        $this->assertHtmlContains($html, 'id="login"');
        $this->assertHtmlContains(
            $html,
            'Inloggen',
            'Expected login link label to appear inside #login.'
        );
    }

    function testContentTokenAppearsInsideColumn2()
    {
        $contentHtml = '<p>Welkom bij poule_v2</p>';
        $html        = renderShell(array('{CONTENT}' => $contentHtml));

        $this->assertHtmlContains($html, 'id="column2"');
        $this->assertHtmlContains(
            $html,
            'Welkom bij poule_v2',
            'Expected content to appear inside #column2.'
        );
    }

    function testInformationTokenAppearsInsideSubmenu()
    {
        $infoHtml = '<ul><li><a href="/?competition=1&amp;com=3">Stand</a></li></ul>';
        $html     = renderShell(array('{INFORMATION}' => $infoHtml));

        $this->assertHtmlContains($html, 'id="submenu"');
        $this->assertHtmlContains(
            $html,
            'Stand',
            'Expected information content to appear inside #submenu.'
        );
    }
}

// ===========================================================================
// 2. Shell container and layout structure
// ===========================================================================

/**
 * Verify that every structural shell element is rendered for a minimal
 * but complete set of placeholder values.
 */
class TestOfShellStructure extends UiShellAssertions
{
    var $html = '';

    function setUp()
    {
        $this->html = renderShell();
    }

    function testOutermostContainerIsPresent()
    {
        $this->assertShellContainer($this->html);
    }

    function testNavigationBandIsPresent()
    {
        $this->assertMenuWrapper($this->html);
    }

    function testPrimaryNavMenuIsPresent()
    {
        $this->assertNavMenu($this->html);
    }

    function testLoginNavAreaIsPresent()
    {
        $this->assertLoginNav($this->html);
    }

    function testPageHeaderIsPresent()
    {
        $this->assertPageHeader($this->html);
    }

    function testH1IsPresent()
    {
        $this->assertH1Present($this->html);
    }

    function testPrimaryContentColumnIsPresent()
    {
        $this->assertContentColumn($this->html);
    }

    function testSidebarColumnIsPresent()
    {
        $this->assertSidebarColumn($this->html);
    }

    function testContextualSidebarSubmenuIsPresent()
    {
        $this->assertSidebar($this->html);
    }

    function testCopyrightFooterIsPresent()
    {
        $this->assertCopyrightFooter($this->html);
    }
}

// ===========================================================================
// 3. Hero context — header renders title, subtitle and logo
// ===========================================================================

/**
 * Verify the header/hero band renders the title, optional subtitle and
 * optional competition logo correctly for the different screen contexts.
 */
class TestOfHeroContext extends UiShellAssertions
{
    function testHeroShowsApplicationTitleInH1()
    {
        $html = renderShell(array('{TITLE}' => 'Poule Applicatie'));

        $this->assertContainsTag($html, 'h1', 'Poule Applicatie');
    }

    function testHeroShowsCompetitionNameInSubtitleParagraph()
    {
        $html = renderShell(array(
            '{TITLE}'     => 'Poule Applicatie',
            '{SUB_TITLE}' => 'Champions League 2024',
        ));

        $this->assertContainsTag($html, 'p', 'Champions League 2024');
    }

    function testHeroSubtitleParagraphIsPresentWhenSubtitleIsEmpty()
    {
        // The <p> tag must exist in the template even when {SUB_TITLE} is empty
        // so that the hero layout does not collapse.
        $html = renderShell(array('{SUB_TITLE}' => ''));

        $this->assertHtmlContains($html, '<p>');
    }

    function testHeroShowsLogoImageWhenLogoIsProvided()
    {
        $logoHtml = '<img class="logo" src="upload/logo.png" alt="Competition Logo" />';
        $html     = renderShell(array('{LOGO}' => $logoHtml));

        $this->assertLogoPresent($html);
    }

    function testHeroHasNoLogoClassWhenLogoIsNotProvided()
    {
        $html = renderShell(array('{LOGO}' => ''));

        $this->assertHtmlNotContains($html, 'class="logo"');
    }

    function testTitleAndSubtitleBothAppearInsideHeaderDiv()
    {
        $title    = 'App Titel';
        $subtitle = 'Competitie Context';
        $html = renderShell(array(
            '{TITLE}'     => $title,
            '{SUB_TITLE}' => $subtitle,
        ));

        // Both text values must appear nested within #header.
        $pattern = '/<div[^>]+id=["\']header["\'][^>]*>'
                 . '.*?' . preg_quote($title, '/')
                 . '.*?' . preg_quote($subtitle, '/')
                 . '.*?<\/div>/si';

        $this->assertTrue(
            (bool) preg_match($pattern, $html),
            'Expected both the title and subtitle to appear inside #header.'
        );
    }
}

// ===========================================================================
// 4. Navigation rendering
// ===========================================================================

/**
 * Verify that navigation link and active-state markup rendered by the
 * menu class is correctly positioned within the shell.
 */
class TestOfShellNavigation extends UiShellAssertions
{
    function testNavMenuContainsProvidedLinks()
    {
        $menuHtml = '<ul>'
                  . '<li><a href="/?com=1">Competities</a></li>'
                  . '<li><a href="/?com=4">Gebruikers</a></li>'
                  . '</ul>';
        $html = renderShell(array('{MENU}' => $menuHtml));

        $this->assertNavLinkPresent($html, '?com=1');
        $this->assertNavLinkPresent($html, '?com=4');
    }

    function testNavMenuWithCurrentPageItemClassIsRecognisedAsActive()
    {
        $menuHtml = '<ul>'
                  . '<li class="current_page_item"><a href="/?com=1">Competities</a></li>'
                  . '<li><a href="/?com=4">Gebruikers</a></li>'
                  . '</ul>';
        $html = renderShell(array('{MENU}' => $menuHtml));

        $this->assertActiveNavItem($html);
    }

    function testNavMenuWithoutCurrentPageItemClassHasNoActiveItem()
    {
        $menuHtml = '<ul>'
                  . '<li><a href="/?com=1">Competities</a></li>'
                  . '<li><a href="/?com=4">Gebruikers</a></li>'
                  . '</ul>';
        $html = renderShell(array('{MENU}' => $menuHtml));

        $this->assertNoActiveNavItem($html);
    }

    function testLoginNavAreaContainsLoginLink()
    {
        $loginHtml = '<a href="/?com=2&amp;option=login">Inloggen</a>';
        $html      = renderShell(array('{LOGIN}' => $loginHtml));

        $this->assertNavLinkPresent($html, 'option=login');
    }

    function testLoginNavAreaContainsLogoutLinkWhenUserIsLoggedIn()
    {
        $logoutHtml = '<a href="/?com=2&amp;option=logout">Uitloggen</a>';
        $html       = renderShell(array('{LOGIN}' => $logoutHtml));

        $this->assertNavLinkPresent($html, 'option=logout');
    }
}

// ===========================================================================
// 5. Sidebar output
// ===========================================================================

/**
 * Verify that the sidebar column and contextual submenu are rendered
 * correctly with and without competition-scoped navigation links.
 */
class TestOfSidebarOutput extends UiShellAssertions
{
    function testSidebarColumnAndSubmenuAreAlwaysPresent()
    {
        // Even when {INFORMATION} is empty the sidebar structure must exist
        // so the two-column layout does not collapse.
        $html = renderShell(array('{INFORMATION}' => ''));

        $this->assertSidebarColumn($html);
        $this->assertSidebar($html);
    }

    function testSidebarDisplaysCompetitionScopedLinks()
    {
        $infoHtml = '<ul>'
                  . '<li><a href="/?competition=1&amp;com=3">Stand</a></li>'
                  . '<li><a href="/?competition=1&amp;com=7">Voorspellingen</a></li>'
                  . '</ul>';
        $html = renderShell(array('{INFORMATION}' => $infoHtml));

        $this->assertSidebarLink($html, 'competition=1');
        $this->assertSidebarLink($html, 'com=3');
        $this->assertSidebarLink($html, 'com=7');
    }

    function testSubmenuIsNestedInsideSidebarColumn()
    {
        $infoHtml = '<ul><li><a href="/?competition=1&amp;com=5">Standen</a></li></ul>';
        $html     = renderShell(array('{INFORMATION}' => $infoHtml));

        // #submenu must appear as a descendant of #column1.
        $pattern = '/<div[^>]+id=["\']column1["\'][^>]*>.*?id=["\']submenu["\']/si';

        $this->assertTrue(
            (bool) preg_match($pattern, $html),
            'Expected #submenu to be nested inside #column1.'
        );
    }

    function testSidebarLinkLabelsAreRenderedCorrectly()
    {
        $infoHtml = '<ul>'
                  . '<li><a href="/?competition=2&amp;com=3">Stand</a></li>'
                  . '</ul>';
        $html = renderShell(array('{INFORMATION}' => $infoHtml));

        $this->assertHtmlContains(
            $html,
            'Stand',
            'Expected sidebar link label to appear in rendered output.'
        );
    }
}
?>
