<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Subleague
{
    private $result = null;
    private $id = 0;

    private $participants = array();
    
    private static $header_dir = 'subleagues';
    private static $resultList = null;
    
    public function __construct($id)
    {
        $this->id = (int)$id;
        $this->result = App::$_DB->doSQL('SELECT *
                                          FROM `subleague`
                                          WHERE `subleague_id` = ' . $this->id . ' LIMIT 1;');
        $this->result = App::$_DB->getRecord($this->result);
        
        $resultList = App::$_DB->doSQL('SELECT `table`.`Participant_User_user_id`, `table`.`table_points`, `table`.`table_position`
                        FROM  `subleague` 
                        INNER JOIN  `participant_subleague` ON  `subleague`.`subleague_id` =  `participant_subleague`.`Subleague_subleague_id` 
                        INNER JOIN  `table` ON  `participant_subleague`.`Participant_User_user_id` =  `table`.`Participant_User_user_id` 
                        AND `subleague`.`Competition_competition_id`=`table`.`Competition_competition_id`
                        WHERE `subleague`.`subleague_id`='.$this->id.'
                        ORDER BY `table`.`table_position` ASC');
                                  
        while (($result = App::$_DB->getRecord($resultList)) != null)
        {
            $participant = array();
            $participant['id'] = $result->Participant_User_user_id;
            $participant['points'] = $result->table_points;
            $participant['position'] = $result->table_position;
            array_push($this->participants, $participant);
        }
    }

    /**
     * Destructs the subleague-object.
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
        return $this->result->subleague_name;
    }
   
    public function getImage()
    {
        return $this->result->subleague_header;
    }
       
    public function getParticipants()
    {
        return $this->participants;
    }
    
    public function getTable()
    {

    }
    
    public function setName($name)
    {
        $this->result->subleague_name = $name;
    }

    public function setImage($file)
    {
        $curImage = $this->result->subleague_header;

        /*$delete = App::$_UPL->deleteFile($curImage, $this->result->Competition_competition_id.'/'.self::$header_dir.'/'.$this->id.'/');
        App::$_UPL->loadUp($file, $this->result->Competition_competition_id.'/'.self::$header_dir.'/'.$this->id.'/');
        */
        $this->result->subleague_header = $file['name'];


        return $delete;
    }
          
    public function delete()
    {        
        App::$_UPL->deleteDir(UPLOAD_DIR.$this->result->Competition_competition_id.'/'.self::$header_dir.'/'.$this->id.'/');

        App::$_DB->doSQL('DELETE FROM `participant_subleague` WHERE `Subleague_subleague_id` = ' . $this->id . '');        
        App::$_DB->doSQL('DELETE FROM `subleague` WHERE `subleague_id` = ' . $this->id . '');
        
        $this->__destruct();
        return true;
    }

    public function save()
    {
        App::$_DB->doSQL('UPDATE `subleague` SET
                          `subleague_name` = "'.App::$_DB->escapeString($this->result->subleague_name).'",
                          `subleague_header` = "'.App::$_DB->escapeString($this->result->subleague_header).'"
                          WHERE `subleague_id` = ' . $this->id . ' LIMIT 1;');
    }

    public static function getAllSubleagues()
    {
       self::$resultList = App::$_DB->doSQL('SELECT * FROM `subleague` ORDER BY `subleague_id` ASC');
    }

    public static function add($name, /*$header,*/ $competitionId)
    {
        App::$_DB->doSQL('INSERT INTO `subleague` (subleague_name, subleague_header, Competition_competition_id)
                          VALUES (
                            "'.App::$_DB->escapeString($name).'",
                            "" './*App::$_DB->escapeString($header['name']).*/',
                            '.(int)$competitionId.')
                          ');
        
        $subleagueId = App::$_DB->getLastId();

        //App::$_UPL->loadUp($header, $competitionId.'/'.self::$header_dir.'/'.(int)$subleagueId.'/');
                
        return $subleagueId;
    }
    
    public function addParticipant($participantId)
    {
        App::$_DB->doSQL('INSERT INTO `participant_subleague` (Subleague_subleague_id, Participant_User_user_id)
                      VALUES (
                        '.$this->getId().',
                        '.(int)$participantId.')
                      ');
    }

    public function deleteParticipant($participantId)
    {
        App::$_DB->doSQL('DELETE FROM `participant_subleague` 
                           WHERE `Subleague_subleague_id`='.$this->getId().' 
                           AND `Participant_User_user_id`='.(int)$participantId.'
                      ');
    }
    
    public static function nextSubleague()
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
                                    FROM `subleague`
                                    WHERE `subleague_id` = ' . (int)$id);

        return (boolean)App::$_DB->getRecord($record)->total;
    }

    public static function deleteAllByUser($userId)
    {
        App::$_DB->doSQL('DELETE FROM `participant_subleague` WHERE `Participant_User_user_id` = ' . (int)$userId . '');        
    }
    
    public static function deleteAllByCompetition($competitionId)
    {
        $resultList = App::$_DB->doSQL('SELECT * FROM `subleague` WHERE `Competition_competition_id` = ' . (int)$competitionId);                                  
        while (($result = App::$_DB->getRecord($resultList)) != null)
        {
            App::$_DB->doSQL('DELETE FROM `participant_subleague` WHERE `Subleague_subleague_id` = ' . $result->subleague_id . '');            
        }
        App::$_DB->doSQL('DELETE FROM `subleague` WHERE `Competition_competition_id` = ' . (int)$competitionId . '');                    
    }
    
    public static function participantExists($id, $participantId)
    {
        $record = App::$_DB->doSQL('SELECT count( * ) AS total
                                    FROM `participant_subleague`
                                    WHERE `Subleague_subleague_id` = ' . (int)$id . '
                                    AND `Participant_User_user_id` = '. (int)$participantId);

        return (boolean)App::$_DB->getRecord($record)->total;   
    }
    
    public static function getHeaderDir($competitionId, $subleagueId)
    {
        return $competitionId.'/'.self::$header_dir.'/'.(int)$subleagueId.'/';
    }
    
}
?>
