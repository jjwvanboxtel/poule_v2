<?php
require_once('./simpletest/autorun.php');
require_once('./mock/appMock.php');
require_once('../modules/games/game.class.php');
require_once('../modules/competitions/competition.class.php');
require_once('../modules/cities/city.class.php');
require_once('../modules/countries/country.class.php');
require_once('../modules/poules/poule.class.php');

class TestOfGames extends UnitTestCase {
    
    private $competitionId = 0;
    private $cityIds = array();
    private $countryIds = array();
    private $pouleIds = array();
    
    function setUp() {
        App::clearAll();
        
        $header['name'] = 'headerCompetition1.png';
        $flagCountry1['name'] = 'testCountry1.png';
        $flagCountry2['name'] = 'testCountry2.png';
        $flagCountry3['name'] = 'testCountry3.png';
        $flagCountry4['name'] = 'testCountry4.png';

        $this->competitionId = Competition::add('testCompetition1', 'descriptionCompetition1', $header, time(), 5, 50, 25, 20);
        array_push($this->cityIds, City::add('testCity1', $this->competitionId));
        array_push($this->cityIds, City::add('testCity2', $this->competitionId));
        array_push($this->countryIds, Country::add('testCountry1', $flagCountry1, $this->competitionId));
        array_push($this->countryIds, Country::add('testCountry2', $flagCountry2, $this->competitionId));
        array_push($this->countryIds, Country::add('testCountry3', $flagCountry3, $this->competitionId));
        array_push($this->countryIds, Country::add('testCountry4', $flagCountry4, $this->competitionId));
        array_push($this->pouleIds, Poule::add('testPoule1', $this->competitionId));
        array_push($this->pouleIds, Poule::add('testPoule2', $this->competitionId));
    }

    function tearDown()
    {
        App::clearAll();
    }
    
    function testAddgame() {
        $date = '25-05-2014';
        $result = '0-0';
        $red_cards = '0';
        $yellow_cards = '0';

        $gameId = Game::add($this->competitionId, $date, $result, $red_cards, $yellow_cards, $this->cityIds[0], $this->countryIds[0], $this->countryIds[1], $this->pouleIds[0]);

        $this->assertTrue(Game::exists($gameId));
        
        $game = new Game($gameId);
        $this->assertEqual($game->getDate(), $date);
        $this->assertEqual($game->getResult(), $result);
        $this->assertEqual($game->getRedCards(), $red_cards);
        $this->assertEqual($game->getYellowCards(), $yellow_cards);
        $this->assertEqual($game->getCity()->getId(), $this->cityIds[0]);
        $this->assertEqual($game->getHomeCountry()->getId(), $this->countryIds[0]);
        $this->assertEqual($game->getAwayCountry()->getId(), $this->countryIds[1]);
        $this->assertEqual($game->getPoule()->getId(), $this->pouleIds[0]);
        
        $game->delete();
    }

    function testDeleteGame() {
        $date = '25-05-2014';
        $result = '0-0';
        $red_cards = '0';
        $yellow_cards = '0';

        Game::add($this->competitionId, $date, $result, $red_cards, $yellow_cards, $this->cityIds[0], $this->countryIds[0], $this->countryIds[1], $this->pouleIds[0]);
        
        $gameId = App::$_DB->getLastId();
        
        $game = new Game($gameId);
        $game->delete();
        
        $this->assertFalse(Game::exists($gameId));        
    }
    
    function testUpdateGame() {
        $date_before = '25-05-2014';
        $result_before = '0-0';
        $red_cards_before = '0';
        $yellow_cards_before = '0';
        $date_after = '26-05-2014';
        $result_after = '1-1';
        $red_cards_after = '1';
        $yellow_cards_after = '1';
        
        $gameId = Game::add($this->competitionId, $date_before, $result_before, $red_cards_before, $yellow_cards_before, $this->cityIds[0], $this->countryIds[0], $this->countryIds[1], $this->pouleIds[0]);
                
        $game = new Game($gameId);
        $this->assertEqual($game->getDate(), $date_before);
        $this->assertEqual($game->getResult(), $result_before);
        $this->assertEqual($game->getRedCards(), $red_cards_before);
        $this->assertEqual($game->getYellowCards(), $yellow_cards_before);
        $this->assertEqual($game->getCity()->getId(), $this->cityIds[0]);
        $this->assertEqual($game->getHomeCountry()->getId(), $this->countryIds[0]);
        $this->assertEqual($game->getAwayCountry()->getId(), $this->countryIds[1]);
        $this->assertEqual($game->getPoule()->getId(), $this->pouleIds[0]);

        $game = new Game($gameId);
        $game->setDate($date_after);
        $game->setResult($result_after);
        $game->setRedCards($red_cards_after);
        $game->setYellowCards($yellow_cards_after);
        $game->setCity($this->cityIds[1]);
        $game->setHomeCountry($this->countryIds[2]);
        $game->setAwayCountry($this->countryIds[3]);
        $game->setPoule($this->pouleIds[1]);
        $game->save();
        
        $game = new Game($gameId);
        $this->assertEqual($game->getDate(), $date_after);
        $this->assertEqual($game->getResult(), $result_after);
        $this->assertEqual($game->getRedCards(), $red_cards_after);
        $this->assertEqual($game->getYellowCards(), $yellow_cards_after);
        $this->assertEqual($game->getCity()->getId(), $this->cityIds[1]);
        $this->assertEqual($game->getHomeCountry()->getId(), $this->countryIds[2]);
        $this->assertEqual($game->getAwayCountry()->getId(), $this->countryIds[3]);
        $this->assertEqual($game->getPoule()->getId(), $this->pouleIds[1]);
        $game->delete();        
    }
    
    function testGetAllGames()
    {
        $count = 5;
        for ($i=0; $i<$count; $i++)
        {
            Game::add($this->competitionId, '25-05-2014', '0-0', '0', '0', $this->cityIds[0], $this->countryIds[0], $this->countryIds[1], $this->pouleIds[0]);
        }
        
        $c = 0;
        Game::getAllGames($this->competitionId);
        while (Game::nextgame() != null)
        {
            $c++;
        }
        $this->assertEqual($count, $c);        
    }
    
}
?>