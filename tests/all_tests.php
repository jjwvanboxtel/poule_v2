<?php
define('TEST_DEBUG', false);
define('TEST_MODE', true);

require_once('./simpletest/autorun.php');

class AllTests extends TestSuite {

    function AllTests() {
        $this->TestSuite('All tests for Poule system');
        $this->addFile('cities_tests.php');
        $this->addFile('competitions_tests.php');
        $this->addFile('countries_tests.php');
        $this->addFile('forms_tests.php');
        $this->addFile('games_tests.php');
        $this->addFile('participants_tests.php');
        $this->addFile('players_tests.php');      
        $this->addFile('poules_tests.php');
        $this->addFile('predictions_tests.php');
        $this->addFile('referees_tests.php');  
        $this->addFile('rounds_tests.php');
        $this->addFile('scorings_tests.php');
        $this->addFile('table_tests.php');

        // UI regression suites (001-ui-ux-refresh).
        // ui_testcase.php defines the shared UiTestCase base class.
        // ui_shell_assertions.php adds UiShellAssertions for shell/navigation.
        // ui_markup_assertions.php adds UiMarkupAssertions for alerts/forms/tables.
        require_once('ui/ui_testcase.php');
        require_once('ui/ui_shell_assertions.php');
        require_once('ui/ui_markup_assertions.php');

        // T008: Shell render regressions for placeholder usage, hero context
        // and sidebar output (locks down the shell before the T009 refactor).
        $this->addFile('ui/ui_shell_render_tests.php');
    }
}
?>