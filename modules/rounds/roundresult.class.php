<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class RoundResult
{
    private $result = null;
    private $id = 0;
    
    private static $resultList = null;
    
    public function __construct($id)
    {
        $this->id = (int)$id;
        $this->result = App::$_DB->doSQL('SELECT *
                                          FROM `round_result`
                                          WHERE `round_result_id` = ' . $this->id . ' LIMIT 1;');
        $this->result = App::$_DB->getRecord($this->result);
    }

    /**
     * Destructs the roundresult-object.
     */
    public function __destruct()
    {
        App::$_DB->freeQuery($this->result);
    } //__destruct

    public function getId()
    {
        return $this->id;
    }

    public function getCountry()
    {
        $country = $this->result->Country_country_id;
        if ($country != 0)
            $country = new Country($country);
        return $country;
    }

    public function setCountry($country)
    {
        $this->result->Country_country_id = $country;
    }

    public function delete()
    {
        App::$_DB->doSQL('DELETE FROM `round_result` WHERE `round_result_id` = ' . $this->id . '');

        $this->__destruct();
        return true;
    }

    public static function deleteAllByCompetition($competitionId)
    {
        App::$_DB->doSQL('DELETE FROM `round_result`
                          WHERE `Competition_competition_id` = ' . (int)$competitionId . '');
    }
    
    public function save()
    {
        App::$_DB->doSQL('UPDATE `round_result` SET
                          `Country_country_id` = "'.$this->result->Country_country_id.'",
                          `Round_round_id` = "'.$this->result->Round_round_id.'"
                          WHERE `round_result_id` = ' . $this->id . ' LIMIT 1;');
    }

    public static function getAllRoundResults($roundId)
    {
       self::$resultList = App::$_DB->doSQL('SELECT * FROM `round_result` WHERE `Round_round_id` = ' .$roundId);
    }

    public static function deleteAllRoundResultsByRound($roundId)
    {
        self::$resultList = App::$_DB->doSQL('DELETE FROM `round_result`
                          WHERE `Round_round_id` = ' . (int)$roundId . '');
    }    
    
    public static function add($countryId, $roundId)
    {
        App::$_DB->doSQL('INSERT INTO `round_result` (Country_country_id, Round_round_id)
                          VALUES (
                            '.(int)$countryId.',
                            '.(int)$roundId.')
                          ');
    }

    public static function nextRoundResult()
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
                                    FROM `round_result`
                                    WHERE `Round_round_id` = ' . (int)$id);

        return (boolean)App::$_DB->getRecord($record)->total;
    }

}
?>
