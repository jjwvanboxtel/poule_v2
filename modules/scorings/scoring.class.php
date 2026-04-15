<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Scoring
{
    private $result = null;
    private $competitionList = array();
    private $id = 0;
    
    private static $resultList = null;
    
    public static $_SCORING_GAME_RESULT_CORRECT = 1;
    public static $_SCORING_GAME_WINNER_EQUAL_CORRECT = 2;
    public static $_SCORING_CARDS_CORRECT = 3;
    public static $_SCORING_YELLOW_OR_RED_CARDS_CORRECT = 4;
    public static $_SCORING_QUESTION_CORRECT = 5;
    
    public function __construct($id)
    {
        $this->id = (int)$id;
        $this->result = App::$_DB->doQuery('SELECT * FROM `scoring` WHERE `scoring_id` = ? LIMIT 1', 'i', $this->id);
        $this->result = App::$_DB->getRecord($this->result);
        
        $resultList = App::$_DB->doQuery('SELECT * FROM `scoring_competition` WHERE `Scoring_scoring_id` = ?', 'i', $this->id);
                          
        while (($competition = App::$_DB->getRecord($resultList)) != null)
        {
            $this->competitionList[$competition->Competition_competition_id]['enabled'] = $competition->Scoring_Competition_enabled;
            $this->competitionList[$competition->Competition_competition_id]['points'] = $competition->Scoring_Competition_points;
            $this->competitionList[$competition->Competition_competition_id]['round_id'] = $competition->Round_round_id;
        }
    }

    /**
     * Destructs the scoring-object.
     */
    public function __destruct()
    {
        App::$_DB->freeQuery($this->result);
    } //__destruct

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->result->scoring_name;
    }
    
    public function getEnabled($competitionId)
    {
        return $this->competitionList[$competitionId]['enabled'];
    }

    public function getPoints($competitionId)
    {
        return $this->competitionList[$competitionId]['points'];
    }

    public function getSection()
    {
        return new Section($this->result->Section_section_id);
    }
    
    public function setName($name)
    {
        $this->result->scoring_name = $name;
    }
    
    public function setEnabled($competitionId, $enabled)
    {
        $this->competitionList[$competitionId]['enabled'] = $enabled;
    }   

    public function setPoints($competitionId, $points)
    {
        $this->competitionList[$competitionId]['points'] = $points;
    }
    
    public function save()
    {
        App::$_DB->doQuery('UPDATE `scoring` SET `scoring_name` = ?, `Section_section_id` = ? WHERE `scoring_id` = ? LIMIT 1', 'sii', $this->result->scoring_name, (int)$this->result->Section_section_id, $this->id);
                         
        foreach ($this->competitionList as $competitionId => $scoring)
        {
            App::$_DB->doQuery('UPDATE `scoring_competition` SET `Scoring_Competition_enabled` = ?, `Scoring_Competition_points` = ? WHERE `Scoring_scoring_id` = ? AND `Competition_competition_id` = ? LIMIT 1', 'iiii', (int)$scoring['enabled'], (int)$scoring['points'], $this->id, (int)$competitionId);
        }
    }

    public static function addScoringByRound($competitionId, $roundName, $roundId)
    {
        App::$_DB->doQuery('INSERT INTO `scoring` (scoring_name, Section_section_id, Round_round_id, Competition_competition_id) VALUES (?, ?, ?, ?)', 'siii', $roundName, 4, (int)$roundId, (int)$competitionId);
                          
        $scoringId = App::$_DB->getLastId();
        
        App::$_DB->doQuery('INSERT INTO `scoring_competition` (Scoring_scoring_id, Competition_competition_id, Scoring_Competition_enabled, Scoring_Competition_points, Round_round_id) VALUES (?, ?, ?, ?, ?)', 'iiiii', (int)$scoringId, (int)$competitionId, 0, 0, (int)$roundId);
        
        return $scoringId;
    }

    public static function deleteScoringByRound($roundId)
    {
        App::$_DB->doQuery('DELETE FROM `scoring_competition` WHERE `Round_round_id` = ?', 'i', (int)$roundId);
        App::$_DB->doQuery('DELETE FROM `scoring` WHERE `Round_round_id` = ?', 'i', (int)$roundId);
        return true;
    }
    
    public static function deleteAllScoringCompetitionByCompetition($competitionId)
    {
        App::$_DB->doQuery('DELETE FROM `scoring_competition` WHERE `Competition_competition_id` = ?', 'i', (int)$competitionId);
        App::$_DB->doQuery('DELETE FROM `scoring` WHERE `Competition_competition_id` = ?', 'i', (int)$competitionId);
    }
    
    public static function getAllScorings($competitionId=0, $section=false)
    {
        if ($competitionId != 0)
        {
            if ($section) {
                self::$resultList = App::$_DB->doQuery(
                    'SELECT `scoring`.*, `scoring_competition`.`Scoring_Competition_enabled`, `scoring_competition`.`Scoring_Competition_points`, `scoring_competition`.`Round_round_id`, `section`.`section_name` FROM `scoring` INNER JOIN `section` ON `section`.`section_id` = `scoring`.`Section_section_id` INNER JOIN `scoring_competition` ON `scoring_competition`.`Scoring_scoring_id` = `scoring`.`scoring_id` WHERE `scoring_competition`.`Competition_competition_id` = ? AND `Section_section_id` = ? ORDER BY `scoring_id` ASC',
                    'ii', (int)$competitionId, (int)$section
                );
            } else {
                self::$resultList = App::$_DB->doQuery(
                    'SELECT `scoring`.*, `scoring_competition`.`Scoring_Competition_enabled`, `scoring_competition`.`Scoring_Competition_points`, `scoring_competition`.`Round_round_id`, `section`.`section_name` FROM `scoring` INNER JOIN `section` ON `section`.`section_id` = `scoring`.`Section_section_id` INNER JOIN `scoring_competition` ON `scoring_competition`.`Scoring_scoring_id` = `scoring`.`scoring_id` WHERE `scoring_competition`.`Competition_competition_id` = ? ORDER BY `scoring_id` ASC',
                    'i', (int)$competitionId
                );
            }
        }
        else
        {
            self::$resultList = App::$_DB->doQuery('SELECT * FROM `scoring` WHERE `Round_round_id` = 0');
        }
    }

    public static function getScoringByRoundId($roundId)
    {
        self::$resultList = App::$_DB->doQuery(
            'SELECT `scoring`.*, `scoring_competition`.`Scoring_Competition_enabled`, `scoring_competition`.`Scoring_Competition_points`, `scoring_competition`.`Round_round_id`, `section`.`section_name` FROM `scoring` INNER JOIN `section` ON `section`.`section_id` = `scoring`.`Section_section_id` INNER JOIN `scoring_competition` ON `scoring_competition`.`Scoring_scoring_id` = `scoring`.`scoring_id` WHERE `scoring`.`Round_round_id` = ?',
            'i', (int)$roundId
        );
        return App::$_DB->getRecord(self::$resultList);
    }
    
    public static function nextScoring()
    {
        if (self::$resultList == null)
          return null;

        $record = App::$_DB->getRecord(self::$resultList);
        if ($record == null)
          self::$resultList = null;

        return $record;
    }

    public static function exists($id)
    {
        $record = App::$_DB->doQuery('SELECT count(*) AS total FROM `scoring` WHERE `scoring_id` = ?', 'i', (int)$id);
        return (boolean)App::$_DB->getRecord($record)->total;
    }

    public static function addCompetition($scoringId, $competitionId)
    {
        App::$_DB->doQuery('INSERT INTO `scoring_competition` (Scoring_scoring_id, Competition_competition_id, Scoring_Competition_enabled, Scoring_Competition_points, Round_round_id) VALUES (?, ?, ?, ?, ?)', 'iiiii', (int)$scoringId, (int)$competitionId, 0, 0, 0);
    } 
}
?>
