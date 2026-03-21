<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Ranking
{
    private $result = null;
    private $userId = 0;
    private $competitionId = 0;
    
    private static $resultList = null;
    
    public function __construct($userId, $competitionId)
    {
        $this->userId = App::$_DB->escapeString($userId);
        $this->competitionId = App::$_DB->escapeString($competitionId);

        $this->result = App::$_DB->doSQL('SELECT *
                                          FROM `table`
                                          WHERE `Participant_User_user_id` = ' . $this->userId . '
                                          AND `Competition_competition_id` = '. $this->competitionId.' LIMIT 1;');
        $this->result = App::$_DB->getRecord($this->result);
    }

    /**
     * Destructs the city-object.
     */
    public function __destruct()
    {
        App::$_DB->freeQuery($this->result);
    } //__destruct

    public function getUserId()
    {
        return $this->userId;
    }

    public function getPoints()
    {
        return $this->result->table_points;
    }

    public function getPosition()
    {
        return $this->result->table_position;
    }
    
    public function getOldPosition()
    {
        return $this->result->table_old_position;
    }

    public function setPoints($points)
    {
        $this->result->table_points = $points;
    }

    public function setPosition($position)
    {
        $this->result->table_position = $position;
    }
    
    public function setOldPosition($position)
    {
        $this->result->table_old_position = $position;
    }
    
    public function save()
    {
        App::$_DB->doSQL('UPDATE `table` SET
                          `table_points` = '.$this->result->table_points.',
                          `table_position` = '.$this->result->table_position.',
                          `table_old_position` = '.$this->result->table_old_position.'
                          WHERE `Participant_User_user_id` = ' . $this->userId . '
                          AND `Competition_competition_id` = ' . $this->competitionId. ' LIMIT 1;');
    }
    
    public function delete()
    {
        App::$_DB->doSQL('DELETE FROM `table` WHERE `Participant_User_user_id` = ' . $this->userId . '');

        $this->__destruct();
        return true;
    }

    public static function deleteAllByCompetition($competitionId)
    {
        App::$_DB->doSQL('DELETE FROM `table`
                          WHERE `Competition_competition_id` = ' . $competitionId . '');
    }

    public static function deleteAllByUser($userId)
    {
        App::$_DB->doSQL('DELETE FROM `table`
                          WHERE `Participant_User_user_id` = ' . $userId . '');
    }
    
    public static function getAllRankings($competitionId)
    {
       self::$resultList = App::$_DB->doSQL('SELECT * FROM `table`
                                             WHERE `Competition_competition_id` = '.$competitionId.'
                                             ORDER BY `table_position` ASC');
    }

    public static function add($competitionId, $userId, $points, $position, $oldPosition)
    {
        App::$_DB->doSQL('INSERT INTO `table` (Participant_User_user_id, Competition_competition_id, table_points, table_position, table_old_position)
                          VALUES (
                            '.$userId.',
                            '.$competitionId.',
                            '.$points.',
                            '.$position.',
                            '.$oldPosition.')
                          ');
    }

    public static function addOrUpdateUser($competitionId, $userId, $points, $position, $old_position)
    {
        if (Ranking::exists($competitionId, $userId)) 
        {
            $ranking = new Ranking($userId, $competitionId);
            $ranking->setPoints($points);
            $ranking->setPosition($position);
            $ranking->setOldPosition($old_position);
            $ranking->save();
        }
        else
        {
            Ranking::add($competitionId, $userId, $points, $position, $old_position);
        }
    }
    
    public static function deleteAllRankings()
    {
        App::$_DB->doSQL('DELETE FROM `table`');
    }    
    
    public static function nextRanking()
    {
        if (self::$resultList == null)
          return null;

        $record = App::$_DB->getRecord(self::$resultList);
        if ($record == null)
          self::$resultList = null;

        return $record;
    }

    public static function exists($competitionId, $userId)
    {
        $record = App::$_DB->doSQL('SELECT count( * ) AS total
                                    FROM `table`
                                    WHERE `Participant_User_user_id` = '.$userId .'
                                    AND `Competition_competition_id` = '.$competitionId);

        return (boolean)App::$_DB->getRecord($record)->total;
    }

    public static function updateRanking($competitionId)
    {
        App::openClass('Ranking', 'modules/table');
        App::openClass('Section', 'modules/sections');
        App::openClass('Scoring', 'modules/scorings');
        App::openClass('Game', 'modules/games');
        App::openClass('Question', 'modules/questions');
        App::openClass('Round', 'modules/rounds');
        App::openClass('QuestionPrediction', 'modules/predictions');
        App::openClass('RoundPrediction', 'modules/predictions');
        App::openClass('GamePrediction', 'modules/predictions');
        App::openClass('Country', 'modules/countries');
        App::openClass('RoundResult', 'modules/rounds');
        App::openClass('Participant', 'modules/users');
        
        $table = array();
        
        // calculate points for every user
        User::getAllUsers(3);
        $i = 0;
        while (($user = User::nextUser()) != null)
        {
            $participant = new Participant($user->user_id);
            // calculate points for users that are subscribed and have payed
            if ($participant->getSubscribed($competitionId) && $participant->getPayed($competitionId))
            {
                $ranking = new Ranking($user->user_id, $competitionId);
                $table[$i]['user_id'] = $user->user_id;
                $table[$i]['user_name'] = $user->user_firstname . ' ' . $user->user_lastname;
                $table[$i]['old_position'] = (@$ranking->getPosition() ? (int)$ranking->getPosition() : 0);
                $table[$i]['points'] = 0;

                // check every section
                Section::getAllSections($competitionId);
                while (($section = Section::nextSection()) != null)
                {
                    if ($section->Section_Competition_enabled)
                    {
                        switch ($section->section_id)
                        {
                            case SECTION::$_SECTION_RESULTS:
                                Game::getAllGames($competitionId);
                                while (($game = Game::nextGame()) != null)
                                {
                                    $result = $game->game_result;
                                    $gamePrediction = new GamePrediction($participant->getId(), $game->game_id);
                                    
                                    Scoring::getAllScorings($competitionId, $section->section_id);
                                    while (($scoring = Scoring::nextScoring()) != null)
                                    {
                                        if ($scoring->Scoring_Competition_enabled)
                                        {
                                            switch ($scoring->scoring_id)
                                            {
                                                case Scoring::$_SCORING_GAME_RESULT_CORRECT:
                                                    if($gamePrediction->getResult() == $result)
                                                        $table[$i]['points'] += $scoring->Scoring_Competition_points;
                                                    break;
                                                case Scoring::$_SCORING_GAME_WINNER_EQUAL_CORRECT:
                                                    $predictionPieces = explode('-', $gamePrediction->getResult());
                                                    $resultPieces = explode('-', $result);
                                                    
                                                    $predictionState = 1;                        
                                                    if(@$predictionPieces[0] > @$predictionPieces[1])
                                                        $predictionState = 2;
                                                    else if(@$predictionPieces[0] < @$predictionPieces[1])
                                                        $predictionState = 3;
                                                                                                        
                                                    $resultState = 1;
                                                    if(@$resultPieces[0] > @$resultPieces[1])
                                                        $resultState = 2;
                                                    else if(@$resultPieces[0] < @$resultPieces[1])
                                                        $resultState = 3;

                                                    if ($predictionState == $resultState
                                                            && $resultPieces[0] != "empty" && $resultPieces[1] != "empty")
                                                        $table[$i]['points'] += $scoring->Scoring_Competition_points;       
                                                    break;
                                            }
                                        }
                                    }
                                }
                                break;
                            case SECTION::$_SECTION_CARDS:
                                Game::getAllGames($competitionId);
                                while (($game = Game::nextGame()) != null)
                                {
                                    $gamePrediction = new GamePrediction($participant->getId(), $game->game_id);
                                    
                                    Scoring::getAllScorings($competitionId, $section->section_id);
                                    while (($scoring = Scoring::nextScoring()) != null)
                                    {
                                        if ($scoring->Scoring_Competition_enabled)
                                        {
                                            switch ($scoring->scoring_id)
                                            {
                                                case Scoring::$_SCORING_CARDS_CORRECT:
                                                    if ($gamePrediction->getYellowCards() == $game->game_yellow_cards 
                                                        && $gamePrediction->getRedCards() == $game->game_red_cards)
                                                        $table[$i]['points'] += $scoring->Scoring_Competition_points;
                                                        break;
                                                case Scoring::$_SCORING_YELLOW_OR_RED_CARDS_CORRECT:
                                                    if (!($gamePrediction->getYellowCards() == $game->game_yellow_cards 
                                                        && $gamePrediction->getRedCards() == $game->game_red_cards))
                                                    {
                                                        if ($gamePrediction->getYellowCards() == $game->game_yellow_cards 
                                                            || $gamePrediction->getRedCards() == $game->game_red_cards)
                                                            $table[$i]['points'] += $scoring->Scoring_Competition_points;
                                                    }
                                                    break;
                                            }
                                        }
                                    }
                                }
                                break;
                            case SECTION::$_SECTION_KNOCK_OUT_FASE:
                                Round::getAllRounds($competitionId);
                                while (($round = Round::nextRound()) != null)
                                {
                                    $scoring = Scoring::getScoringByRoundId($round->round_id);
                                    if ($scoring->Scoring_Competition_enabled)
                                    {
                                        $predictedCountries = array();
                                        $roundPredictions = RoundPrediction::getAllPredictionsByRound($participant->getId(), $round->round_id);
                                        while (($prediction = RoundPrediction::nextPrediction()) != null)
                                        {
                                            $country = new Country($prediction->Country_country_id);
                                            array_push($predictedCountries, $country->getName());
                                        }

                                        $roundResults = RoundResult::getAllRoundResults($round->round_id);
                                        while (($result = RoundResult::nextRoundResult()) != null)
                                        {
                                            if ($result->Country_country_id != 0)
                                            {
                                                $country = new Country($result->Country_country_id);
                                                if (in_array($country->getName(), $predictedCountries))
                                                    $table[$i]['points'] += $scoring->Scoring_Competition_points;
                                            }
                                        }
                                    }
                                }
                                break;
                            case SECTION::$_SECTION_QUESTIONS:
                                Question::getAllQuestions($competitionId);
                                while (($question = Question::nextQuestion()) != null)
                                {
                                    $questionPrediction = new QuestionPrediction($participant->getId(), $question->question_id);
                                    
                                    Scoring::getAllScorings($competitionId, $section->section_id);
                                    while (($scoring = Scoring::nextScoring()) != null)
                                    {
                                        if ($scoring->Scoring_Competition_enabled)
                                        {
                                            switch ($scoring->scoring_id)
                                            {
                                                case Scoring::$_SCORING_QUESTION_CORRECT:    
                                                    if (preg_match('/'.$questionPrediction->getAnswer().'/', $question->question_anwser))
                                                        $table[$i]['points'] += $scoring->Scoring_Competition_points;
                                                    break;
                                            }
                                        }
                                    }
                                }
                                break;
                        }
                    }
                }
                $i++;
            }
            else
            {
                if (Ranking::exists($competitionId, $user->user_id))
                {
                    $ranking = new Ranking($user->user_id, $competitionId);
                    $ranking->delete();
                }
            }
        }            

        // determine positions
        usort($table, 'compare');
        
        foreach ($table as $key => $ranking)
        {
            Ranking::addOrUpdateUser($competitionId, $ranking['user_id'], $ranking['points'], $key+1, $ranking['old_position']);
        }
        
        return true;
    } //doEditTable
}

function compare($user1, $user2)
{
    if ($user1['points'] < $user2['points'])
        return 1;
    else if ($user1['points'] > $user2['points'])
        return  -1;
    else
        return strcasecmp($user1['user_name'], $user2['user_name']);
}

?>
