<?php
require_once('./simpletest/autorun.php');
require_once('./mock/appMock.php');
require_once('../modules/rounds/round.class.php');
require_once('../modules/rounds/roundresult.class.php');
require_once('../modules/competitions/competition.class.php');
require_once('../modules/countries/country.class.php');

class TestOfRounds extends UnitTestCase {
    
    private $competitionId = 0;
    private $countryIds = array();

    function setUp() {
        App::clearAll();
        
        $header['name'] = 'headerCompetition1.png';
        $flagCountry1['name'] = 'testCountry1.png';
        $flagCountry2['name'] = 'testCountry2.png';

        $this->countryIds = array();
        $this->competitionId = Competition::add('testCompetition1', 'descriptionCompetition1', $header, time(), 5, 50, 25, 20);
        array_push($this->countryIds, Country::add('testCountry1', $flagCountry1, $this->competitionId));
        array_push($this->countryIds, Country::add('testCountry2', $flagCountry2, $this->competitionId));
    }

    function tearDown()
    {
        App::clearAll();
    }
    
    function testAddRound() {
        $name = 'testRound1';
        $count = 1;
        $roundId = Round::add($this->competitionId, $name, $count);

        $this->assertTrue(Round::exists($roundId));
        
        $round = new Round($roundId);
        $this->assertEqual($round->getName(), $name);
        $this->assertEqual($round->getCount(), $count);
        $countries = $round->getCountries();
        $this->assertEqual($countries[0], 0);
        
        $this->assertTrue(RoundResult::exists($roundId));
        
        $round->delete();
    }

    function testDeleteRound() {
        $name = 'testRound1';
        $count = 1;
        $roundId = Round::add($this->competitionId, $name, $count);
        
        $round = new Round($roundId);
        $round->delete();
        
        $this->assertFalse(Round::exists($roundId));
        $this->assertFalse(RoundResult::exists($roundId));        
    }
    
    function testUpdateRound() {
        $name_before = 'testRound1';
        $count_before = 1;
        $name_after = 'testRound2';
        $count_after = 2;

        $roundId = Round::add($this->competitionId, $name_before, $count_before);
        
        $round = new Round($roundId);
        $this->assertEqual($round->getName(), $name_before);
        $this->assertEqual($round->getCount(), $count_before);
        $countries = $round->getCountries();
        $this->assertEqual($countries[0], 0);

        $round = new Round($roundId);
        $round->setName($name_after);
        $round->setCount($count_after);
        $round->save();

        $round->setCountries($this->countryIds);
        $round->save();
        
        $round = new Round($roundId);
        $this->assertEqual($round->getName(), $name_after);
        $this->assertEqual($round->getCount(), $count_after);
        $countries = $round->getCountries();
        $this->assertEqual($countries[0]->getId(), $this->countryIds[0]);
        $this->assertEqual($countries[1]->getId(), $this->countryIds[1]);
                
        $round->delete();        
    }
    
    function testGetAllRounds()
    {      
        $count = 5;
        for ($i=0; $i<$count; $i++)
        {
            $roundId = Round::add($this->competitionId, 'testRound1', 1);
        }
        
        $c = 0;
        Round::getAllRounds($this->competitionId);
        while (Round::nextRound() != null)
        {
            $c++;
        }
        $this->assertEqual($count, $c);        
    }
    
}
?>