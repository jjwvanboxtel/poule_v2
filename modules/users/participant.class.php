<?php
if (!defined('VALID_ACCESS')) die();

/**
 * This class is the model for a participant.
 *
 * @package   poule
 * @author    Jaap van Boxtel
 * @copyright 20-05-2013
 * @version   0.1
 */
class Participant extends User
{
    private $result = null;
    private $competitionList = array();
    
    public function __construct($id)
    {       
        $id = (int)$id;
        parent::__construct($id);

        $this->result = App::$_DB->doQuery('SELECT * FROM `participant` WHERE `User_user_id` = ? LIMIT 1', 'i', $this->id);
        $this->result = App::$_DB->getRecord($this->result);        
    
        $resultList = App::$_DB->doQuery('SELECT * FROM `participant_competition` WHERE `Participant_User_user_id` = ?', 'i', $this->id);
                          
        while (($competition = App::$_DB->getRecord($resultList)) != null)
        {
            $this->competitionList[$competition->Competition_competition_id]['payed'] = $competition->Participant_Competition_payed;
            $this->competitionList[$competition->Competition_competition_id]['subscribed'] = $competition->Participant_Competition_subscribed;            
        }
    } //__construct
    
    public function __destruct()
    {
        App::$_DB->freeQuery($this->result);
    } //__destruct

    /**
     * Updates the cutomer in the database.
     */
    public function save()
    {        
        parent::save();
        
        App::$_DB->doQuery(
            'UPDATE `participant` SET `part_postalCode` = ?, `part_street` = ?, `part_town` = ?, `part_housenr` = ?, `part_addition` = ?, `part_bankaccount` = ? WHERE `User_user_id` = ? LIMIT 1',
            'sssissi',
            $this->result->part_postalCode,
            $this->result->part_street,
            $this->result->part_town,
            (int)$this->result->part_housenr,
            $this->result->part_addition,
            $this->result->part_bankaccount,
            $this->id
        );

        
        foreach ($this->competitionList as $competitionId => $participant)
        {
            App::$_DB->doQuery(
                'UPDATE `participant_competition` SET `Participant_Competition_payed` = ?, `Participant_Competition_subscribed` = ? WHERE `Participant_User_user_id` = ? AND `Competition_competition_id` = ? LIMIT 1',
                'ssii',
                $participant['payed'],
                $participant['subscribed'],
                $this->id,
                (int)$competitionId
            );
        }
    } //save

    /**
     * Removes the participant from the database
     */
    public function delete()
    {
        parent::delete();

        App::$_DB->doQuery('DELETE FROM `participant` WHERE `User_user_id` = ?', 'i', $this->id);
        
        self::deleteAllParticipantCompetitionByUser($this->id);
        
        $this->__destruct();
    } //delete

    /**
     * Creates a new participant and adds it to the database.
     *
     * @param String $email
     * @param String $password
     * @param String $firstName
     * @param String $lastName
     * @param String $phoneNr
     * @param int $userGroup
     * @param String $postalCode
     * @param String $street
     * @param String $town
     * @param int $houseNr
     * @param String $addition
     * @param int $bankAccount
     */
    public static function addp($enabled, $email, $password, $firstName, $lastName, $phoneNr, $userGroup,
                                                    $postalCode, $street, $town, $houseNr, $addition='', $bankAccount)
    {             
        $id = parent::add($enabled, $email, $password, $firstName, $lastName, $phoneNr, $userGroup);
        App::$_DB->doQuery(
            'INSERT INTO `participant` (part_postalCode, part_street, part_town, part_housenr, part_addition, part_bankaccount, User_user_id) VALUES (?, ?, ?, ?, ?, ?, ?)',
            'sssissi',
            str_replace(' ', '', $postalCode),
            $street,
            $town,
            (int)$houseNr,
            $addition,
            $bankAccount,
            (int)$id
        );
                                   
        App::openClass('Round', 'modules/rounds');       
        App::openClass('Question', 'modules/questions');     
        App::openClass('Game', 'modules/games');
        App::openClass('Competition', 'modules/competitions');

        Game::createPredictions($id);
        Question::createPredictions($id);
        Round::createPredictions($id);
        Competition::createCompetitions($id);
        
        return $id;
    } //add

    /**
     * Gets the id of an participant.
     *
     * @return int $id
     */
    public function getId()
    {
        return $this->id;
    } // getId

    /**
     * Gets the postalcode.
     *
     * @return String $postalCode
     */
    public function getPostalCode()
    {
        return $this->result->part_postalCode;
    } // getPostalCode

    /**
     * Gets the streetname of an participant
     *
     * @return String $street
     */
    public function getStreet()
    {
        return $this->result->part_street;
    } // getStreet

    /**
     * Gets the town of an participant
     *
     * @return String $town
     */
    public function getTown()
    {
        return $this->result->part_town;
    } // getTown
 
    /**
     * Gets the housenumber of an participant
     * 
     * @return int $houseNr
     */
    public function getHouseNr()
    {
        return $this->result->part_housenr;
    } // $houseNr

    /**
     * Gets the addition of an housenumber
     *
     * @return String $addition
     */
    public function getAddition()
    {
        return $this->result->part_addition;
    } // $getAddition

    /**
     * Gets the bankaccount
     *
     * @return String $addition
     */
    public function getBankAccount()
    {
        return $this->result->part_bankaccount;
    } // $getBankAccount

    /**
     * Gets the payed status
     *
     * @return String $payed
     */
    public function getPayed($competitionId)
    {
        return $this->competitionList[$competitionId]['payed'];
    } // $getPayed    

    /**
     * Gets the subscribed status
     *
     * @return String $subsribed
     */
    public function getSubscribed($competitionId)
    {
        return $this->competitionList[$competitionId]['subscribed'];
    } // $getSubscribed    

    
    /**
     * Sets the postalCode of a participant
     *
     * @param $postalCode
     */
    public function setPostalCode($postalCode)
    {
        $this->result->part_postalCode = $postalCode;
    } // setPostalCode

    /**
     * Sets the streetname of a participant
     *
     * @param $street
     */
     public function setStreet($street)
     {
         $this->result->part_street = $street;
     } // setStreet

     /**
     * Gets the addition of an housenumber
     *
     * @return String $addition
     */
     public function setAddition($addition)
     {
         $this->result->part_addition = $addition;
     } // setStreet

     /**
      * Sets the town of a participant
      *
      * @param $town
      */
     public function setTown($town)
     {
         $this->result->part_town = $town;
     } // setTown

     /**
      * Sets the housenumber of a participant
      *
      * @param $houseNr
      */
     public function setHouseNr($houseNr)
     {
         $this->result->part_housenr = (int) $houseNr;
     } // setHouseNr

     /**
      * Sets the bankaccount of a participant
      *
      * @param $houseNr
      */
     public function setBankAccount($number)
     {
         $this->result->part_bankaccount = $number;
     } // setBankAccount 

    /**
     * Sets the payed status of a participant
     *
     * @param $payed
     */
    public function setPayed($competitionId, $payed)
    {
       $this->competitionList[$competitionId]['payed'] = $payed;
    } // setPayed      

    /**
     * Sets the subscribed status of a participant
     *
     * @param $subscribed
     */
    public function setSubscribed($competitionId, $subscribed)
    {
       $this->competitionList[$competitionId]['subscribed'] = $subscribed;
    } // setSubscribed      
         
    public static function deleteAllParticipantCompetitionByUser($userId)
    {
        App::$_DB->doQuery('DELETE FROM `participant_competition` WHERE `Participant_User_user_id` = ?', 'i', (int)$userId);
    }
    
    public static function deleteAllParticipantCompetitionByCompetition($competitionId)
    {
        App::$_DB->doQuery('DELETE FROM `participant_competition` WHERE `Competition_competition_id` = ?', 'i', (int)$competitionId);
    }
    
    public static function addCompetition($userId, $competitionId)
    {
        App::$_DB->doQuery('INSERT INTO `participant_competition` (Participant_User_user_id, Competition_competition_id, Participant_Competition_payed, Participant_Competition_subscribed) VALUES (?, ?, 0, 0)', 'ii', (int)$userId, (int)$competitionId);
    } 
    
    public static function getNumberOfParticipants($competitionId, $subscribed, $payed)
    {
        $sql = 'SELECT count(*) AS total FROM `participant_competition` WHERE `Competition_competition_id` = ?';
        $types = 'i';
        $params = [(int)$competitionId];

        if ($payed) {
            $sql .= ' AND `Participant_Competition_payed` = 1';
        }
        if ($subscribed) {
            $sql .= ' AND `Participant_Competition_subscribed` = 1';
        }

        $record = App::$_DB->doQuery($sql, $types, ...$params);
        return App::$_DB->getRecord($record)->total;
    } 
} // Participant
?>
