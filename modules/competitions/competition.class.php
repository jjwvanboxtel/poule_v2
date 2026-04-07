<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Competition
{
    private $result = null;
    private $id = 0;

    private static $header_dir = 'header/';
    private static $resultList = null;
    
    public function __construct($id)
    {
        $this->id = (int)$id;
        $this->result = App::$_DB->doSQL('SELECT *
                                          FROM `competition`
                                          WHERE `competition_id` = ' . $this->id . ' LIMIT 1;');
        $this->result = App::$_DB->getRecord($this->result);
    }

    /**
     * Destructs the competition-object.
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
        return $this->result->competition_name;
    }

    public function getMoney()
    {
        return $this->result->competition_money;
    }

    public function getFirstPlace()
    {
        return $this->result->competition_first_place;
    }

    public function getSecondPlace()
    {
        return $this->result->competition_second_place;
    }

    public function getThirdPlace()
    {
        return $this->result->competition_third_place;
    }
    
    public function getImage()
    {
        return $this->result->competition_header;
    }
    
    public function getDescription()
    {
        return $this->result->competition_description;
    }

    public function getFinalSubmissionDate()
    {
        return $this->result->competition_final_submission_date;
    }
    
    public function setName($name)
    {
        $this->result->competition_name = $name;
    }

    public function setMoney($money)
    {
        $this->result->competition_money = $money;
    }

    public function setFirstPlace($percentage)
    {
        $this->result->competition_first_place = $percentage;
    }

    public function setSecondPlace($percentage)
    {
        $this->result->competition_second_place = $percentage;
    }

    public function setThirdPlace($percentage)
    {
        $this->result->competition_third_place = $percentage;
    }

    public function setFinalSubmissionDate($date)
    {
        $this->result->competition_final_submission_date = $date;
    }
    
    public function setImage($file)
    {
        $curImage = $this->result->competition_header;

        $delete = App::$_UPL->deleteFile($curImage, $this->result->competition_id.'/'.self::$header_dir);
        $safe = App::$_UPL->loadUp($file, $this->result->competition_id.'/'.self::$header_dir);

        $this->result->competition_header = $safe;

        return $delete;
    }

    public function setDescription($description)
    {
        $this->result->competition_description = $description;
    }
        
    public function delete()
    {        
        App::openClass('City', 'modules/cities');
        App::openClass('Player', 'modules/players');
        App::openClass('Country', 'modules/countries');
        App::openClass('Game', 'modules/games');
        App::openClass('GamePrediction', 'modules/predictions');
        App::openClass('RoundPrediction', 'modules/predictions');
        App::openClass('QuestionPrediction', 'modules/predictions');
        App::openClass('Poule', 'modules/poules');
        App::openClass('Question', 'modules/questions');
        App::openClass('Referee', 'modules/referees');
        App::openClass('Round', 'modules/rounds');
        App::openClass('RoundResult', 'modules/rounds');
        App::openClass('Scoring', 'modules/scorings');
        App::openClass('Section', 'modules/sections');
        App::openClass('Ranking', 'modules/table');
        App::openClass('Form', 'modules/forms');
        App::openClass('Subleague', 'modules/subleagues');

        Game::deleteAllByCompetition($this->id);
        Round::deleteAllByCompetition($this->id);
        Ranking::deleteAllByCompetition($this->id);        
        City::deleteAllByCompetition($this->id);
        Player::deleteAllByCompetition($this->id);
        Country::deleteAllByCompetition($this->id);
        Poule::deleteAllByCompetition($this->id);
        Referee::deleteAllByCompetition($this->id);
        Question::deleteAllByCompetition($this->id);
        Scoring::deleteAllScoringCompetitionByCompetition($this->id);
        Section::deleteAllSectionCompetitionByCompetition($this->id);
        Participant::deleteAllParticipantCompetitionByCompetition($this->id);
        Form::deleteAllByCompetition($this->id);
        Subleague::deleteAllByCompetition($this->id);
        
        App::$_UPL->deleteDir(UPLOAD_DIR.$this->id);
        
        App::$_DB->doSQL('DELETE FROM `competition` WHERE `competition_id` = ' . $this->id . '');
        
        $this->__destruct();
        return true;
    }

    public function save()
    {
        App::$_DB->doSQL('UPDATE `competition` SET
                          `competition_name` = "'.App::$_DB->escapeString($this->result->competition_name).'",
                          `competition_description` = "'.addslashes($this->result->competition_description).'",
                          `competition_header` = "'.App::$_DB->escapeString($this->result->competition_header).'",
                          `competition_final_submission_date` = '.$this->result->competition_final_submission_date.',
                          `competition_money` = '.$this->result->competition_money.',
                          `competition_first_place` = '.$this->result->competition_first_place.',
                          `competition_second_place` = '.$this->result->competition_second_place.',
                          `competition_third_place` = '.$this->result->competition_third_place.'
                          WHERE `competition_id` = ' . $this->id . ' LIMIT 1;');
    }

    public static function getAllCompetitions()
    {
       self::$resultList = App::$_DB->doSQL('SELECT * FROM `competition` ORDER BY `competition_name` ASC');
    }

    public static function add($name, $description, $header, $submission_date, $money, $first_place, $second_place, $third_place)
    {
        // Insert with an empty header placeholder first so we have the
        // competition ID available for the upload path, then update the
        // header field with the safe (randomised) filename after upload.
        App::$_DB->doSQL('INSERT INTO `competition` (competition_name, competition_description, competition_header, competition_final_submission_date, competition_money, competition_first_place, competition_second_place, competition_third_place)
                          VALUES (
                            "'.App::$_DB->escapeString($name).'",
                            "'.addslashes($description).'",
                            "",
                            '.$submission_date.',
                            '.App::$_DB->escapeString($money).',
                            '.App::$_DB->escapeString($first_place).',
                            '.App::$_DB->escapeString($second_place).',
                            '.App::$_DB->escapeString($third_place).')
                          ');
        
        $competitionId = App::$_DB->getLastId();

        $safe = App::$_UPL->loadUp($header, $competitionId.'/'.self::$header_dir);

        App::$_DB->doSQL('UPDATE `competition` SET `competition_header` = "'.App::$_DB->escapeString($safe).'" WHERE `competition_id` = '.intval($competitionId).' LIMIT 1;');
        
        App::openClass('Participant', 'modules/users');
        User::getAllUsers(3);
        while (($user = User::nextUser()) != null)
        {
            Participant::addCompetition($user->user_id, $competitionId);
        }

        App::openClass('Section', 'modules/sections');   
        Section::getAllSections();
        while (($section = Section::nextSection()) != null)
        {       
            Section::addCompetition($section->section_id, $competitionId);
        }

        App::openClass('Scoring', 'modules/scorings');
        Scoring::getAllScorings();
        while (($scoring = Scoring::nextScoring()) != null)
        {       
            Scoring::addCompetition($scoring->scoring_id, $competitionId);
        }
        
        return $competitionId;
    }
    
    public static function nextCompetition()
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
        $record = App::$_DB->doSQL('SELECT count( * ) AS total
                                    FROM `competition`
                                    WHERE `competition_id` = ' . (int)$id);

        return (boolean)App::$_DB->getRecord($record)->total;
    }

    public static function getHeaderDir($competitionId)
    {
        return $competitionId.'/'.self::$header_dir;
    }
    
    public static function createCompetitions($userId) {
        App::openClass('Participant', 'modules/users');

        Competition::getAllCompetitions();
        while (($competition = Competition::nextCompetition()) != null)
        {
           Participant::addCompetition($userId, $competition->competition_id);
        }
    }

    public static function checkSubmissionDateExpired($competitionId, $date)
    {
        $competition = new Competition($competitionId);
        if ($competition->getFinalSubmissionDate() < $date)
            return true;
            
        return false;
    }    

}
?>
