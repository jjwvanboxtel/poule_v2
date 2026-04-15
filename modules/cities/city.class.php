<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class City
{
    private $result = null;
    private $id = 0;
    
    private static $resultList = null;
    
    public function __construct($id)
    {
        $this->id = (int)$id;
        $this->result = App::$_DB->doQuery('SELECT * FROM `city` WHERE `city_id` = ? LIMIT 1', 'i', $this->id);
        $this->result = App::$_DB->getRecord($this->result);
    }

    /**
     * Destructs the city-object.
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
        return $this->result->city_name;
    }
    
    public function setName($name)
    {
        $this->result->city_name = $name;
    }

    /**
     * Gets how many games this city has
     */
    public function getGameCount()
    {
        $record = App::$_DB->doQuery('SELECT count(*) AS total FROM `game` WHERE `City_city_id` = ?', 'i', $this->id);
        return App::$_DB->getRecord($record)->total;
    } //getGameCount

    public function delete()
    {
        //first check if there are any games present in the database
        if ($this->getGameCount() > 0)
          throw new Exception(App::$_LANG->getValue('LANG_CITY') . ' ' .
                              App::$_LANG->getValue('ERROR_HASSTILL') . ' ' .
                              App::$_LANG->getValue('LANG_CITY_GAMES'));

        App::$_DB->doQuery('DELETE FROM `city` WHERE `city_id` = ?', 'i', $this->id);

        $this->__destruct();
        return true;
    }

    public static function deleteAllByCompetition($competitionId)
    {
        App::$_DB->doQuery('DELETE FROM `city` WHERE `Competition_competition_id` = ?', 'i', (int)$competitionId);
    }
    
    public function save()
    {
        App::$_DB->doQuery('UPDATE `city` SET `city_name` = ? WHERE `city_id` = ? LIMIT 1', 'si', $this->result->city_name, $this->id);
    }

    public static function getAllCities($competitionId)
    {
        self::$resultList = App::$_DB->doQuery('SELECT * FROM `city` WHERE `Competition_competition_id` = ? ORDER BY `city_name` ASC', 'i', (int)$competitionId);
    }

    public static function add($name, $competition)
    {
        App::$_DB->doQuery('INSERT INTO `city` (city_name, Competition_competition_id) VALUES (?, ?)', 'si', $name, (int)$competition);
        return App::$_DB->getLastId();
    }

    public static function nextCity()
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
        $record = App::$_DB->doQuery('SELECT count(*) AS total FROM `city` WHERE `city_id` = ?', 'i', (int)$id);
        return (boolean)App::$_DB->getRecord($record)->total;
    }

}
?>
