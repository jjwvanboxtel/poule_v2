<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Poule
{
    private $result = null;
    private $id = 0;
    
    private static $resultList = null;
    
    public function __construct($id)
    {
        $this->id = (int)$id;
        $this->result = App::$_DB->doQuery('SELECT * FROM `poule` WHERE `poule_id` = ? LIMIT 1', 'i', $this->id);
        $this->result = App::$_DB->getRecord($this->result);
    }

    /**
     * Destructs the poule-object.
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
        return $this->result->poule_name;
    }
    
    public function setName($name)
    {
        $this->result->poule_name = $name;
    }

    /**
     * Gets how many games this poule has
     */
    public function getGameCount()
    {
        $record = App::$_DB->doQuery('SELECT count(*) AS total FROM `game` WHERE `Poule_poule_id` = ?', 'i', $this->id);
        return App::$_DB->getRecord($record)->total;
    } //getGameCount

    public function delete()
    {
        //first check if there are any games present in the database
        if ($this->getGameCount() > 0)
          throw new Exception(App::$_LANG->getValue('LANG_POULE') . ' ' .
                              App::$_LANG->getValue('ERROR_HASSTILL') . ' ' .
                              App::$_LANG->getValue('LANG_POULE_GAMES'));

        App::$_DB->doQuery('DELETE FROM `poule` WHERE `poule_id` = ?', 'i', $this->id);

        $this->__destruct();
        return true;
    }

    public static function deleteAllByCompetition($competitionId)
    {
        App::$_DB->doQuery('DELETE FROM `poule` WHERE `Competition_competition_id` = ?', 'i', (int)$competitionId);
    }
    
    public function save()
    {
        App::$_DB->doQuery('UPDATE `poule` SET `poule_name` = ? WHERE `poule_id` = ? LIMIT 1', 'si', $this->result->poule_name, $this->id);
    }

    public static function getAllPoules($competitionId)
    {
        self::$resultList = App::$_DB->doQuery('SELECT * FROM `poule` WHERE `Competition_competition_id` = ? ORDER BY `poule_name` ASC', 'i', (int)$competitionId);
    }

    public static function add($name, $competitionId)
    {
        App::$_DB->doQuery('INSERT INTO `poule` (poule_name, Competition_competition_id) VALUES (?, ?)', 'si', $name, (int)$competitionId);
        return App::$_DB->getLastId();
    }

    public static function nextPoule()
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
        $record = App::$_DB->doQuery('SELECT count(*) AS total FROM `poule` WHERE `poule_id` = ?', 'i', (int)$id);
        return (boolean)App::$_DB->getRecord($record)->total;
    }

}
?>
