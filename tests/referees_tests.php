<?php
require_once('./simpletest/autorun.php');
require_once('./mock/appMock.php');
require_once('../modules/referees/referee.class.php');
require_once('../modules/competitions/competition.class.php');

class TestOfReferees extends UnitTestCase {
    
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
    
    function testAddReferee() {
        $name = 'testReferee1';
        $refereeId = Referee::add($this->competitionId, $name);

        $this->assertTrue(Referee::exists($refereeId));
        
        $referee = new Referee($refereeId);
        $this->assertEqual($referee->getName(), $name);
        
        $referee->delete();
    }

    function testDeleteReferee() {
        $name = 'testReferee1';
        $refereeId = Referee::add($this->competitionId, $name);
        
        $referee = new Referee($refereeId);
        $referee->delete();
        
        $this->assertFalse(Referee::exists($refereeId));        
    }
    
    function testUpdateReferee() {
        $name_before = 'testReferee1';
        $name_after = 'testReferee2';
        
        $refereeId = Referee::add($this->competitionId, $name_before);
        
        $referee = new Referee($refereeId);
        $this->assertEqual($referee->getName(), $name_before);

        $referee = new Referee($refereeId);
        $referee->setName($name_after);
        $referee->save();
        
        $referee = new Referee($refereeId);
        $this->assertEqual($referee->getName(), $name_after);
        $referee->delete();        
    }
    
    function testGetAllReferees()
    {
        $name = 'testReferee1';
        
        $count = 5;
        for ($i=0; $i<$count; $i++)
        {
            $refereeId = Referee::add($this->competitionId, $name);
        }
        
        $c = 0;
        Referee::getAllReferees($this->competitionId);
        while (Referee::nextReferee() != null)
        {
            $c++;
        }
        $this->assertEqual($count, $c);        
    }
    
}
?>