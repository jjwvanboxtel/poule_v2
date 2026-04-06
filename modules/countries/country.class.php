<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Country
{
    private $result = null;
    private $id = 0;
    
    private static $image_dir = 'countries/';
    private static $resultList = null;
    
    public function __construct($id)
    {
        $this->id = App::$_DB->escapeString($id);
        $this->result = App::$_DB->doSQL('SELECT *
                                          FROM `country`
                                          WHERE `country_id` = ' . $this->id . ' LIMIT 1;');
        $this->result = App::$_DB->getRecord($this->result);
    }

    /**
     * Destructs the country-object.
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
        return $this->result->country_name;
    }

    public function getImage()
    {
        return $this->result->country_flag;
    }
    
    public function setName($name)
    {
        $this->result->country_name = $name;
    }

    public function setImage($file)
    {
        $curImage = $this->result->country_flag;

        $delete = App::$_UPL->deleteFile($curImage, $this->result->Competition_competition_id.'/'.self::$image_dir);
        $safe = App::$_UPL->loadUp($file, $this->result->Competition_competition_id.'/'.self::$image_dir);

        $this->result->country_flag = $safe;

        return $delete;
    }

    /**
     * Gets how many games this country has
     */
    public function getGameCount()
    {
        $record = App::$_DB->doSQL('SELECT count( * ) AS total
                                    FROM `game`
                                    WHERE `Country_country_id_home` = ' . $this->id . '
                                    OR `Country_country_id_away` = ' . $this->id);

        return App::$_DB->getRecord($record)->total;
    } //getGameCount

    /**
     * Gets how many predictions this country has
     */
    public function getPredictionCount()
    {
        $record = App::$_DB->doSQL('SELECT count( * ) AS total
                                    FROM `participant_round_prediction`
                                    WHERE `Country_country_id` = ' . $this->id);

        return App::$_DB->getRecord($record)->total;
    } //getRoundCount
    
    /**
     * Gets how many rounds this country has
     */
    public function getRoundCount()
    {
        $record = App::$_DB->doSQL('SELECT count( * ) AS total
                                    FROM `round_result`
                                    WHERE `Country_country_id` = ' . $this->id);

        return App::$_DB->getRecord($record)->total;
    } //getRoundCount

    
    public function delete()
    {
    //first check if there are any games present in the database
        if ($this->getGameCount() > 0
            || $this->getRoundCount() > 0
           // || $this->getPredictionCount() > 0
           )
          throw new Exception(App::$_LANG->getValue('LANG_COUNTRY') . ' ' .
                              App::$_LANG->getValue('ERROR_HASSTILL') . ' ' .
                              App::$_LANG->getValue('LANG_COUNTRY_GAMES'));

        App::$_DB->doSQL('DELETE FROM `country` WHERE `country_id` = ' . $this->id . '');

        $curImage = $this->result->country_flag;

        $delete = App::$_UPL->deleteFile($curImage, $this->result->Competition_competition_id.'/'.self::$image_dir);

        $this->__destruct();
        return $delete;
    }

    public static function deleteAllByCompetition($competitionId)
    {
        App::$_DB->doSQL('DELETE FROM `country`
                          WHERE `Competition_competition_id` = ' . $competitionId . '');
    }
    
    public function save()
    {
        App::$_DB->doSQL('UPDATE `country` SET
                          `country_name` = "'.App::$_DB->escapeString($this->result->country_name).'",
                          `country_flag` = "'.App::$_DB->escapeString($this->result->country_flag).'"
                          WHERE `country_id` = ' . $this->id . ' LIMIT 1;');
    }

    public static function getCountriesByName($pattern)
    {
       self::$resultList = App::$_DB->doSQL("SELECT * FROM `country`
                                             WHERE `country_name` LIKE '%".$pattern."%'
                                             ORDER BY `country_name` ASC");
    }

    public static function getAllCountries($competitionId)
    {
       self::$resultList = App::$_DB->doSQL('SELECT * FROM `country`
                                             WHERE `Competition_competition_id` = '.$competitionId.'
                                             ORDER BY `country_name` ASC');
    }
        
    public static function add($name, $file, $competitionId)
    {
        $safe = App::$_UPL->loadUp($file, $competitionId.'/'.self::$image_dir);
        
        App::$_DB->doSQL('INSERT INTO `country` (country_name, country_flag, Competition_competition_id)
                          VALUES (
                            "'.App::$_DB->escapeString($name).'",
                            "'.App::$_DB->escapeString($safe).'",
                            '.$competitionId.')
                          ');
                         
        return App::$_DB->getLastId();
    }

    public static function nextCountry()
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
                                    FROM `country`
                                    WHERE `country_id` = ' . App::$_DB->escapeString($id));

        return (boolean)App::$_DB->getRecord($record)->total;
    }

    public static function getCountryDir($competitionId)
    {
        return $competitionId.'/'.self::$image_dir;
    }


}
?>
