<?php
require_once('./simpletest/autorun.php');
require_once('./mock/appMock.php');
require_once('../modules/predictions/gameprediction.class.php');
require_once('../modules/predictions/roundprediction.class.php');
require_once('../modules/predictions/questionprediction.class.php');
require_once('../modules/competitions/competition.class.php');
require_once('../modules/games/game.class.php');
require_once('../modules/cities/city.class.php');
require_once('../modules/countries/country.class.php');
require_once('../modules/poules/poule.class.php');
require_once('../modules/questions/question.class.php');
require_once('../modules/rounds/round.class.php');
require_once('../modules/rounds/roundresult.class.php');
require_once('../modules/users/user.class.php');
require_once('../modules/usergroups/usergroup.class.php');

class TestOfPredictions extends UnitTestCase {
    
    private $competitionId = 0;
    private $cityIds = array();
    private $countryIds = array();
    private $pouleIds = array();
    
    function setUp() {
        App::clearAll();
        
        $flagCountry1['name'] = 'testCountry1.png';
        $flagCountry2['name'] = 'testCountry2.png';
        $header['name'] = 'headerCompetition1.png';

        $this->competitionId = Competition::add('testCompetition1', 'descriptionCompetition1', $header, time(), 5, 50, 25, 20);
        array_push($this->cityIds, City::add('testCity1', $this->competitionId));
        array_push($this->countryIds, Country::add('testCountry1', $flagCountry1, $this->competitionId));
        array_push($this->countryIds, Country::add('testCountry2', $flagCountry2, $this->competitionId));
        array_push($this->pouleIds, Poule::add('testPoule1', $this->competitionId));
    }

    function tearDown()
    {
        App::clearAll();
    }
    
    function testAddPredictionsByUser() {
        $game_result = '0-0';
        $game_red_cards = '0';
        $game_yellow_cards = '0';
        $round_country = null;
        $question_anwser = '';
    
        $gameId = Game::add($this->competitionId, '25-05-2014', '0-0', '0', '0', $this->cityIds[0], $this->countryIds[0], $this->countryIds[1], $this->pouleIds[0]);
        $questionId = Question::add($this->competitionId, '1', 'testQuestion1', '1');
        $roundId = Round::add($this->competitionId, 'testRound1', '1');

        $participantId = Participant::addp(1, 'poule@test.nl', 'poule01', 'testPerson', 'lastName', '8888888888', 3,
                                         '1111AA', 'testStraat', 'testPlaats', '1', 'a', '1111111111');
                 
        $this->assertTrue(GamePrediction::exists($participantId, $gameId));
        $this->assertTrue(QuestionPrediction::exists($participantId, $questionId));
        $this->assertTrue(RoundPrediction::exists($participantId, $roundId));
        
        $gameprediction = new GamePrediction($participantId, $gameId);
        $this->assertEqual($gameprediction->getResult(), $game_result);
        $this->assertEqual($gameprediction->getRedCards(), $game_red_cards);
        $this->assertEqual($gameprediction->getYellowCards(), $game_yellow_cards);
        
        $roundprediction = new RoundPrediction($participantId, $roundId, 0);
        $this->assertEqual($roundprediction->getCountry(), $round_country);

        $questionprediction = new QuestionPrediction($participantId, $questionId);
        $this->assertEqual($questionprediction->getAnswer(), $question_anwser);
        
        $gameprediction->delete();
        $roundprediction->delete();
        $questionprediction->delete();
        
        $game = new Game($gameId);
        $game->delete();
        $question = new Question($questionId);
        $question->delete();
        $round = new Round($roundId);
        $round->delete();
        $participant = new Participant($participantId);
        $participant->delete();
    }

    function testAddPredictionsByGame() {
        $game_result = '0-0';
        $game_red_cards = '0';
        $game_yellow_cards = '0';
        
        $participantId = Participant::addp(1, 'poule@test.nl', 'poule01', 'testPerson', 'lastName', '8888888888', 3,
                                         '1111AA', 'testStraat', 'testPlaats', '1', 'a', '1111111111');

        $gameId = Game::add($this->competitionId, '25-05-2014', '0-0', '0', '0', $this->cityIds[0], $this->countryIds[0], $this->countryIds[1], $this->pouleIds[0]);

        $this->assertTrue(GamePrediction::exists($participantId, $gameId));
        
        $gameprediction = new GamePrediction($participantId, $gameId);
        $this->assertEqual($gameprediction->getResult(), $game_result);
        $this->assertEqual($gameprediction->getRedCards(), $game_red_cards);
        $this->assertEqual($gameprediction->getYellowCards(), $game_yellow_cards);
        
        $gameprediction->delete();
        
        $game = new Game($gameId);
        $game->delete();
        $participant = new Participant($participantId);
        $participant->delete();
    }
    
    function testAddPredictionsByRound() {
        $round_country = null;
    
        $participantId = Participant::addp(1, 'poule@test.nl', 'poule01', 'testPerson', 'lastName', '8888888888', 3,
                                         '1111AA', 'testStraat', 'testPlaats', '1', 'a', '1111111111');

        $roundId = Round::add($this->competitionId, 'testRound1', '1');
        
        $this->assertTrue(RoundPrediction::exists($participantId, $roundId));
        
        $roundprediction = new RoundPrediction($participantId, $roundId, 0);
        $this->assertEqual($roundprediction->getCountry(), $round_country);
        
        $roundprediction->delete();
        
        $round = new Round($roundId);
        $round->delete();
        $participant = new Participant($participantId);
        $participant->delete();
    }
    
    function testAddPredictionsByQuestion() {
        $question_anwser = '';
    
        $participantId = Participant::addp(1, 'poule@test.nl', 'poule01', 'testPerson', 'lastName', '8888888888', 3,
                                         '1111AA', 'testStraat', 'testPlaats', '1', 'a', '1111111111');

        $questionId = Question::add($this->competitionId, '1', 'testQuestion1', '1');
        
        $this->assertTrue(QuestionPrediction::exists($participantId, $questionId));
        
        $questionprediction = new QuestionPrediction($participantId, $questionId);
        $this->assertEqual($questionprediction->getAnswer(), $question_anwser);
        
        $questionprediction->delete();
        
        $question = new Question($questionId);
        $question->delete();
        $participant = new Participant($participantId);
        $participant->delete();
    }
    
    function testUpdatePredictions() {
        $game_result_before = '0-0';
        $game_red_cards_before = '0';
        $game_yellow_cards_before = '0';
        $round_country_before = null;
        $question_anwser_before = '';
        $game_result_after = '1-1';
        $game_red_cards_after = '1';
        $game_yellow_cards_after = '1';
        $round_country_after = $this->countryIds[0];
        $question_anwser_after = 'testAntwoord';
        
        $gameId = Game::add($this->competitionId, '25-05-2014', '0-0', '0', '0', $this->cityIds[0], $this->countryIds[0], $this->countryIds[1], $this->pouleIds[0]);
        $questionId = Question::add($this->competitionId, '1', 'testQuestion1', '1');
        $roundId = Round::add($this->competitionId, 'testRound1', '1');

        $participantId = Participant::addp(1, 'poule@test.nl', 'poule01', 'testPerson', 'lastName', '8888888888', 3,
                                         '1111AA', 'testStraat', 'testPlaats', '1', 'a', '1111111111');
                 
        $this->assertTrue(GamePrediction::exists($participantId, $gameId));
        $this->assertTrue(QuestionPrediction::exists($participantId, $questionId));
        $this->assertTrue(RoundPrediction::exists($participantId, $roundId));
        
        $gameprediction = new GamePrediction($participantId, $gameId);
        $this->assertEqual($gameprediction->getResult(), $game_result_before);
        $this->assertEqual($gameprediction->getRedCards(), $game_red_cards_before);
        $this->assertEqual($gameprediction->getYellowCards(), $game_yellow_cards_before);
        
        $roundprediction = new RoundPrediction($participantId, $roundId, 0);
        $this->assertEqual($roundprediction->getCountry(), $round_country_before);

        $questionprediction = new QuestionPrediction($participantId, $questionId);
        $this->assertEqual($questionprediction->getAnswer(), $question_anwser_before);
        
        $gameprediction->setResult($game_result_after);
        $gameprediction->setRedCards($game_red_cards_after);
        $gameprediction->setYellowCards($game_yellow_cards_after);
        $gameprediction->save();
        $roundprediction->setCountry($round_country_after);
        $roundprediction->save();
        $questionprediction->setAnswer($question_anwser_after);
        $questionprediction->save();
        
        $gameprediction = new GamePrediction($participantId, $gameId);
        $this->assertEqual($gameprediction->getResult(), $game_result_after);
        $this->assertEqual($gameprediction->getRedCards(), $game_red_cards_after);
        $this->assertEqual($gameprediction->getYellowCards(), $game_yellow_cards_after);
        
        $roundprediction = new RoundPrediction($participantId, $roundId, 0);
        $this->assertEqual($roundprediction->getCountry()->getId(), $round_country_after);

        $questionprediction = new QuestionPrediction($participantId, $questionId);
        $this->assertEqual($questionprediction->getAnswer(), $question_anwser_after);
        
        $gameprediction->delete();
        $roundprediction->delete();
        $questionprediction->delete();
        
        $game = new Game($gameId);
        $game->delete();
        $question = new Question($questionId);
        $question->delete();
        $round = new Round($roundId);
        $round->delete();
        $participant = new Participant($participantId);
        $participant->delete();
    }    
}
?>