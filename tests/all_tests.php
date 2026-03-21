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
    }
}
?>