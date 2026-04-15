<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Player
{
    private $result = null;
    private $id = 0;
    
    private static $resultList = null;
    
    public function __construct($id)
    {
        $this->id = (int)$id;
        $this->result = App::$_DB->doQuery('SELECT * FROM `player` WHERE `player_id` = ? LIMIT 1', 'i', $this->id);
        $this->result = App::$_DB->getRecord($this->result);
    }

    /**
     * Destructs the player-object.
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
        return $this->result->player_name;
    }

    public function getCountry()
    {
        return new Country($this->result->Country_country_id);
    }
    
    public function setName($name)
    {
        $this->result->player_name = $name;
    }

    public function setCountry($country)
    {
        $this->result->Country_country_id = $country;    
    }
    
    /**
     * Gets how many games this player has
     */
    public function getGameCount()
    {
        $record = App::$_DB->doQuery('SELECT count(*) AS total FROM `game` WHERE `Player_player_id` = ?', 'i', $this->id);
        return App::$_DB->getRecord($record)->total;
    } //getGameCount

    public function delete()
    {
        App::$_DB->doQuery('DELETE FROM `player` WHERE `player_id` = ?', 'i', $this->id);
        $this->__destruct();
        return true;
    }

    public static function deleteAllByCompetition($competitionId)
    {
        App::$_DB->doQuery('DELETE FROM `player` WHERE `Competition_competition_id` = ?', 'i', (int)$competitionId);
    }
    
    public function save()
    {
        App::$_DB->doQuery('UPDATE `player` SET `player_name` = ?, `Country_country_id` = ? WHERE `player_id` = ? LIMIT 1', 'sii', $this->result->player_name, (int)$this->result->Country_country_id, $this->id);
    }

    public static function getAllPlayers($competitionId, $countryId=false)
    {
        if ($countryId) {
            self::$resultList = App::$_DB->doQuery(
                'SELECT `player`.`player_id`, `player`.`player_name`, `country`.`country_name` FROM `player` INNER JOIN `country` ON `player`.`Country_country_id` = `country`.`country_id` WHERE `player`.`Competition_competition_id` = ? AND `country`.`country_id` = ? ORDER BY `country`.`country_name`, `player`.`player_name`',
                'ii', (int)$competitionId, (int)$countryId
            );
        } else {
            self::$resultList = App::$_DB->doQuery(
                'SELECT `player`.`player_id`, `player`.`player_name`, `country`.`country_name` FROM `player` INNER JOIN `country` ON `player`.`Country_country_id` = `country`.`country_id` WHERE `player`.`Competition_competition_id` = ? ORDER BY `country`.`country_name`, `player`.`player_name`',
                'i', (int)$competitionId
            );
        }
    }

    public static function add($competitionId, $name, $countryId)
    {
        App::$_DB->doQuery('INSERT INTO `player` (player_name, Country_country_id, Competition_competition_id) VALUES (?, ?, ?)', 'sii', $name, (int)$countryId, (int)$competitionId);
        return App::$_DB->getLastId();
    }

    public static function nextPlayer()
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
        $record = App::$_DB->doQuery('SELECT count(*) AS total FROM `player` WHERE `player_id` = ?', 'i', (int)$id);
        return (boolean)App::$_DB->getRecord($record)->total;
    }

}
?>
