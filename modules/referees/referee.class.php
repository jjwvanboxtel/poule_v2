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
        $this->result = App::$_DB->doQuery('SELECT * FROM `referee` WHERE `referee_id` = ? LIMIT 1', 'i', $this->id);
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
        $record = App::$_DB->doQuery('SELECT count(*) AS total FROM `game` WHERE `Referee_referee_id` = ?', 'i', $this->id);
        return App::$_DB->getRecord($record)->total;
    } //getGameCount

    public function delete()
    {
        App::$_DB->doQuery('DELETE FROM `referee` WHERE `referee_id` = ?', 'i', $this->id);
        $this->__destruct();
        return true;
    }

    public static function deleteAllByCompetition($competitionId)
    {
        App::$_DB->doQuery('DELETE FROM `referee` WHERE `Competition_competition_id` = ?', 'i', (int)$competitionId);
    }
    
    public function save()
    {
        App::$_DB->doQuery('UPDATE `referee` SET `referee_name` = ? WHERE `referee_id` = ? LIMIT 1', 'si', $this->result->referee_name, $this->id);
    }

    public static function getAllReferees($competitionId)
    {
        self::$resultList = App::$_DB->doQuery('SELECT * FROM `referee` WHERE `Competition_competition_id` = ? ORDER BY `referee_name` ASC', 'i', (int)$competitionId);
    }

    public static function add($competitionId, $name)
    {
        App::$_DB->doQuery('INSERT INTO `referee` (referee_name, Competition_competition_id) VALUES (?, ?)', 'si', $name, (int)$competitionId);
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
        $record = App::$_DB->doQuery('SELECT count(*) AS total FROM `referee` WHERE `referee_id` = ?', 'i', (int)$id);
        return (boolean)App::$_DB->getRecord($record)->total;
    }

}
?>
