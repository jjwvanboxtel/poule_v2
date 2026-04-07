<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Referee
{
    private $result = null;
    private $id = 0;
    
    private static $resultList = null;
    
    public function __construct($id)
    {
        $this->id = (int)$id;
        $this->result = App::$_DB->doSQL('SELECT *
                                          FROM `referee`
                                          WHERE `referee_id` = ' . $this->id . ' LIMIT 1;');
        $this->result = App::$_DB->getRecord($this->result);
    }

    /**
     * Destructs the referee-object.
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
        return $this->result->referee_name;
    }
    
    public function setName($name)
    {
        $this->result->referee_name = $name;
    }

    /**
     * Gets how many games this referee has
     */
    public function getGameCount()
    {
        $record = App::$_DB->doSQL('SELECT count( * ) AS total
                                    FROM `game`
                                    WHERE `Referee_referee_id` = ' . $this->id);

        return App::$_DB->getRecord($record)->total;
    } //getGameCount

    public function delete()
    {
        App::$_DB->doSQL('DELETE FROM `referee` WHERE `referee_id` = ' . $this->id . '');

        $this->__destruct();
        return true;
    }

    public static function deleteAllByCompetition($competitionId)
    {
        App::$_DB->doSQL('DELETE FROM `referee`
                          WHERE `Competition_competition_id` = ' . (int)$competitionId . '');
    }
    
    public function save()
    {
        App::$_DB->doSQL('UPDATE `referee` SET
                          `referee_name` = "'.App::$_DB->escapeString($this->result->referee_name).'"
                          WHERE `referee_id` = ' . $this->id . ' LIMIT 1;');
    }

    public static function getAllReferees($competitionId)
    {
       self::$resultList = App::$_DB->doSQL('SELECT * FROM `referee`
                                             WHERE `Competition_competition_id` = '.(int)$competitionId.'
                                             ORDER BY `referee_name` ASC');
    }

    public static function add($competitionId, $name)
    {
        App::$_DB->doSQL('INSERT INTO `referee` (referee_name, Competition_competition_id)
                          VALUES (
                            "'.App::$_DB->escapeString($name).'",
                            '.(int)$competitionId.')
                          ');
        return App::$_DB->getLastId();
    }

    public static function nextReferee()
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
                                    FROM `referee`
                                    WHERE `referee_id` = ' . (int)$id);

        return (boolean)App::$_DB->getRecord($record)->total;
    }

}
?>
