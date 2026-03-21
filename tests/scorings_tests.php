<?php
require_once('./simpletest/autorun.php');
require_once('./mock/appMock.php');
require_once('../modules/scorings/scoring.class.php');
require_once('../modules/sections/section.class.php');
require_once('../modules/competitions/competition.class.php');

class TestOfScorings extends UnitTestCase {
    
    private $competitionId = 0;
    
    function setUp() {
        App::clearAll();
        
        $header['name'] = 'headerCompetition1.png';
        $this->competitionId = Competition::add('testCompetition1', 'descriptionCompetition1', $header, time(), 5, 50, 25, 20);
    }

    function tearDown()
    {
        App::clearAll();
    }
        
    function testAddRoundScoring() {
        $roundName = 'testRound1';
        $scoringId = Scoring::addScoringByRound($this->competitionId, $roundName, 1);

        $this->assertTrue(Scoring::exists($scoringId));
        
        $scoring = new Scoring($scoringId);
        $this->assertEqual($scoring->getName(), $roundName);
        $this->assertEqual($scoring->getEnabled($this->competitionId), 0);
        $this->assertEqual($scoring->getPoints($this->competitionId), 0);
        $this->assertEqual($scoring->getSection()->getId(), Section::$_SECTION_KNOCK_OUT_FASE);
        
        Scoring::deleteScoringByRound($scoring->getId());
    }

    function testDeleteScoring() {
        $roundName = 'testRound1';
        $roundId = 1;
        $scoringId = Scoring::addScoringByRound($this->competitionId, $roundName, $roundId);
        
        Scoring::deleteScoringByRound($roundId);
        
        $this->assertFalse(Scoring::exists($scoringId));        
    }

    function testUpdateScorings() {
        $name_before = 'Uitslag groepswedstrijd goed1';
        $enabled_before = 1;
        $points_before = 10;
        $name_after = 'Uitslag groepswedstrijd goed';
        $enabled_after = 0;
        $points_after = 0;

        $scoring = new Scoring(Scoring::$_SCORING_GAME_RESULT_CORRECT);
        $scoring->setName($name_before);
        $scoring->setEnabled($this->competitionId, $enabled_before);
        $scoring->setPoints($this->competitionId, $points_before);
        $scoring->save();

        $scoring = new Scoring(Scoring::$_SCORING_GAME_RESULT_CORRECT);        
        $this->assertEqual($scoring->getName(), $name_before);
        $this->assertEqual($scoring->getEnabled($this->competitionId), $enabled_before);
        $this->assertEqual($scoring->getPoints($this->competitionId), $points_before);
        $this->assertEqual($scoring->getSection()->getId(), Section::$_SECTION_RESULTS);

        $scoring = new Scoring(Scoring::$_SCORING_GAME_RESULT_CORRECT);
        $scoring->setName($name_after);
        $scoring->setEnabled($this->competitionId, $enabled_after);
        $scoring->setPoints($this->competitionId, $points_after);
        $scoring->save();
        
        $scoring = new Scoring(Scoring::$_SCORING_GAME_RESULT_CORRECT);        
        $this->assertEqual($scoring->getName(), $name_after);
        $this->assertEqual($scoring->getEnabled($this->competitionId), $enabled_after);
        $this->assertEqual($scoring->getPoints($this->competitionId), $points_after);
        $this->assertEqual($scoring->getSection()->getId(), Section::$_SECTION_RESULTS);
    }
        
    function testGetAllScorings()
    {               
        $c = 0;
        Scoring::getAllScorings($this->competitionId);
        while (Scoring::nextScoring() != null)
        {
            $c++;
        }
        $this->assertEqual($c, 5);        
    }
    
}
?>