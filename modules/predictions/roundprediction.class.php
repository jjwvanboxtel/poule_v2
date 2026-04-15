<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class RoundPrediction
{
    private $result = null;
    private $userId = 0;
    private $roundId = 0;
    private $predictionId = 0;
    
    private static $resultList = null;
    
    public function __construct($userId, $roundId, $predictionId)
    {
        $this->userId = (int)$userId;
        $this->roundId = (int)$roundId;
        $this->predictionId = (int)$predictionId;
        
        $this->result = App::$_DB->doQuery('SELECT * FROM `participant_round_prediction` WHERE `Participant_User_user_id` = ? AND `Round_round_id` = ? AND `Round_prediction_id` = ?', 'iii', $this->userId, $this->roundId, $this->predictionId);
        $this->result = App::$_DB->getRecord($this->result);
    }

    /**
     * Destructs the prediction-object.
     */
    public function __destruct()
    {
        App::$_DB->freeQuery($this->result);
    } //__destruct

    public function getUserId()
    {
        return $this->userId;
    }

    public function getRoundId()
    {
        return $this->roundId;
    }

    public function getPredictionId()
    {
        return $this->predictionId;
    }
    
    public function getCountry()
    {
        $countryId = $this->result->Country_country_id;
        if ($countryId != 0)
            return new Country($countryId);
        else
            return null;
    }
    
    public function setCountry($country)
    {
        $this->result->Country_country_id = $country;
    }

    public function delete()
    {
        App::$_DB->doQuery('DELETE FROM `participant_round_prediction` WHERE `Participant_User_user_id` = ? AND `Round_round_id` = ? AND `Round_prediction_id` = ?', 'iii', $this->userId, $this->roundId, $this->predictionId);
        $this->__destruct();
        return true;
    }
    
    public function save()
    {
        App::$_DB->doQuery('UPDATE `participant_round_prediction` SET `Country_country_id` = ? WHERE `Participant_User_user_id` = ? AND `Round_round_id` = ? AND `Round_prediction_id` = ? LIMIT 1', 'iiii', (int)$this->result->Country_country_id, $this->userId, $this->roundId, $this->predictionId);
    }

    public static function getAllPredictions($userId)
    {
        self::$resultList = App::$_DB->doQuery('SELECT * FROM `participant_round_prediction` WHERE `Participant_User_user_id` = ?', 'i', (int)$userId);
    }
    
    public static function getAllPredictionsByRound($userId, $roundId)
    {
        self::$resultList = App::$_DB->doQuery('SELECT * FROM `participant_round_prediction` WHERE `Participant_User_user_id` = ? AND `Round_round_id` = ?', 'ii', (int)$userId, (int)$roundId);
    }
    
    public static function getPredictionCountryCount($roundId)
    {
        self::$resultList = App::$_DB->doQuery(
            'SELECT `Country_country_id`, COUNT(`Country_country_id`) AS count FROM `participant_round_prediction` INNER JOIN `participant_competition` ON `participant_round_prediction`.`Participant_User_user_id` = `participant_competition`.`Participant_User_user_id` WHERE `Round_round_id` = ? AND `participant_competition`.`Participant_Competition_payed` = 1 AND `participant_competition`.`Participant_Competition_subscribed` = 1 GROUP BY `Country_country_id` ORDER BY `count` DESC LIMIT 15',
            'i', (int)$roundId
        );
    }
    
    public static function deleteAllPredictionsByUser($userId)
    {
        App::$_DB->doQuery('DELETE FROM `participant_round_prediction` WHERE `Participant_User_user_id` = ?', 'i', (int)$userId);
    }
    
    public static function deleteAllPredictionsByRound($roundId)
    {
        App::$_DB->doQuery('DELETE FROM `participant_round_prediction` WHERE `Round_round_id` = ?', 'i', (int)$roundId);
    }
    
    public static function add($userId, $roundId, $predictionId, $country)
    {
        App::$_DB->doQuery('INSERT INTO `participant_round_prediction` (Participant_User_user_id, Round_round_id, Round_prediction_id, Country_country_id) VALUES (?, ?, ?, ?)', 'iiii', (int)$userId, (int)$roundId, (int)$predictionId, (int)$country);
    }

    public static function nextPrediction()
    {
        if (self::$resultList == null)
          return null;

        $record = App::$_DB->getRecord(self::$resultList);
        if ($record == null)
          self::$resultList = null;

        return $record;
    }

    public static function exists($userId, $roundId)
    {
        $record = App::$_DB->doQuery('SELECT count(*) AS total FROM `participant_round_prediction` WHERE `Participant_User_user_id` = ? AND `Round_round_id` = ?', 'ii', (int)$userId, (int)$roundId);
        return (boolean)App::$_DB->getRecord($record)->total;
    }

}
?>
