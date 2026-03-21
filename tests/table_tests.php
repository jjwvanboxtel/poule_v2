<?php
require_once('./simpletest/autorun.php');
require_once('./mock/appMock.php');
require_once('../modules/table/ranking.class.php');
require_once('../modules/competitions/competition.class.php');
require_once('../modules/poules/poule.class.php');
require_once('../modules/countries/country.class.php');
require_once('../modules/cities/city.class.php');
require_once('../modules/rounds/round.class.php');
require_once('../modules/players/player.class.php');
require_once('../modules/referees/referee.class.php');
require_once('../modules/questions/question.class.php');
require_once('../modules/games/game.class.php');
require_once('../modules/rounds/roundresult.class.php');
require_once('../modules/usergroups/usergroup.class.php');

class TestOfTable extends UnitTestCase {

    private $tag = 'TestOfTable';
    
    private $competitionId = 0;

    private $participants = array();
    private $countrys = array();
    private $poules = array();
    private $citys = array();
    private $games = array();
    private $rounds = array();
    private $sections = array();
    private $scorings = array();
    private $referees = array();
    private $players = array();
    private $questions = array();
    private $expectations = array();
    
    private $step_count = 0;
    
    function setUp() {
        App::clearAll();
        
        self::setCompetition();
        self::setCountries();
        self::setPoules();
        self::setCities();
        self::setGames();
        self::setRounds();
        self::setQuestions();
        self::setPlayers();
        self::setReferees();
        self::setScorings();
        self::setSections();
        self::setParticipants();
        self::setPredictions();        
        self::setExpectations();
        
        $this->step_count = 0;
    }

    function tearDown()
    {
        App::clearAll();
    }
    
    function testRanking()
    {
        self::simulateCompetition($this->expectations[0]);
    }
    
    function testRankingAllSectionsDisabled()
    {
        foreach ($this->sections as $section)
        {
            $section->setEnabled($this->competitionId, 0);
            $section->save();
        }
        
        self::simulateCompetition($this->expectations[1]);        
    }

    function testRankingAllScoringsDisabled()
    {
        foreach ($this->scorings as $scoring)
        {
            $scoring->setEnabled($this->competitionId, 0);
            $scoring->save();
        }

        self::simulateCompetition($this->expectations[1]);
    }

    function setCompetition()
    {
        $header['name'] = 'headerCompetition1.png';        
        $this->competitionId = Competition::add('testCompetition1', 'descriptionCompetition1', $header, time(), 5, 50, 25, 20);
    }
    
    function setCountries()
    {
        $this->countries = array();
        $image['name'] = 'testCountry.png';
        array_push($this->countries, new Country(Country::add('testCountry1', $image, $this->competitionId)));
        array_push($this->countries, new Country(Country::add('testCountry2', $image, $this->competitionId)));
        array_push($this->countries, new Country(Country::add('testCountry3', $image, $this->competitionId)));
        array_push($this->countries, new Country(Country::add('testCountry4', $image, $this->competitionId)));
    }
    
    function setPoules()
    {
        $this->poules = array();
        array_push($this->poules, new Poule(Poule::add('A', $this->competitionId)));
    }
    
    function setCities()
    {
        $this->cities = array();
        array_push($this->cities, new City(City::add('testCity1', $this->competitionId)));
    }
    
    function setGames()
    {
        $this->games = array();
        array_push($this->games, new Game(Game::add($this->competitionId, '25-05-2014', 'empty-empty', 'empty', 'empty', $this->cities[0]->getId(), $this->countries[0]->getId(), $this->countries[1]->getId(), $this->poules[0]->getId())));
        array_push($this->games, new Game(Game::add($this->competitionId, '26-05-2014', 'empty-empty', 'empty', 'empty', $this->cities[0]->getId(), $this->countries[2]->getId(), $this->countries[3]->getId(), $this->poules[0]->getId())));
        array_push($this->games, new Game(Game::add($this->competitionId, '27-05-2014', 'empty-empty', 'empty', 'empty', $this->cities[0]->getId(), $this->countries[1]->getId(), $this->countries[2]->getId(), $this->poules[0]->getId())));
    }
    
    function setRounds()
    {
        $this->rounds = array();
        array_push($this->rounds, new Round(Round::add($this->competitionId, 'Final', 2)));
        array_push($this->rounds, new Round(Round::add($this->competitionId, 'Winner', 1)));
    }
 
    function setQuestions()
    {
        $this->questions = array();
        $i=0;
        foreach (Question::$_TYPES as $key => $value)
        {
            array_push($this->questions, new Question(Question::add($this->competitionId, '1', 'testQuestion'.$i, $key)));
            $i++;
        }

        foreach (Question::$_TYPES as $key => $value)
        {
            array_push($this->questions, new Question(Question::add($this->competitionId, '2', 'testQuestion'.$i, $key)));
            $i++;
        }
    }
    
    function setPlayers()
    {
        $this->players = array();
        array_push($this->players, new Player(Player::add($this->competitionId, 'testPlayer1', $this->countries[0]->getId())));  
        array_push($this->players, new Player(Player::add($this->competitionId, 'testPlayer2', $this->countries[0]->getId())));  
        array_push($this->players, new Player(Player::add($this->competitionId, 'testPlayer3', $this->countries[0]->getId())));  
    }
    
    function setReferees()
    {
        $this->referees = array();
        array_push($this->referees, new Referee(Referee::add($this->competitionId, 'testReferee1')));
        array_push($this->referees, new Referee(Referee::add($this->competitionId, 'testReferee2')));
        array_push($this->referees, new Referee(Referee::add($this->competitionId, 'testReferee3')));
    }

    function setScorings()
    {
        $this->scorings = array();
        Scoring::getAllScorings($this->competitionId);
        while (($scoring = Scoring::nextScoring()) != null)
        {
            array_push($this->scorings, new Scoring($scoring->scoring_id));
        }
        
        self::setScoring($this->scorings[0], 5, 1);
        self::setScoring($this->scorings[1], 3, 1);
        self::setScoring($this->scorings[2], 3, 1);
        self::setScoring($this->scorings[3], 1, 1);
        self::setScoring($this->scorings[4], 5, 1);
        self::setScoring($this->scorings[5], 20, 1);
        self::setScoring($this->scorings[6], 25, 1);
    }
 
    function setScoring($scoring, $points, $enabled)
    {
        $scoring->setPoints($this->competitionId, $points);
        $scoring->setEnabled($this->competitionId, $enabled);
        $scoring->save();    
    }
    
    function setSections()
    {
        $this->sections = array();
        Section::getAllSections($this->competitionId);
        while (($section = Section::nextSection()) != null)
        {
            array_push($this->sections, new Section($section->section_id));
        }

        self::setSection($this->sections[0], 1);
        self::setSection($this->sections[1], 1);
        self::setSection($this->sections[2], 1);
        self::setSection($this->sections[3], 1);
    }
    
    function setSection($section, $enabled)
    {
        $section->setEnabled($this->competitionId, $enabled);
        $section->save();    
    }
    
    function setParticipants()
    {
        $this->participants = array();
        array_push($this->participants, new Participant(Participant::addp(1, 'poule@test.nl', 'password01', 'testPerson', 'lastName', '1111111111', 3,
                                            '1111AA', 'testStraat', 'testPlaats', '1', 'a', '1111111111')));
        array_push($this->participants, new Participant(Participant::addp(1, 'poule2@test.nl', 'password02', 'testPerson2', 'lastName2', '2222222222', 3,
                                            '2222BB', 'testStraat2', 'testPlaats2', '2', 'b', '2222222222')));
        array_push($this->participants, new Participant(Participant::addp(1, 'poule3@test.nl', 'password03', 'testPerson3', 'lastName3', '3333333333', 3,
                                            '3333CC', 'testStraat3', 'testPlaats3', '3', 'c', '3333333333')));
        array_push($this->participants, new Participant(Participant::addp(1, 'poule4@test.nl', 'password04', 'testPerson4', 'lastName4', '4444444444', 3,
                                            '4444DD', 'testStraat4', 'testPlaats4', '4', 'd', '4444444444')));
        array_push($this->participants, new Participant(Participant::addp(1, 'poule5@test.nl', 'password05', 'testPerson5', 'lastName5', '5555555555', 3,
                                            '5555EE', 'testStraat5', 'testPlaats5', '5', 'e', '5555555555')));
        array_push($this->participants, new Participant(Participant::addp(1, 'poule6@test.nl', 'password06', 'testPerson6', 'lastName6', '6666666666', 3,
                                            '6666FF', 'testStraat6', 'testPlaats6', '6', 'f', '6666666666')));
        
        self::setParticipant($this->participants[0], 1, 1);
        self::setParticipant($this->participants[1], 1, 1);
        self::setParticipant($this->participants[2], 1, 1);
        self::setParticipant($this->participants[3], 1, 0);
        self::setParticipant($this->participants[4], 0, 1);
    }

    function setParticipant($participant, $payed, $subscribed)
    {
        $participant->setPayed($this->competitionId, $payed);
        $participant->setSubscribed($this->competitionId, $subscribed);
        $participant->save();
    }
    
    function setPredictions()
    {
        // Participant 1
        self::setPredictionsForParticipant($this->participants[0]->getId(), array(
            array(
                array('result' => '2-1', 'yellow_cards' => '2', 'red_cards' => '2'),
                array('result' => '1-2', 'yellow_cards' => '2', 'red_cards' => '2'),
                array('result' => '1-1', 'yellow_cards' => '2', 'red_cards' => '2')
            ),
            array(
                array('country' => $this->countries[3]->getId()),
                array('country' => $this->countries[2]->getId()),
                array('country' => $this->countries[3]->getId())
            ),
            array(
                array('answer' => '0'),
                array('answer' => $this->countries[1]->getName()),   
                array('answer' => $this->referees[1]->getName()),
                array('answer' => $this->players[1]->getName()),
                array('answer' => $this->players[0]->getName()),
                array('answer' => '4'),
                array('answer' => '3'),
                array('answer' => $this->countries[2]->getName()),   
                array('answer' => $this->referees[2]->getName()),
                array('answer' => $this->players[2]->getName()),
                array('answer' => $this->players[2]->getName()),
                array('answer' => '6')
            )
        ));

        // Participant 2
        self::setPredictionsForParticipant($this->participants[1]->getId(), array(
            array(
                array('result' => '1-1', 'yellow_cards' => '4', 'red_cards' => '0'),
                array('result' => '2-1', 'yellow_cards' => '4', 'red_cards' => '0'),
                array('result' => '1-2', 'yellow_cards' => '4', 'red_cards' => '0')
            ),
            array(
                array('country' => $this->countries[0]->getId()),
                array('country' => $this->countries[1]->getId()),
                array('country' => $this->countries[0]->getId())
            ),
            array(
                array('answer' => '1'),
                array('answer' => $this->countries[0]->getName()),   
                array('answer' => $this->referees[0]->getName()),
                array('answer' => $this->players[0]->getName()),
                array('answer' => $this->players[1]->getName()),
                array('answer' => '5'),
                array('answer' => '1'),
                array('answer' => $this->countries[0]->getName()),   
                array('answer' => $this->referees[0]->getName()),
                array('answer' => $this->players[0]->getName()),
                array('answer' => $this->players[0]->getName()),
                array('answer' => '5')
            )
        ));

        // Participant 3
        self::setPredictionsForParticipant($this->participants[2]->getId(), array(
            array(
                array('result' => '2-2', 'yellow_cards' => '4', 'red_cards' => '1'),
                array('result' => '3-2', 'yellow_cards' => '4', 'red_cards' => '1'),
                array('result' => '2-3', 'yellow_cards' => '4', 'red_cards' => '1')
            ),
            array(
                array('country' => $this->countries[0]->getId()),
                array('country' => $this->countries[3]->getId()),
                array('country' => $this->countries[0]->getId())
            ),
            array(
                array('answer' => '1'),
                array('answer' => $this->countries[0]->getName()),   
                array('answer' => $this->referees[0]->getName()),
                array('answer' => $this->players[1]->getName()),
                array('answer' => $this->players[0]->getName()),
                array('answer' => '4'),
                array('answer' => '0'),
                array('answer' => $this->countries[1]->getName()),   
                array('answer' => $this->referees[1]->getName()),
                array('answer' => $this->players[1]->getName()),
                array('answer' => $this->players[1]->getName()),
                array('answer' => '4')
            )
        ));
    }
    
    function setPredictionsForParticipant($participantId, $predictions)
    {
        self::setGamePrediction($participantId, $this->games[0]->getId(), $predictions[0][0]['result'], $predictions[0][0]['yellow_cards'], $predictions[0][0]['red_cards']);
        self::setGamePrediction($participantId, $this->games[1]->getId(), $predictions[0][1]['result'], $predictions[0][1]['yellow_cards'], $predictions[0][1]['red_cards']);
        self::setGamePrediction($participantId, $this->games[2]->getId(), $predictions[0][2]['result'], $predictions[0][2]['yellow_cards'], $predictions[0][2]['red_cards']);        

        self::setRoundPrediction($participantId, $this->rounds[0]->getId(), 0, $predictions[1][0]['country']);
        self::setRoundPrediction($participantId, $this->rounds[0]->getId(), 1, $predictions[1][1]['country']);
        self::setRoundPrediction($participantId, $this->rounds[1]->getId(), 0, $predictions[1][2]['country']);

        self::setQuestionPrediction($participantId, $this->questions[0]->getId(), $predictions[2][0]['answer']);
        self::setQuestionPrediction($participantId, $this->questions[1]->getId(), $predictions[2][1]['answer']);
        self::setQuestionPrediction($participantId, $this->questions[2]->getId(), $predictions[2][2]['answer']);
        self::setQuestionPrediction($participantId, $this->questions[3]->getId(), $predictions[2][3]['answer']);
        self::setQuestionPrediction($participantId, $this->questions[4]->getId(), $predictions[2][4]['answer']);
        self::setQuestionPrediction($participantId, $this->questions[5]->getId(), $predictions[2][5]['answer']);
        self::setQuestionPrediction($participantId, $this->questions[6]->getId(), $predictions[2][6]['answer']);
        self::setQuestionPrediction($participantId, $this->questions[7]->getId(), $predictions[2][7]['answer']);
        self::setQuestionPrediction($participantId, $this->questions[8]->getId(), $predictions[2][8]['answer']);
        self::setQuestionPrediction($participantId, $this->questions[9]->getId(), $predictions[2][9]['answer']);
        self::setQuestionPrediction($participantId, $this->questions[10]->getId(), $predictions[2][10]['answer']);
        self::setQuestionPrediction($participantId, $this->questions[11]->getId(), $predictions[2][11]['answer']);
    }
    
    function setGamePrediction($participantId, $gameId, $result, $yellow_cards, $red_cards)
    {
        $gamePrediction = new GamePrediction($participantId, $gameId);
        $gamePrediction->setResult($result);
        $gamePrediction->setYellowCards($yellow_cards);
        $gamePrediction->setRedCards($red_cards);
        $gamePrediction->save();
    }

    function setRoundPrediction($participantId, $roundId, $predictionId, $country)
    {
        $roundPrediction = new RoundPrediction($participantId, $roundId, $predictionId);
        $roundPrediction->setCountry($country);
        $roundPrediction->save();
    }

    function setQuestionPrediction($participantId, $questionId, $answer)
    {
        $questionPrediction = new QuestionPrediction($participantId, $questionId);
        $questionPrediction->setAnswer($answer);
        $questionPrediction->save();        
    }
    
    function setExpectations()
    {
        $this->expectations[0] = array(
            array( 
                array('id' => $this->participants[0]->getId(), 'points' => '0', 'old_position' => '0'),
                array('id' => $this->participants[1]->getId(), 'points' => '0', 'old_position' => '0'),
                array('id' => $this->participants[2]->getId(), 'points' => '0', 'old_position' => '0')
            ),
            array( 
                array('id' => $this->participants[1]->getId(), 'points' => '11', 'old_position' => '2'),
                array('id' => $this->participants[2]->getId(), 'points' => '4', 'old_position' => '3'),
                array('id' => $this->participants[0]->getId(), 'points' => '0', 'old_position' => '1')
            ),
            array( 
                array('id' => $this->participants[1]->getId(), 'points' => '22', 'old_position' => '1'),
                array('id' => $this->participants[2]->getId(), 'points' => '8', 'old_position' => '2'),
                array('id' => $this->participants[0]->getId(), 'points' => '0', 'old_position' => '3')
            ),
            array( 
                array('id' => $this->participants[1]->getId(), 'points' => '33', 'old_position' => '1'),
                array('id' => $this->participants[2]->getId(), 'points' => '12', 'old_position' => '2'),
                array('id' => $this->participants[0]->getId(), 'points' => '0', 'old_position' => '3')
            ),
            array( 
                array('id' => $this->participants[1]->getId(), 'points' => '73', 'old_position' => '1'),
                array('id' => $this->participants[2]->getId(), 'points' => '32', 'old_position' => '2'),
                array('id' => $this->participants[0]->getId(), 'points' => '0', 'old_position' => '3')
            ),
            array( 
                array('id' => $this->participants[1]->getId(), 'points' => '98', 'old_position' => '1'),
                array('id' => $this->participants[2]->getId(), 'points' => '57', 'old_position' => '2'),
                array('id' => $this->participants[0]->getId(), 'points' => '0', 'old_position' => '3')
            ),
            array( 
                array('id' => $this->participants[1]->getId(), 'points' => '103', 'old_position' => '1'),
                array('id' => $this->participants[2]->getId(), 'points' => '62', 'old_position' => '2'),
                array('id' => $this->participants[0]->getId(), 'points' => '0', 'old_position' => '3')
            ),
            array( 
                array('id' => $this->participants[1]->getId(), 'points' => '108', 'old_position' => '1'),
                array('id' => $this->participants[2]->getId(), 'points' => '67', 'old_position' => '2'),
                array('id' => $this->participants[0]->getId(), 'points' => '0', 'old_position' => '3')
            ),
            array( 
                array('id' => $this->participants[1]->getId(), 'points' => '113', 'old_position' => '1'),
                array('id' => $this->participants[2]->getId(), 'points' => '72', 'old_position' => '2'),
                array('id' => $this->participants[0]->getId(), 'points' => '0', 'old_position' => '3')
            ),
            array( 
                array('id' => $this->participants[1]->getId(), 'points' => '118', 'old_position' => '1'),
                array('id' => $this->participants[2]->getId(), 'points' => '72', 'old_position' => '2'),
                array('id' => $this->participants[0]->getId(), 'points' => '0', 'old_position' => '3')
            ),
            array( 
                array('id' => $this->participants[1]->getId(), 'points' => '123', 'old_position' => '1'),
                array('id' => $this->participants[2]->getId(), 'points' => '72', 'old_position' => '2'),
                array('id' => $this->participants[0]->getId(), 'points' => '0', 'old_position' => '3')
            ),
            array( 
                array('id' => $this->participants[1]->getId(), 'points' => '128', 'old_position' => '1'),
                array('id' => $this->participants[2]->getId(), 'points' => '72', 'old_position' => '2'),
                array('id' => $this->participants[0]->getId(), 'points' => '0', 'old_position' => '3')
            ),
            array( 
                array('id' => $this->participants[1]->getId(), 'points' => '133', 'old_position' => '1'),
                array('id' => $this->participants[2]->getId(), 'points' => '77', 'old_position' => '2'),
                array('id' => $this->participants[0]->getId(), 'points' => '0', 'old_position' => '3')
            ),
            array( 
                array('id' => $this->participants[1]->getId(), 'points' => '138', 'old_position' => '1'),
                array('id' => $this->participants[2]->getId(), 'points' => '82', 'old_position' => '2'),
                array('id' => $this->participants[0]->getId(), 'points' => '0', 'old_position' => '3')
            ),
            array( 
                array('id' => $this->participants[1]->getId(), 'points' => '143', 'old_position' => '1'),
                array('id' => $this->participants[2]->getId(), 'points' => '87', 'old_position' => '2'),
                array('id' => $this->participants[0]->getId(), 'points' => '0', 'old_position' => '3')
            ),
            array( 
                array('id' => $this->participants[1]->getId(), 'points' => '148', 'old_position' => '1'),
                array('id' => $this->participants[2]->getId(), 'points' => '92', 'old_position' => '2'),
                array('id' => $this->participants[0]->getId(), 'points' => '0', 'old_position' => '3')
            ),
            array( 
                array('id' => $this->participants[1]->getId(), 'points' => '153', 'old_position' => '1'),
                array('id' => $this->participants[2]->getId(), 'points' => '97', 'old_position' => '2'),
                array('id' => $this->participants[0]->getId(), 'points' => '0', 'old_position' => '3')
            ),
            array( 
                array('id' => $this->participants[1]->getId(), 'points' => '158', 'old_position' => '1'),
                array('id' => $this->participants[2]->getId(), 'points' => '102', 'old_position' => '2'),
                array('id' => $this->participants[0]->getId(), 'points' => '0', 'old_position' => '3')
            )
        );
        
        $this->expectations[1] = array(
            array( 
                array('id' => $this->participants[0]->getId(), 'points' => '0', 'old_position' => '0'),
                array('id' => $this->participants[1]->getId(), 'points' => '0', 'old_position' => '0'),
                array('id' => $this->participants[2]->getId(), 'points' => '0', 'old_position' => '0')
            ),
            array( 
                array('id' => $this->participants[0]->getId(), 'points' => '0', 'old_position' => '1'),
                array('id' => $this->participants[1]->getId(), 'points' => '0', 'old_position' => '2'),
                array('id' => $this->participants[2]->getId(), 'points' => '0', 'old_position' => '3')
            ),
            array( 
                array('id' => $this->participants[0]->getId(), 'points' => '0', 'old_position' => '1'),
                array('id' => $this->participants[1]->getId(), 'points' => '0', 'old_position' => '2'),
                array('id' => $this->participants[2]->getId(), 'points' => '0', 'old_position' => '3')
            ),
            array( 
                array('id' => $this->participants[0]->getId(), 'points' => '0', 'old_position' => '1'),
                array('id' => $this->participants[1]->getId(), 'points' => '0', 'old_position' => '2'),
                array('id' => $this->participants[2]->getId(), 'points' => '0', 'old_position' => '3')
            ),
            array( 
                array('id' => $this->participants[0]->getId(), 'points' => '0', 'old_position' => '1'),
                array('id' => $this->participants[1]->getId(), 'points' => '0', 'old_position' => '2'),
                array('id' => $this->participants[2]->getId(), 'points' => '0', 'old_position' => '3')
            ),
            array( 
                array('id' => $this->participants[0]->getId(), 'points' => '0', 'old_position' => '1'),
                array('id' => $this->participants[1]->getId(), 'points' => '0', 'old_position' => '2'),
                array('id' => $this->participants[2]->getId(), 'points' => '0', 'old_position' => '3')
            ),
            array( 
                array('id' => $this->participants[0]->getId(), 'points' => '0', 'old_position' => '1'),
                array('id' => $this->participants[1]->getId(), 'points' => '0', 'old_position' => '2'),
                array('id' => $this->participants[2]->getId(), 'points' => '0', 'old_position' => '3')
            ),
            array( 
                array('id' => $this->participants[0]->getId(), 'points' => '0', 'old_position' => '1'),
                array('id' => $this->participants[1]->getId(), 'points' => '0', 'old_position' => '2'),
                array('id' => $this->participants[2]->getId(), 'points' => '0', 'old_position' => '3')
            ),
            array( 
                array('id' => $this->participants[0]->getId(), 'points' => '0', 'old_position' => '1'),
                array('id' => $this->participants[1]->getId(), 'points' => '0', 'old_position' => '2'),
                array('id' => $this->participants[2]->getId(), 'points' => '0', 'old_position' => '3')
            ),
            array( 
                array('id' => $this->participants[0]->getId(), 'points' => '0', 'old_position' => '1'),
                array('id' => $this->participants[1]->getId(), 'points' => '0', 'old_position' => '2'),
                array('id' => $this->participants[2]->getId(), 'points' => '0', 'old_position' => '3')
            ),
            array( 
                array('id' => $this->participants[0]->getId(), 'points' => '0', 'old_position' => '1'),
                array('id' => $this->participants[1]->getId(), 'points' => '0', 'old_position' => '2'),
                array('id' => $this->participants[2]->getId(), 'points' => '0', 'old_position' => '3')
            ),
            array( 
                array('id' => $this->participants[0]->getId(), 'points' => '0', 'old_position' => '1'),
                array('id' => $this->participants[1]->getId(), 'points' => '0', 'old_position' => '2'),
                array('id' => $this->participants[2]->getId(), 'points' => '0', 'old_position' => '3')
            ),
            array( 
                array('id' => $this->participants[0]->getId(), 'points' => '0', 'old_position' => '1'),
                array('id' => $this->participants[1]->getId(), 'points' => '0', 'old_position' => '2'),
                array('id' => $this->participants[2]->getId(), 'points' => '0', 'old_position' => '3')
            ),
            array( 
                array('id' => $this->participants[0]->getId(), 'points' => '0', 'old_position' => '1'),
                array('id' => $this->participants[1]->getId(), 'points' => '0', 'old_position' => '2'),
                array('id' => $this->participants[2]->getId(), 'points' => '0', 'old_position' => '3')
            ),
            array( 
                array('id' => $this->participants[0]->getId(), 'points' => '0', 'old_position' => '1'),
                array('id' => $this->participants[1]->getId(), 'points' => '0', 'old_position' => '2'),
                array('id' => $this->participants[2]->getId(), 'points' => '0', 'old_position' => '3')
            ),
            array( 
                array('id' => $this->participants[0]->getId(), 'points' => '0', 'old_position' => '1'),
                array('id' => $this->participants[1]->getId(), 'points' => '0', 'old_position' => '2'),
                array('id' => $this->participants[2]->getId(), 'points' => '0', 'old_position' => '3')
            ),
            array( 
                array('id' => $this->participants[0]->getId(), 'points' => '0', 'old_position' => '1'),
                array('id' => $this->participants[1]->getId(), 'points' => '0', 'old_position' => '2'),
                array('id' => $this->participants[2]->getId(), 'points' => '0', 'old_position' => '3')
            ),
            array( 
                array('id' => $this->participants[0]->getId(), 'points' => '0', 'old_position' => '1'),
                array('id' => $this->participants[1]->getId(), 'points' => '0', 'old_position' => '2'),
                array('id' => $this->participants[2]->getId(), 'points' => '0', 'old_position' => '3')
            )
        );
    }
    
    function simulateCompetition($expectations) 
    {   
        self::checkRanking($this->competitionId, $expectations[0]);
            
        // Test games
        // Everything correct: 5 + 3 + 3 = 11 points
        // Partly correct: 3 + 1 = 4 points
        
        $this->games[0]->setResult('1-1');  
        $this->games[0]->setYellowCards('4');
        $this->games[0]->setRedCards('0');
        $this->games[0]->save();
        
        self::checkRanking($this->competitionId, $expectations[1]);

        $this->games[1]->setResult('2-1');  
        $this->games[1]->setYellowCards('4');
        $this->games[1]->setRedCards('0');
        $this->games[1]->save();
        
        self::checkRanking($this->competitionId, $expectations[2]);

        $this->games[2]->setResult('1-2');  
        $this->games[2]->setYellowCards('4');
        $this->games[2]->setRedCards('0');
        $this->games[2]->save();
        
        self::checkRanking($this->competitionId, $expectations[3]);

        // Test round 1
        // Correct: 20 points
        
        $this->rounds[0]->setCountries( array($this->countries[1]->getId(), $this->countries[0]->getId()) );
        $this->rounds[0]->save();
        
        self::checkRanking($this->competitionId, $expectations[4]);

        // Test round 2
        // Correct: 25 points

        $this->rounds[1]->setCountries( array($this->countries[0]->getId()) );
        $this->rounds[1]->save();
        
        self::checkRanking($this->competitionId, $expectations[5]);

        // Test questions
        // Correct: 5 points
        
        $this->questions[0]->setAnwser(array('1'));
        $this->questions[0]->save();
        
        self::checkRanking($this->competitionId, $expectations[6]);

        $this->questions[1]->setAnwser(array($this->countries[0]->getName()));
        $this->questions[1]->save();
        
        self::checkRanking($this->competitionId, $expectations[7]);

        $this->questions[2]->setAnwser(array($this->referees[0]->getName()));
        $this->questions[2]->save();
        
        self::checkRanking($this->competitionId, $expectations[8]);

        $this->questions[3]->setAnwser(array($this->players[0]->getName()));
        $this->questions[3]->save();
        
        self::checkRanking($this->competitionId, $expectations[9]);

        $this->questions[4]->setAnwser(array($this->players[1]->getName()));
        $this->questions[4]->save();

        self::checkRanking($this->competitionId, $expectations[10]);

        $this->questions[5]->setAnwser(array('5'));
        $this->questions[5]->save();

        self::checkRanking($this->competitionId, $expectations[11]);    
        
        $this->questions[6]->setAnwser(array('0', '1'));
        $this->questions[6]->save();

        self::checkRanking($this->competitionId, $expectations[12]);    
        
        $this->questions[7]->setAnwser(array($this->countries[0]->getName(), $this->countries[1]->getName()));
        $this->questions[7]->save();

        self::checkRanking($this->competitionId, $expectations[13]);
        
        $this->questions[8]->setAnwser(array($this->referees[0]->getName(), $this->referees[1]->getName()));
        $this->questions[8]->save();

        self::checkRanking($this->competitionId, $expectations[14]);
        
        $this->questions[9]->setAnwser(array($this->players[0]->getName(), $this->players[1]->getName()));
        $this->questions[9]->save();

        self::checkRanking($this->competitionId, $expectations[15]);
        
        $this->questions[10]->setAnwser(array($this->players[0]->getName(), $this->players[1]->getName()));
        $this->questions[10]->save();

        self::checkRanking($this->competitionId, $expectations[16]);
        
        $this->questions[11]->setAnwser(array('4', '5'));
        $this->questions[11]->save();

        self::checkRanking($this->competitionId, $expectations[17]);
    }        
    
    function checkRanking($competitionId, $participants)
    {
        App::log($this->tag, 'step ' . $this->step_count++);
        
        Ranking::updateRanking($competitionId);

        Ranking::getAllRankings($competitionId);
        $c = 0;
        while (($ranking = Ranking::nextRanking()) != null)
        {
            App::log($this->tag, 'user_id='.$ranking->Participant_User_user_id.', position='.$ranking->table_position.', 
                old_position='.$ranking->table_old_position.', points='.$ranking->table_points);
                
            // Check table order
            $this->assertTrue(($ranking->Participant_User_user_id == $participants[$c]['id']));
            $this->assertEqual($ranking->table_position, ($c+1));                
            
            // Check points
            $this->assertEqual($ranking->table_points, $participants[$c]['points']);        
            
            // Check old position
            $this->assertEqual($ranking->table_old_position, $participants[$c]['old_position']);        
                
            $c++;
        }
        
        // Check that only participants that have payed and have subscribed are in the ranking
        $this->assertEqual($c, count($participants));
    }
}
?>