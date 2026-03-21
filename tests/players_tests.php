<?php
require_once('./simpletest/autorun.php');
require_once('./mock/appMock.php');
require_once('../modules/players/player.class.php');
require_once('../modules/competitions/competition.class.php');
require_once('../modules/countries/country.class.php');

class TestOfPlayers extends UnitTestCase {
    
    private $competitionId = 0;
    private $countryIds = array();
    
    function setUp() {
        App::clearAll();
        
        $header['name'] = 'headerCompetition1.png';
        $flagCountry1['name'] = 'testCountry1.png';
        $flagCountry2['name'] = 'testCountry2.png';

        $this->competitionId = Competition::add('testCompetition1', 'descriptionCompetition1', $header, time(), 5, 50, 25, 20);
        array_push($this->countryIds, Country::add('testCountry1', $flagCountry1, $this->competitionId));
        array_push($this->countryIds, Country::add('testCountry2', $flagCountry2, $this->competitionId));
    }

    function tearDown()
    {
        App::clearAll();
    }
    
    function testAddPlayer() {
        $name = 'testPlayer1';
        $playerId = Player::add($this->competitionId, $name, $this->countryIds[0]);

        $this->assertTrue(Player::exists($playerId));
        
        $player = new Player($playerId);
        $this->assertEqual($player->getName(), $name);
        $this->assertEqual($player->getCountry()->getId(), $this->countryIds[0]);
        
        $player->delete();
    }

    function testDeletePlayer() {
        $name = 'testPlayer1';
        $playerId = Player::add($this->competitionId, $name, $this->countryIds[0]);
        
        $player = new Player($playerId);
        $player->delete();
        
        $this->assertFalse(Player::exists($playerId));        
    }
    
    function testUpdatePlayer() {
        $name_before = 'testPlayer1';
        $name_after = 'testPlayer2';
        $playerId = Player::add($this->competitionId, $name_before, $this->countryIds[0]);      
        
        $player = new Player($playerId);
        $this->assertEqual($player->getName(), $name_before);
        $this->assertEqual($player->getCountry()->getId(), $this->countryIds[0]);

        $player = new Player($playerId);
        $player->setName($name_after);
        $player->setCountry($this->countryIds[1]);
        $player->save();
        
        $player = new Player($playerId);
        $this->assertEqual($player->getName(), $name_after);
        $this->assertEqual($player->getCountry()->getId(), $this->countryIds[1]);
        $player->delete();        
    }
    
    function testGetAllPlayers()
    {
        $name = 'testPlayer1';
        
        $count = 5;
        for ($i=0; $i<$count; $i++)
        {
            $playerId = Player::add($this->competitionId, $name, $this->countryIds[0]);  
        }
        
        $c = 0;
        Player::getAllPlayers($this->competitionId);
        while (Player::nextPlayer() != null)
        {
            $c++;
        }
        $this->assertEqual($count, $c);        
    }
    
}
?>