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
        $this->id = App::$_DB->escapeString($id);
        $this->result = App::$_DB->doSQL('SELECT *
                                          FROM `city`
                                          WHERE `city_id` = ' . $this->id . ' LIMIT 1;');
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
        $record = App::$_DB->doSQL('SELECT count( * ) AS total
                                    FROM `game`
                                    WHERE `City_city_id` = ' . $this->id);

        return App::$_DB->getRecord($record)->total;
    } //getGameCount

    public function delete()
    {
        //first check if there are any games present in the database
        if ($this->getGameCount() > 0)
          throw new Exception(App::$_LANG->getValue('LANG_CITY') . ' ' .
                              App::$_LANG->getValue('ERROR_HASSTILL') . ' ' .
                              App::$_LANG->getValue('LANG_CITY_GAMES'));

        App::$_DB->doSQL('DELETE FROM `city` WHERE `city_id` = ' . $this->id . '');

        $this->__destruct();
        return true;
    }

    public static function deleteAllByCompetition($competitionId)
    {
        App::$_DB->doSQL('DELETE FROM `city`
                          WHERE `Competition_competition_id` = ' . $competitionId . '');
    }
    
    public function save()
    {
        App::$_DB->doSQL('UPDATE `city` SET
                          `city_name` = "'.App::$_DB->escapeString($this->result->city_name).'"
                          WHERE `city_id` = ' . $this->id . ' LIMIT 1;');
    }

    public static function getAllCities($competitionId)
    {
       self::$resultList = App::$_DB->doSQL('SELECT * FROM `city`
                                             WHERE `Competition_competition_id` = ' . $competitionId . ' 
                                             ORDER BY `city_name` ASC');
    }

    public static function add($name, $competition)
    {
        App::$_DB->doSQL('INSERT INTO `city` (city_name, Competition_competition_id)
                          VALUES (
                            "'.App::$_DB->escapeString($name).'",
                            '.$competition.')
                          ');
        
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
        $record = App::$_DB->doSQL('SELECT count( * ) AS total
                                    FROM `city`
                                    WHERE `city_id` = ' . App::$_DB->escapeString($id));

        return (boolean)App::$_DB->getRecord($record)->total;
    }

}
?>
