<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Section
{
    private $result = null;
    private $competitionList = array();
    private $id = 0;
    
    private static $resultList = null;
    
    public static $_SECTION_RESULTS = 1;
    public static $_SECTION_CARDS = 2;
    public static $_SECTION_KNOCK_OUT_FASE = 4;
    public static $_SECTION_QUESTIONS = 5;
    
    public function __construct($id)
    {
        $this->id = App::$_DB->escapeString($id);
        $this->result = App::$_DB->doSQL('SELECT *
                                          FROM `section`
                                          WHERE `section_id` = ' . $this->id . ' LIMIT 1;');
        $this->result = App::$_DB->getRecord($this->result);
        
        $resultList = App::$_DB->doSQL('SELECT * FROM `section_competition`
                  WHERE `Section_section_id` = ' . $this->id);
                          
        while (($competition = App::$_DB->getRecord($resultList)) != null)
        {
            $this->competitionList[$competition->Competition_competition_id]['enabled'] = $competition->Section_Competition_enabled;
        }
    }

    /**
     * Destructs the section-object.
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
        return $this->result->section_name;
    }
    
    public function setName($name)
    {
        $this->result->section_name = $name;
    }

    public function getEnabled($competitionId)
    {
        return $this->competitionList[$competitionId]['enabled'];
    }
    
    public function setEnabled($competitionId, $enabled)
    {
        $this->competitionList[$competitionId]['enabled'] = $enabled;
    }    

    public function save()
    {
        App::$_DB->doSQL('UPDATE `section` SET
                          `section_name` = "'.App::$_DB->escapeString($this->result->section_name).'"
                         WHERE `section_id` = ' . $this->id . ' LIMIT 1;');
        
        foreach ($this->competitionList as $competitionId => $section)
        {
            App::$_DB->doSQL('UPDATE `section_competition` SET
                                `Section_Competition_enabled` = "'.$section['enabled'].'"
                                WHERE `Section_section_id` = ' . $this->id . ' 
                                AND `Competition_competition_id` = ' . $competitionId . ' LIMIT 1;');
        }
    }
    
    public static function getAllSections($competitionId=0)
    {
        if ($competitionId != 0)
        {
            self::$resultList = App::$_DB->doSQL('SELECT `section`.*, `section_competition`.`Section_Competition_enabled`
                                                  FROM `section`
                                                  INNER JOIN `section_competition` ON `section`.`section_id` = `section_competition`.`Section_section_id`
                                                  WHERE `section_competition`.`Competition_competition_id` = '.$competitionId.'');
        }
        else
        {
            self::$resultList = App::$_DB->doSQL('SELECT *
                                                  FROM `section`');
        }
    }

    public static function nextSection()
    {
        if (self::$resultList == null)
          return null;

        $record = App::$_DB->getRecord(self::$resultList);
        if ($record == null)
          self::$resultList = null;

        return $record;
    }
   
    public static function deleteAllSectionCompetitionByCompetition($competitionId)
    {
        App::$_DB->doSQL('DELETE FROM `section_competition`
                          WHERE `Competition_competition_id` = ' . $competitionId . '');
    }
    
    public static function exists($id)
    {
        $record = App::$_DB->doSQL('SELECT count( * ) AS total
                                    FROM `section`
                                    WHERE `section_id` = ' . App::$_DB->escapeString($id));

        return (boolean)App::$_DB->getRecord($record)->total;
    }
    
    public static function addCompetition($sectionId, $competitionId)
    {
            App::$_DB->doSQL('INSERT INTO `section_competition` (Section_section_id, Competition_competition_id, Section_Competition_enabled)
                          VALUES (
                            '.$sectionId.',
                            '.$competitionId.',
                            0)
                          ');
    }
}
?>
