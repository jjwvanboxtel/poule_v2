<?php
/**
 * Overview render regression tests for poule_v2 (T019).
 *
 * Tests the server-rendered HTML output of representative overview and list
 * templates to lock down table structure, column header meaning, summary areas,
 * and action regions before the overview restyling (T020–T023).
 *
 * These tests operate purely on HTML strings — no HTTP server is required.
 * Templates are loaded from disk, stripped of their PHP die() guard, and
 * rendered with controlled placeholder values, then asserted using the helpers
 * from UiMarkupAssertions.
 *
 * Covered templates
 *   – modules/table/table.tpl.php               (standings overview)
 *   – modules/subleagues/subleague_table.tpl.php (subleague standings)
 *   – modules/competitions/competition.tpl.php   (competition list)
 *   – modules/games/game.tpl.php                 (games list)
 *   – modules/predictions/prediction.tpl.php     (prediction form overview)
 *   – modules/predictions/user.tpl.php           (prediction user list)
 *   – modules/participants/participant.tpl.php    (participant list)
 *   – modules/users/user.tpl.php                 (user management list)
 *
 * Test classes
 *   TestOfStandingsTableStructure      — standings table columns, title, form wrapper
 *   TestOfSubleagueTableStructure      — subleague standings columns and title
 *   TestOfCompetitionListStructure     — competition list columns and add link
 *   TestOfGamesListStructure           — games list columns and template token
 *   TestOfPredictionOverviewStructure  — prediction form area and message wrapper
 *   TestOfUserPredictionListStructure  — user prediction list and status message
 *   TestOfParticipantListStructure     — participant list, card wrapper and add link
 *   TestOfUserManagementListStructure  — user list columns and usergroup filter
 *   TestOfOverviewTokenReplacement     — no unreplaced placeholder tokens in output
 *   TestOfOverviewActionRegions        — add links and action-row rendering
 *
 * @package   poule_v2
 * @author    Jaap van Boxtel
 * @copyright 2024
 */

require_once('./simpletest/autorun.php');
require_once('./ui/ui_markup_assertions.php');

// ---------------------------------------------------------------------------
// Standings table rendering helpers (table.tpl.php)
// ---------------------------------------------------------------------------

/**
 * Return the default placeholder values used by renderStandingsTable().
 *
 * @return array<string,string>  Map of placeholder token → default value.
 */
function standingsTableDefaults()
{
    return array(
        '{COM_NAME}'       => 'Stand',
        '{TABLE_MSG}'      => '',
        '{LANG_POSITION}'  => 'Positie',
        '{LANG_PARTICIPANT}' => 'Deelnemer',
        '{LANG_POINTS}'    => 'Punten',
        '{CONTENT}'        => '<tr class="odd"><td>1</td><td>&nbsp;</td>'
                            . '<td>Jan de Vries</td><td>42</td></tr>',
        '{TABLE_BUTTONS}'  => '',
    );
}

/**
 * Render the standings table template with the supplied placeholder replacements.
 *
 * Loads modules/table/table.tpl.php, strips the PHP die() guard, and
 * substitutes every placeholder token.
 *
 * @param  array $replacements  Map of placeholder token → replacement string.
 * @return string               The fully rendered HTML string.
 */
function renderStandingsTable($replacements = array())
{
    $values  = array_merge(standingsTableDefaults(), $replacements);
    $tplPath = dirname(__FILE__) . '/../../modules/table/table.tpl.php';
    $tpl     = file_get_contents($tplPath);

    $tpl = preg_replace('/^<\?php die\(\); \?>\s*/s', '', $tpl);

    return str_replace(array_keys($values), array_values($values), $tpl);
}

// ---------------------------------------------------------------------------
// Subleague standings table rendering helpers (subleague_table.tpl.php)
// ---------------------------------------------------------------------------

/**
 * Return the default placeholder values used by renderSubleagueTable().
 *
 * @return array<string,string>  Map of placeholder token → default value.
 */
function subleagueTableDefaults()
{
    return array(
        '{COM_NAME}'         => 'Subcompetitie Stand',
        '{LANG_POSITION}'    => 'Positie',
        '{LANG_PARTICIPANT}' => 'Deelnemer',
        '{LANG_POINTS}'      => 'Punten',
        '{CONTENT}'          => '<tr class="odd"><td>1</td><td>Eerste</td>'
                              . '<td>Jan de Vries</td><td>38</td></tr>',
    );
}

/**
 * Render the subleague standings template with the supplied replacements.
 *
 * @param  array $replacements  Map of placeholder token → replacement string.
 * @return string               The fully rendered HTML string.
 */
function renderSubleagueTable($replacements = array())
{
    $values  = array_merge(subleagueTableDefaults(), $replacements);
    $tplPath = dirname(__FILE__)
             . '/../../modules/subleagues/subleague_table.tpl.php';
    $tpl     = file_get_contents($tplPath);

    $tpl = preg_replace('/^<\?php die\(\); \?>\s*/s', '', $tpl);

    return str_replace(array_keys($values), array_values($values), $tpl);
}

// ---------------------------------------------------------------------------
// Competition list rendering helpers (competition.tpl.php)
// ---------------------------------------------------------------------------

/**
 * Return the default placeholder values used by renderCompetitionList().
 *
 * @return array<string,string>  Map of placeholder token → default value.
 */
function competitionListDefaults()
{
    return array(
        '{COM_NAME}'         => 'Competities',
        '{COMPETITION_MSG}'  => '',
        '{COMPETITION_ADD}'  => '<a href="/?com=1&amp;option=add">Toevoegen</a>',
        '{LANG_ID}'          => 'ID',
        '{LANG_COMPETITION}' => 'Competitie',
        '{LANG_ACTIONS}'     => 'Acties',
        '{CONTENT}'          => '<tr class="odd"><td>1</td>'
                              . '<td>WK 2026</td>'
                              . '<td><a href="/?com=1&amp;option=edit&amp;id=1">Wijzigen</a></td>'
                              . '</tr>',
    );
}

/**
 * Render the competition list template with the supplied replacements.
 *
 * @param  array $replacements  Map of placeholder token → replacement string.
 * @return string               The fully rendered HTML string.
 */
function renderCompetitionList($replacements = array())
{
    $values  = array_merge(competitionListDefaults(), $replacements);
    $tplPath = dirname(__FILE__)
             . '/../../modules/competitions/competition.tpl.php';
    $tpl     = file_get_contents($tplPath);

    $tpl = preg_replace('/^<\?php die\(\); \?>\s*/s', '', $tpl);

    return str_replace(array_keys($values), array_values($values), $tpl);
}

// ---------------------------------------------------------------------------
// Games list rendering helpers (game.tpl.php)
// ---------------------------------------------------------------------------

/**
 * Return the default placeholder values used by renderGamesList().
 *
 * @return array<string,string>  Map of placeholder token → default value.
 */
function gamesListDefaults()
{
    // {CONTENT} must come first so that PHP's sequential str_replace expands
    // it before processing the {LANG_*} / {TEMPLATE_NAME} tokens that now live
    // inside the class-generated table structure rather than in the template.
    return array(
        '{CONTENT}'           => '<div class="d-none d-md-block">'
                              . '<div class="table-responsive">'
                              . '<table class="list" cellpadding="0" cellspacing="0">'
                              . '<tr>'
                              . '<th>{LANG_DATE}</th>'
                              . '<th>{LANG_CITY}</th>'
                              . '<th>{LANG_POULE}</th>'
                              . '<th colspan="2">{LANG_COUNTRY}</th>'
                              . '<th>{LANG_RESULT}</th>'
                              . '<th colspan="2">{LANG_COUNTRY}</th>'
                              . '<th>{LANG_YELLOW_CARDS}</th>'
                              . '<th>{LANG_RED_CARDS}</th>'
                              . '<th style="width: 75px;">{LANG_ACTIONS}</th>'
                              . '</tr>'
                              . '<tr class="odd"><td>01-06-2026</td>'
                              . '<td>Amsterdam</td><td>A</td>'
                              . '<td>Nederland</td><td>1 - 0</td>'
                              . '<td>Duitsland</td><td>0</td><td>0</td>'
                              . '<td><a href="/?com=5&amp;option=edit&amp;id=1">'
                              . '<img src="templates/{TEMPLATE_NAME}/icons/page_edit.png" alt="" class="actions" /></a></td>'
                              . '</tr>'
                              . '<tr><td colspan="11">{LANG_COUNT}: 1</td></tr>'
                              . '</table></div></div>',
        '{COM_NAME}'          => 'Wedstrijden',
        '{GAME_MSG}'          => '',
        '{GAME_ADD}'          => '<a href="/?com=5&amp;option=add">Toevoegen</a>',
        '{LANG_DATE}'         => 'Datum',
        '{LANG_CITY}'         => 'Stad',
        '{LANG_POULE}'        => 'Poule',
        '{LANG_COUNTRY}'      => 'Land',
        '{LANG_RESULT}'       => 'Uitslag',
        '{LANG_YELLOW_CARDS}' => 'Gele kaarten',
        '{LANG_RED_CARDS}'    => 'Rode kaarten',
        '{LANG_ACTIONS}'      => 'Acties',
        '{LANG_COUNT}'        => 'Aantal',
        '{TEMPLATE_NAME}'     => 'orange',
    );
}

/**
 * Render the games list template with the supplied replacements.
 *
 * @param  array $replacements  Map of placeholder token → replacement string.
 * @return string               The fully rendered HTML string.
 */
function renderGamesList($replacements = array())
{
    $values  = array_merge(gamesListDefaults(), $replacements);
    $tplPath = dirname(__FILE__) . '/../../modules/games/game.tpl.php';
    $tpl     = file_get_contents($tplPath);

    $tpl = preg_replace('/^<\?php die\(\); \?>\s*/s', '', $tpl);

    return str_replace(array_keys($values), array_values($values), $tpl);
}

// ---------------------------------------------------------------------------
// Prediction overview rendering helpers (prediction.tpl.php)
// ---------------------------------------------------------------------------

/**
 * Return the default placeholder values used by renderPredictionOverview().
 *
 * @return array<string,string>  Map of placeholder token → default value.
 */
function predictionOverviewDefaults()
{
    return array(
        '{COM_NAME}'        => 'Voorspellingen',
        '{PREDICTION_MSG}'  => '',
        '{ERROR_MSG}'       => '',
        '{SUBMISSION_MSG}'  => '',
        '{PREDICTION_EDIT}' => '',
        '{USER_CONTENT}'    => '<p>Jan de Vries</p>',
        '{GAME_CONTENT}'    => '<table class="list"><tr>'
                            . '<th>Thuis</th><th>Uitslag</th><th>Uit</th>'
                            . '</tr></table>',
        '{ROUND_CONTENT}'    => '',
        '{QUESTION_CONTENT}' => '',
        '{PREDICTION_BUTTONS}' => '<input type="submit" name="submit" value="Opslaan" />',
    );
}

/**
 * Render the prediction overview template with the supplied replacements.
 *
 * @param  array $replacements  Map of placeholder token → replacement string.
 * @return string               The fully rendered HTML string.
 */
function renderPredictionOverview($replacements = array())
{
    $values  = array_merge(predictionOverviewDefaults(), $replacements);
    $tplPath = dirname(__FILE__)
             . '/../../modules/predictions/prediction.tpl.php';
    $tpl     = file_get_contents($tplPath);

    $tpl = preg_replace('/^<\?php die\(\); \?>\s*/s', '', $tpl);

    return str_replace(array_keys($values), array_values($values), $tpl);
}

// ---------------------------------------------------------------------------
// Prediction user list rendering helpers (user.tpl.php)
// ---------------------------------------------------------------------------

/**
 * Return the default placeholder values used by renderUserPredictionList().
 *
 * @return array<string,string>  Map of placeholder token → default value.
 */
function userPredictionListDefaults()
{
    return array(
        '{COM_NAME}'  => 'Deelnemers voorspellingen',
        '{USER_MSG}'  => '',
        '{CONTENT}'   => '<tr class="odd"><td>'
                       . '<a href="/?competition=1&amp;com=7&amp;id=3">Jan de Vries</a>'
                       . '</td></tr>',
    );
}

/**
 * Render the prediction user list template with the supplied replacements.
 *
 * @param  array $replacements  Map of placeholder token → replacement string.
 * @return string               The fully rendered HTML string.
 */
function renderUserPredictionList($replacements = array())
{
    $values  = array_merge(userPredictionListDefaults(), $replacements);
    $tplPath = dirname(__FILE__)
             . '/../../modules/predictions/user.tpl.php';
    $tpl     = file_get_contents($tplPath);

    $tpl = preg_replace('/^<\?php die\(\); \?>\s*/s', '', $tpl);

    return str_replace(array_keys($values), array_values($values), $tpl);
}

// ---------------------------------------------------------------------------
// Participant list rendering helpers (participant.tpl.php)
// ---------------------------------------------------------------------------

/**
 * Return the default placeholder values used by renderParticipantList().
 *
 * @return array<string,string>  Map of placeholder token → default value.
 */
function participantListDefaults()
{
    return array(
        '{COM_NAME}'         => 'Deelnemers',
        '{PARTICIPANT_MSG}'  => '',
        '{LANG_FILTER}'      => 'Filter',
        '{FILTER_LIST}'      => '<ul><li><a href="/?com=3">Alle</a></li></ul>',
        '{LANG_ID}'          => 'ID',
        '{LANG_PARTICIPANT}' => 'Deelnemer',
        '{LANG_ACTIONS}'     => 'Acties',
        '{CONTENT}'          => '<tr class="odd"><td>1</td>'
                              . '<td>Jan de Vries</td>'
                              . '<td><a href="/?com=3&amp;option=edit&amp;id=1">Wijzigen</a></td>'
                              . '</tr>',
    );
}

/**
 * Render the participant list template with the supplied replacements.
 *
 * @param  array $replacements  Map of placeholder token → replacement string.
 * @return string               The fully rendered HTML string.
 */
function renderParticipantList($replacements = array())
{
    $values  = array_merge(participantListDefaults(), $replacements);
    $tplPath = dirname(__FILE__)
             . '/../../modules/participants/participant.tpl.php';
    $tpl     = file_get_contents($tplPath);

    $tpl = preg_replace('/^<\?php die\(\); \?>\s*/s', '', $tpl);

    return str_replace(array_keys($values), array_values($values), $tpl);
}

// ---------------------------------------------------------------------------
// User management list rendering helpers (user.tpl.php)
// ---------------------------------------------------------------------------

/**
 * Return the default placeholder values used by renderUserList().
 *
 * @return array<string,string>  Map of placeholder token → default value.
 */
function userListDefaults()
{
    return array(
        '{COM_NAME}'           => 'Gebruikers',
        '{USER_MSG}'           => '',
        '{USER_ADD}'           => '<a href="/?com=4&amp;option=add">Toevoegen</a>',
        '{LANG_USERGROUP}'     => 'Gebruikersgroep',
        '{USERGROUP_LIST}'     => '<ul><li><a href="/?com=4">Alle</a></li></ul>',
        '{LANG_ID}'            => 'ID',
        '{LANG_USER_FULLNAME}' => 'Naam',
        '{LANG_ACTIONS}'       => 'Acties',
        '{CONTENT}'            => '<tr class="odd"><td></td><td>1</td>'
                                . '<td>Jan de Vries</td><td>Deelnemer</td>'
                                . '<td><a href="/?com=4&amp;option=edit&amp;id=1">Wijzigen</a></td>'
                                . '</tr>',
    );
}

/**
 * Render the user management list template with the supplied replacements.
 *
 * @param  array $replacements  Map of placeholder token → replacement string.
 * @return string               The fully rendered HTML string.
 */
function renderUserList($replacements = array())
{
    $values  = array_merge(userListDefaults(), $replacements);
    $tplPath = dirname(__FILE__) . '/../../modules/users/user.tpl.php';
    $tpl     = file_get_contents($tplPath);

    $tpl = preg_replace('/^<\?php die\(\); \?>\s*/s', '', $tpl);

    return str_replace(array_keys($values), array_values($values), $tpl);
}

// ===========================================================================
// 1. Standings table (table.tpl.php)
// ===========================================================================

/**
 * Verify that the standings table template renders the expected structure:
 * title panel, list table with column headers, and a form wrapper.
 *
 * These assertions lock down the standings template contract so that
 * T020 (overview restyling) cannot accidentally break column meaning.
 */
class TestOfStandingsTableStructure extends UiMarkupAssertions
{
    function testTitlePanelWithHeadingIsPresent()
    {
        $html = renderStandingsTable();

        $this->assertContainsClass($html, 'title');
        $this->assertContainsTag($html, 'h2', 'Stand');
    }

    function testListTableIsPresent()
    {
        $html = renderStandingsTable();

        $this->assertListTable($html);
    }

    function testPositionColumnHeaderIsRendered()
    {
        $html = renderStandingsTable(array('{LANG_POSITION}' => 'Positie'));

        $this->assertListTableHeader($html, 'Positie');
    }

    function testParticipantColumnHeaderIsRendered()
    {
        $html = renderStandingsTable(array('{LANG_PARTICIPANT}' => 'Deelnemer'));

        $this->assertListTableHeader($html, 'Deelnemer');
    }

    function testPointsColumnHeaderIsRendered()
    {
        $html = renderStandingsTable(array('{LANG_POINTS}' => 'Punten'));

        $this->assertListTableHeader($html, 'Punten');
    }

    function testDataRowsAreRenderedInsideTable()
    {
        $html = renderStandingsTable(array(
            '{CONTENT}' => '<tr class="odd"><td>1</td><td>&nbsp;</td>'
                         . '<td>Jan de Vries</td><td>42</td></tr>'
                         . '<tr class="even"><td>2</td><td>&nbsp;</td>'
                         . '<td>Piet Klaassen</td><td>38</td></tr>',
        ));

        $this->assertListTableRowCount($html, 2);
    }

    function testFormWrapperIsPresentForTableButtons()
    {
        $html = renderStandingsTable(array(
            '{TABLE_BUTTONS}' => '<input type="submit" name="submit" value="Bijwerken" />',
        ));

        $this->assertFormPresent($html);
        $this->assertFormSubmitButton($html, 'Bijwerken');
    }

    function testStatusMessageAreaIsRendered()
    {
        $html = renderStandingsTable(array(
            '{TABLE_MSG}' => '<div class="alert alert-success">Stand bijgewerkt</div>',
        ));

        $this->assertAlertPresent($html);
        $this->assertSuccessAlert($html);
    }
}

// ===========================================================================
// 2. Subleague standings table (subleague_table.tpl.php)
// ===========================================================================

/**
 * Verify that the subleague standings template renders the expected title,
 * column headers, and list table wrapped in a form element.
 */
class TestOfSubleagueTableStructure extends UiMarkupAssertions
{
    function testTitlePanelWithHeadingIsPresent()
    {
        $html = renderSubleagueTable();

        $this->assertContainsClass($html, 'title');
        $this->assertContainsTag($html, 'h2', 'Subcompetitie Stand');
    }

    function testListTableIsPresent()
    {
        $html = renderSubleagueTable();

        $this->assertListTable($html);
    }

    function testPositionColumnHeaderIsRendered()
    {
        $html = renderSubleagueTable(array('{LANG_POSITION}' => 'Positie'));

        $this->assertListTableHeader($html, 'Positie');
    }

    function testParticipantColumnHeaderIsRendered()
    {
        $html = renderSubleagueTable(array('{LANG_PARTICIPANT}' => 'Deelnemer'));

        $this->assertListTableHeader($html, 'Deelnemer');
    }

    function testPointsColumnHeaderIsRendered()
    {
        $html = renderSubleagueTable(array('{LANG_POINTS}' => 'Punten'));

        $this->assertListTableHeader($html, 'Punten');
    }

    function testFormWrapperWrapsTheTable()
    {
        $html = renderSubleagueTable();

        // The subleague_table.tpl.php wraps the list table in a <form>.
        $pattern = '/<form[^>]*>.*?<table[^>]+class=["\'][^"\']*\blist\b[^"\']*["\']/si';
        $this->assertTrue(
            (bool) preg_match($pattern, $html),
            'Expected table.list to be wrapped inside a <form> element.'
        );
    }

    function testDataRowsAreRenderedInsideTable()
    {
        $html = renderSubleagueTable(array(
            '{CONTENT}' => '<tr class="odd"><td>1</td><td>Eerste</td>'
                         . '<td>Jan de Vries</td><td>38</td></tr>',
        ));

        $this->assertListTableRowCount($html, 1);
    }
}

// ===========================================================================
// 3. Competition list (competition.tpl.php)
// ===========================================================================

/**
 * Verify that the competition list template renders the title panel, column
 * headers, add link, and data rows correctly.
 */
class TestOfCompetitionListStructure extends UiMarkupAssertions
{
    function testTitlePanelWithHeadingIsPresent()
    {
        $html = renderCompetitionList();

        $this->assertContainsClass($html, 'title');
        $this->assertContainsTag($html, 'h2', 'Competities');
    }

    function testListTableIsPresent()
    {
        $html = renderCompetitionList();

        $this->assertListTable($html);
    }

    function testIdColumnHeaderIsRendered()
    {
        $html = renderCompetitionList(array('{LANG_ID}' => 'ID'));

        $this->assertListTableHeader($html, 'ID');
    }

    function testCompetitionNameColumnHeaderIsRendered()
    {
        $html = renderCompetitionList(array('{LANG_COMPETITION}' => 'Competitie'));

        $this->assertListTableHeader($html, 'Competitie');
    }

    function testActionsColumnHeaderIsRendered()
    {
        $html = renderCompetitionList(array('{LANG_ACTIONS}' => 'Acties'));

        $this->assertListTableHeader($html, 'Acties');
    }

    function testAddLinkIsRenderedWhenProvided()
    {
        $html = renderCompetitionList(array(
            '{COMPETITION_ADD}' => '<a href="/?com=1&amp;option=add">Toevoegen</a>',
        ));

        $this->assertOverviewAddLink($html);
    }

    function testNoAddLinkWhenNotProvided()
    {
        $html = renderCompetitionList(array('{COMPETITION_ADD}' => ''));

        $this->assertHtmlNotContains(
            $html,
            'option=add',
            'Expected no add link when {COMPETITION_ADD} is empty.'
        );
    }

    function testEditActionLinkIsRenderedInDataRow()
    {
        $html = renderCompetitionList(array(
            '{CONTENT}' => '<tr class="odd"><td>1</td>'
                         . '<td>WK 2026</td>'
                         . '<td><a href="/?com=1&amp;option=edit&amp;id=1">Wijzigen</a></td>'
                         . '</tr>',
        ));

        $this->assertListTableActionLink($html, 'option=edit');
    }

    function testStatusMessageAreaIsAbsentWhenEmpty()
    {
        $html = renderCompetitionList(array('{COMPETITION_MSG}' => ''));

        $this->assertNoAlert($html);
    }
}

// ===========================================================================
// 4. Games list (game.tpl.php)
// ===========================================================================

/**
 * Verify that the games list template renders the expected multi-column
 * structure including date, city, poule, country, result and action columns.
 */
class TestOfGamesListStructure extends UiMarkupAssertions
{
    function testTitlePanelWithHeadingIsPresent()
    {
        $html = renderGamesList();

        $this->assertContainsClass($html, 'title');
        $this->assertContainsTag($html, 'h2', 'Wedstrijden');
    }

    function testListTableIsPresent()
    {
        $html = renderGamesList();

        $this->assertListTable($html);
    }

    function testDateColumnHeaderIsRendered()
    {
        $html = renderGamesList(array('{LANG_DATE}' => 'Datum'));

        $this->assertListTableHeader($html, 'Datum');
    }

    function testCityColumnHeaderIsRendered()
    {
        $html = renderGamesList(array('{LANG_CITY}' => 'Stad'));

        $this->assertListTableHeader($html, 'Stad');
    }

    function testResultColumnHeaderIsRendered()
    {
        $html = renderGamesList(array('{LANG_RESULT}' => 'Uitslag'));

        $this->assertListTableHeader($html, 'Uitslag');
    }

    function testActionsColumnHeaderIsRendered()
    {
        $html = renderGamesList(array('{LANG_ACTIONS}' => 'Acties'));

        $this->assertListTableHeader($html, 'Acties');
    }

    function testAddLinkIsRenderedWhenProvided()
    {
        $html = renderGamesList(array(
            '{GAME_ADD}' => '<a href="/?com=5&amp;option=add">Toevoegen</a>',
        ));

        $this->assertOverviewAddLink($html);
    }

    function testTemplateNameTokenIsReplacedInImageSrc()
    {
        $html = renderGamesList(array('{TEMPLATE_NAME}' => 'orange'));

        $this->assertHtmlNotContains(
            $html,
            '{TEMPLATE_NAME}',
            'Expected {TEMPLATE_NAME} token to be replaced in rendered output.'
        );
        $this->assertHtmlContains(
            $html,
            'templates/orange/',
            'Expected template path to appear in the rendered image src.'
        );
    }
}

// ===========================================================================
// 5. Prediction overview (prediction.tpl.php)
// ===========================================================================

/**
 * Verify that the prediction overview template renders the title panel,
 * message areas, user summary, game content area, and submit form correctly.
 */
class TestOfPredictionOverviewStructure extends UiMarkupAssertions
{
    function testTitlePanelWithHeadingIsPresent()
    {
        $html = renderPredictionOverview();

        $this->assertContainsClass($html, 'title');
        $this->assertContainsTag($html, 'h2', 'Voorspellingen');
    }

    function testFormWrapperIsPresentForPredictionSubmission()
    {
        $html = renderPredictionOverview();

        $this->assertFormPresent($html);
        $this->assertFormMethod($html, 'post');
    }

    function testSubmitButtonIsRenderedWhenProvided()
    {
        $html = renderPredictionOverview(array(
            '{PREDICTION_BUTTONS}' => '<input type="submit" name="submit" value="Opslaan" />',
        ));

        $this->assertFormSubmitButton($html, 'Opslaan');
    }

    function testMessageAreaIsRenderedForPredictionMsg()
    {
        $html = renderPredictionOverview(array(
            '{PREDICTION_MSG}' => '<div class="alert alert-info">Vul uw voorspellingen in.</div>',
        ));

        $this->assertInfoAlert($html);
    }

    function testErrorMessageAreaIsRendered()
    {
        $html = renderPredictionOverview(array(
            '{ERROR_MSG}' => '<div class="alert alert-danger">Er is een fout opgetreden.</div>',
        ));

        $this->assertDangerAlert($html);
    }

    function testSubmissionMessageAreaIsRendered()
    {
        $html = renderPredictionOverview(array(
            '{SUBMISSION_MSG}' => '<div class="alert alert-success">Voorspelling opgeslagen.</div>',
        ));

        $this->assertSuccessAlert($html);
    }

    function testUserContentAreaIsRendered()
    {
        $html = renderPredictionOverview(array(
            '{USER_CONTENT}' => '<p>Jan de Vries</p>',
        ));

        $this->assertHtmlContains(
            $html,
            'Jan de Vries',
            'Expected user content area to contain the user name.'
        );
    }

    function testGameContentAreaIsRendered()
    {
        $html = renderPredictionOverview(array(
            '{GAME_CONTENT}' => '<table class="list"><tr>'
                              . '<th>Thuis</th><th>Uitslag</th><th>Uit</th>'
                              . '</tr></table>',
        ));

        $this->assertListTable($html);
    }
}

// ===========================================================================
// 6. Prediction user list (user.tpl.php)
// ===========================================================================

/**
 * Verify that the prediction user list template renders the title panel, a
 * list table, and a message area for status feedback.
 */
class TestOfUserPredictionListStructure extends UiMarkupAssertions
{
    function testTitlePanelWithHeadingIsPresent()
    {
        $html = renderUserPredictionList();

        $this->assertContainsClass($html, 'title');
        $this->assertContainsTag($html, 'h2', 'Deelnemers voorspellingen');
    }

    function testListTableIsPresent()
    {
        $html = renderUserPredictionList();

        $this->assertListTable($html);
    }

    function testDataRowsWithUserLinksAreRendered()
    {
        $html = renderUserPredictionList(array(
            '{CONTENT}' => '<tr class="odd">'
                         . '<td><a href="/?competition=1&amp;com=7&amp;id=3">Jan de Vries</a></td>'
                         . '</tr>',
        ));

        $this->assertListTableRowCount($html, 1);
        $this->assertListTableActionLink($html, 'com=7');
    }

    function testStatusMessageAreaIsAbsentWhenEmpty()
    {
        $html = renderUserPredictionList(array('{USER_MSG}' => ''));

        $this->assertNoAlert($html);
    }

    function testStatusMessageAreaIsRenderedWhenSet()
    {
        $html = renderUserPredictionList(array(
            '{USER_MSG}' => '<div class="alert alert-info">Selecteer een deelnemer.</div>',
        ));

        $this->assertInfoAlert($html);
    }
}

// ===========================================================================
// 7. Participant list (participant.tpl.php)
// ===========================================================================

/**
 * Verify that the participant list template renders the card wrapper, list
 * table with correct column headers, a filter area, and action links.
 */
class TestOfParticipantListStructure extends UiMarkupAssertions
{
    function testTitlePanelWithHeadingIsPresent()
    {
        $html = renderParticipantList();

        $this->assertContainsClass($html, 'title');
        $this->assertContainsTag($html, 'h2', 'Deelnemers');
    }

    function testCardWrapperIsPresentAroundTable()
    {
        $html = renderParticipantList();

        $this->assertHtmlContains(
            $html,
            'id="participant"',
            'Expected the participant card wrapper (#participant) to be present.'
        );
        $this->assertContainsClass($html, 'card');
    }

    function testListTableIsPresent()
    {
        $html = renderParticipantList();

        $this->assertListTable($html);
    }

    function testIdColumnHeaderIsRendered()
    {
        $html = renderParticipantList(array('{LANG_ID}' => 'ID'));

        $this->assertListTableHeader($html, 'ID');
    }

    function testParticipantNameColumnHeaderIsRendered()
    {
        $html = renderParticipantList(array('{LANG_PARTICIPANT}' => 'Deelnemer'));

        $this->assertListTableHeader($html, 'Deelnemer');
    }

    function testActionsColumnHeaderIsRendered()
    {
        $html = renderParticipantList(array('{LANG_ACTIONS}' => 'Acties'));

        $this->assertListTableHeader($html, 'Acties');
    }

    function testFilterAreaIsRendered()
    {
        $html = renderParticipantList(array('{LANG_FILTER}' => 'Filter'));

        $this->assertHtmlContains(
            $html,
            'Filter',
            'Expected the filter label to appear in the rendered output.'
        );
    }

    function testEditActionLinkIsRenderedInDataRow()
    {
        $html = renderParticipantList();

        $this->assertListTableActionLink($html, 'option=edit');
    }
}

// ===========================================================================
// 8. User management list (user.tpl.php)
// ===========================================================================

/**
 * Verify that the user management list template renders the card wrapper, list
 * table with column headers, usergroup filter, and add link correctly.
 */
class TestOfUserManagementListStructure extends UiMarkupAssertions
{
    function testTitlePanelWithHeadingIsPresent()
    {
        $html = renderUserList();

        $this->assertContainsClass($html, 'title');
        $this->assertContainsTag($html, 'h2', 'Gebruikers');
    }

    function testCardWrapperIsPresentAroundTable()
    {
        $html = renderUserList();

        $this->assertHtmlContains(
            $html,
            'id="user"',
            'Expected the user card wrapper (#user) to be present.'
        );
        $this->assertContainsClass($html, 'card');
    }

    function testListTableIsPresent()
    {
        $html = renderUserList();

        $this->assertListTable($html);
    }

    function testFullNameColumnHeaderIsRendered()
    {
        $html = renderUserList(array('{LANG_USER_FULLNAME}' => 'Naam'));

        $this->assertListTableHeader($html, 'Naam');
    }

    function testUserGroupColumnHeaderIsRendered()
    {
        $html = renderUserList(array('{LANG_USERGROUP}' => 'Gebruikersgroep'));

        $this->assertListTableHeader($html, 'Gebruikersgroep');
    }

    function testActionsColumnHeaderIsRendered()
    {
        $html = renderUserList(array('{LANG_ACTIONS}' => 'Acties'));

        $this->assertListTableHeader($html, 'Acties');
    }

    function testAddLinkIsRenderedWhenProvided()
    {
        $html = renderUserList(array(
            '{USER_ADD}' => '<a href="/?com=4&amp;option=add">Toevoegen</a>',
        ));

        $this->assertOverviewAddLink($html);
    }

    function testUsergroupFilterAreaIsRendered()
    {
        $html = renderUserList(array(
            '{LANG_USERGROUP}' => 'Gebruikersgroep',
            '{USERGROUP_LIST}' => '<ul><li><a href="/?com=4">Alle</a></li></ul>',
        ));

        $this->assertHtmlContains(
            $html,
            'Gebruikersgroep',
            'Expected the usergroup filter label to appear in the rendered output.'
        );
    }

    function testEditActionLinkIsRenderedInDataRow()
    {
        $html = renderUserList();

        $this->assertListTableActionLink($html, 'option=edit');
    }
}

// ===========================================================================
// 9. Token replacement — no unreplaced placeholders in output
// ===========================================================================

/**
 * Verify that every placeholder token is replaced in every overview template
 * so that raw token strings like {COM_NAME} are never visible to end-users.
 */
class TestOfOverviewTokenReplacement extends UiMarkupAssertions
{
    function testStandingsTableHasNoUnreplacedTokens()
    {
        $html   = renderStandingsTable();
        $tokens = array_keys(standingsTableDefaults());

        foreach ($tokens as $token) {
            $this->assertHtmlNotContains(
                $html,
                $token,
                "Placeholder {$token} was not replaced in standings table output."
            );
        }
    }

    function testSubleagueTableHasNoUnreplacedTokens()
    {
        $html   = renderSubleagueTable();
        $tokens = array_keys(subleagueTableDefaults());

        foreach ($tokens as $token) {
            $this->assertHtmlNotContains(
                $html,
                $token,
                "Placeholder {$token} was not replaced in subleague table output."
            );
        }
    }

    function testCompetitionListHasNoUnreplacedTokens()
    {
        $html   = renderCompetitionList();
        $tokens = array_keys(competitionListDefaults());

        foreach ($tokens as $token) {
            $this->assertHtmlNotContains(
                $html,
                $token,
                "Placeholder {$token} was not replaced in competition list output."
            );
        }
    }

    function testGamesListHasNoUnreplacedTokens()
    {
        $html   = renderGamesList();
        $tokens = array_keys(gamesListDefaults());

        foreach ($tokens as $token) {
            $this->assertHtmlNotContains(
                $html,
                $token,
                "Placeholder {$token} was not replaced in games list output."
            );
        }
    }

    function testPredictionOverviewHasNoUnreplacedTokens()
    {
        $html   = renderPredictionOverview();
        $tokens = array_keys(predictionOverviewDefaults());

        foreach ($tokens as $token) {
            $this->assertHtmlNotContains(
                $html,
                $token,
                "Placeholder {$token} was not replaced in prediction overview output."
            );
        }
    }

    function testUserPredictionListHasNoUnreplacedTokens()
    {
        $html   = renderUserPredictionList();
        $tokens = array_keys(userPredictionListDefaults());

        foreach ($tokens as $token) {
            $this->assertHtmlNotContains(
                $html,
                $token,
                "Placeholder {$token} was not replaced in user prediction list output."
            );
        }
    }

    function testParticipantListHasNoUnreplacedTokens()
    {
        $html   = renderParticipantList();
        $tokens = array_keys(participantListDefaults());

        foreach ($tokens as $token) {
            $this->assertHtmlNotContains(
                $html,
                $token,
                "Placeholder {$token} was not replaced in participant list output."
            );
        }
    }

    function testUserListHasNoUnreplacedTokens()
    {
        $html   = renderUserList();
        $tokens = array_keys(userListDefaults());

        foreach ($tokens as $token) {
            $this->assertHtmlNotContains(
                $html,
                $token,
                "Placeholder {$token} was not replaced in user list output."
            );
        }
    }
}

// ===========================================================================
// 10. Action regions — add link and data-row action rendering
// ===========================================================================

/**
 * Verify that add links and row-level action links are correctly rendered
 * across the representative overview templates.
 *
 * These tests protect the critical user actions (create, edit, delete) so
 * that restyling cannot accidentally remove or break them.
 */
class TestOfOverviewActionRegions extends UiMarkupAssertions
{
    function testCompetitionListRendersAddLinkWithOptionAdd()
    {
        $html = renderCompetitionList(array(
            '{COMPETITION_ADD}' => '<a href="/?com=1&amp;option=add">Toevoegen</a>',
        ));

        $this->assertOverviewAddLink($html);
        $this->assertContainsLink($html, 'option=add');
    }

    function testGamesListRendersAddLinkWithOptionAdd()
    {
        $html = renderGamesList(array(
            '{GAME_ADD}' => '<a href="/?com=5&amp;option=add">Toevoegen</a>',
        ));

        $this->assertOverviewAddLink($html);
        $this->assertContainsLink($html, 'option=add');
    }

    function testUserListRendersAddLinkWithOptionAdd()
    {
        $html = renderUserList(array(
            '{USER_ADD}' => '<a href="/?com=4&amp;option=add">Toevoegen</a>',
        ));

        $this->assertOverviewAddLink($html);
        $this->assertContainsLink($html, 'option=add');
    }

    function testCompetitionListRendersEditActionLinkInRow()
    {
        $html = renderCompetitionList(array(
            '{CONTENT}' => '<tr class="odd"><td>1</td><td>WK 2026</td>'
                         . '<td><a href="/?com=1&amp;option=edit&amp;id=1">Wijzigen</a></td>'
                         . '</tr>',
        ));

        $this->assertListTableActionLink($html, 'option=edit&amp;id=1');
    }

    function testGamesListRendersEditAndDeleteLinksInRow()
    {
        $html = renderGamesList(array(
            '{CONTENT}' => '<div class="d-none d-md-block"><div class="table-responsive">'
                         . '<table class="list" cellpadding="0" cellspacing="0"><tr>'
                         . '<th>Datum</th><th>Stad</th><th>Poule</th>'
                         . '<th colspan="2">Land</th><th>Uitslag</th>'
                         . '<th colspan="2">Land</th><th>Gele</th><th>Rode</th>'
                         . '<th>Acties</th></tr>'
                         . '<tr class="odd"><td>01-06-2026</td>'
                         . '<td>Amsterdam</td><td>A</td>'
                         . '<td>Nederland</td><td>1 - 0</td><td>Duitsland</td>'
                         . '<td>0</td><td>0</td>'
                         . '<td><a href="/?com=5&amp;option=edit&amp;id=1">Wijzigen</a>'
                         . ' <a href="/?com=5&amp;option=delete&amp;id=1">Verwijderen</a></td>'
                         . '</tr>'
                         . '<tr><td colspan="11">Aantal: 1</td></tr>'
                         . '</table></div></div>',
        ));

        $this->assertListTableActionLink($html, 'option=edit');
        $this->assertListTableActionLink($html, 'option=delete');
    }

    function testStandingsTableRendersFormSubmitButton()
    {
        $html = renderStandingsTable(array(
            '{TABLE_BUTTONS}' => '<input type="submit" name="submit" value="Bijwerken" />',
        ));

        $this->assertFormPresent($html);
        $this->assertFormSubmitButton($html, 'Bijwerken');
        $this->assertHtmlContains($html, 'name="submit"');
    }

    function testUserPredictionListRendersUserDetailLink()
    {
        $html = renderUserPredictionList(array(
            '{CONTENT}' => '<tr class="odd">'
                         . '<td><a href="/?competition=1&amp;com=7&amp;id=3">Jan de Vries</a></td>'
                         . '</tr>',
        ));

        $this->assertContainsLink($html, 'com=7&amp;id=3');
    }

    function testParticipantListRendersEditActionLinkInRow()
    {
        $html = renderParticipantList();

        $this->assertListTableActionLink($html, 'option=edit');
    }
}
?>
