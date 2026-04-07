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
        
        $this->result = App::$_DB->doSQL('SELECT *
                                          FROM `participant_round_prediction`
                                          WHERE `Participant_User_user_id` = ' . $this->userId . '
                                          AND `Round_round_id` = ' . $this->roundId . '
                                          AND `Round_prediction_id` = ' . $this->predictionId);
                                          
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
        App::$_DB->doSQL('DELETE FROM `participant_round_prediction` 
                            WHERE `Participant_User_user_id` = ' . $this->userId . '
                            AND `Round_round_id` = ' . $this->roundId . '
                            AND `Round_prediction_id` = ' . $this->predictionId . '');

        $this->__destruct();
        return true;
    }
    
    public function save()
    {
        App::$_DB->doSQL('UPDATE `participant_round_prediction` SET
                          `Country_country_id` = "'.App::$_DB->escapeString($this->result->Country_country_id).'"
                          WHERE `Participant_User_user_id` = ' . $this->userId . '
                          AND `Round_round_id` = ' . $this->roundId . '
                          AND `Round_prediction_id` = ' . $this->predictionId . ' LIMIT 1;');
    }

    public static function getAllPredictions($userId)
    {
        self::$resultList = App::$_DB->doSQL('SELECT * FROM `participant_round_prediction`
                          WHERE `Participant_User_user_id` = ' . (int)$userId);
    }
    
    public static function getAllPredictionsByRound($userId, $roundId)
    {
        self::$resultList = App::$_DB->doSQL('SELECT * FROM `participant_round_prediction`
                          WHERE `Participant_User_user_id` = ' . (int)$userId . '
                          AND `Round_round_id` = ' . (int)$roundId);
    }
    
    public static function getPredictionCountryCount($roundId)
    {
        self::$resultList = App::$_DB->doSQL('SELECT  `Country_country_id` , COUNT( Country_country_id ) AS count
                          FROM  `participant_round_prediction` 
                          INNER JOIN `participant_competition`
                          ON `participant_round_prediction`.`Participant_User_user_id`=`participant_competition`.`Participant_User_user_id`
                          WHERE  `Round_round_id` = ' . (int)$roundId . '
                          AND `participant_competition`.`Participant_Competition_payed` = 1 
                          AND `participant_competition`.`Participant_Competition_subscribed` = 1
                          GROUP BY  `Country_country_id`
                          ORDER BY  `count` DESC
                          LIMIT 15');
    }
    
    public static function deleteAllPredictionsByUser($userId)
    {
        self::$resultList = App::$_DB->doSQL('DELETE FROM `participant_round_prediction`
                          WHERE `Participant_User_user_id` = ' . (int)$userId);
    }
    
    public static function deleteAllPredictionsByRound($roundId)
    {
        self::$resultList = App::$_DB->doSQL('DELETE FROM `participant_round_prediction`
                          WHERE `Round_round_id` = ' . (int)$roundId . '');
    }
    
    public static function add($userId, $roundId, $predictionId, $country)
    {
        App::$_DB->doSQL('INSERT INTO `participant_round_prediction` (Participant_User_user_id, Round_round_id, Round_prediction_id, Country_country_id)
                          VALUES (
                            '.(int)$userId.',
                            '.(int)$roundId.',
                            '.(int)$predictionId.',
                            '.(int)$country.')
                          ');
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
        $record = App::$_DB->doSQL('SELECT count( * ) AS total
                                    FROM `participant_round_prediction`
                                    WHERE `Participant_User_user_id` = ' . (int)$userId . '
                                    AND `Round_round_id` = ' . (int)$roundId . '');

        return (boolean)App::$_DB->getRecord($record)->total;
    }

}
?>
