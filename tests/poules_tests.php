<?php
require_once('./simpletest/autorun.php');
require_once('./mock/appMock.php');
require_once('../modules/poules/poule.class.php');
require_once('../modules/competitions/competition.class.php');

class TestOfPoules extends UnitTestCase {
    
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
    
    function testAddPoule() {
        $name = 'testPoule1';
        $pouleId = Poule::add($name, $this->competitionId);

        $this->assertTrue(Poule::exists($pouleId));
        
        $poule = new Poule($pouleId);
        $this->assertEqual($poule->getName(), $name);
        
        $poule->delete();
    }

    function testDeletePoule() {
        $name = 'testPoule1';
        $pouleId = Poule::add($name, $this->competitionId);
        
        $poule = new Poule($pouleId);
        $poule->delete();
        
        $this->assertFalse(Poule::exists($pouleId));        
    }
    
    function testUpdatePoule() {
        $name_before = 'testPoule1';
        $name_after = 'testPoule2';
        $pouleId = Poule::add($name_before, $this->competitionId);
        
        $poule = new Poule($pouleId);
        $this->assertEqual($poule->getName(), $name_before);

        $poule = new Poule($pouleId);
        $poule->setName($name_after);
        $poule->save();
        
        $poule = new Poule($pouleId);
        $this->assertEqual($poule->getName(), $name_after);
        $poule->delete();        
    }
    
    function testGetAllPoules()
    {
        $name = 'testPoule1';
        
        $count = 5;
        for ($i=0; $i<$count; $i++)
        {
            $pouleId = Poule::add($name, $this->competitionId);
        }
        
        $c = 0;
        Poule::getAllPoules($this->competitionId);
        while (Poule::nextPoule() != null)
        {
            $c++;
        }
        $this->assertEqual($count, $c);        
    }
    
}
?>