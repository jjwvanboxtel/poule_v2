<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Form
{
    private $result = null;
    private $id = 0;
    
    private static $image_dir = 'forms/';
    private static $resultList = null;
    
    public function __construct($id)
    {
        $this->id = App::$_DB->escapeString($id);
        $this->result = App::$_DB->doSQL('SELECT *
                                          FROM `form`
                                          WHERE `form_id` = ' . $this->id . ' LIMIT 1;');
        $this->result = App::$_DB->getRecord($this->result);
    }

    /**
     * Destructs the form-object.
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
        return $this->result->form_name;
    }

    public function getFile()
    {
        return $this->result->form_file;
    }
    
    public function setName($name)
    {
        $this->result->form_name = $name;
    }

    public function setFile($file)
    {
        $curFile = $this->result->form_file;

        $delete = App::$_UPL->deleteFile($curFile, $this->result->Competition_competition_id.'/'.self::$image_dir);
        App::$_UPL->loadUp($file, $this->result->Competition_competition_id.'/'.self::$image_dir);
        
        $this->result->form_file = $file['name'];

        return $delete;
    }
   
    public function delete()
    {
        App::$_DB->doSQL('DELETE FROM `form` WHERE `form_id` = ' . $this->id . '');

        $curFile = $this->result->form_file;

        $delete = App::$_UPL->deleteFile($curFile, $this->result->Competition_competition_id.'/'.self::$image_dir);

        $this->__destruct();
        return $delete;
    }

    public static function deleteAllByCompetition($competitionId)
    {
        App::$_DB->doSQL('DELETE FROM `form`
                          WHERE `Competition_competition_id` = ' . $competitionId . '');
    }
    
    public function save()
    {
        App::$_DB->doSQL('UPDATE `form` SET
                          `form_name` = "'.App::$_DB->escapeString($this->result->form_name).'",
                          `form_file` = "'.App::$_DB->escapeString($this->result->form_file).'"
                          WHERE `form_id` = ' . $this->id . ' LIMIT 1;');
    }

    public static function getAllForms($competitionId)
    {
       self::$resultList = App::$_DB->doSQL('SELECT * FROM `form`
                                             WHERE `Competition_competition_id` = '.$competitionId.'
                                             ORDER BY `form_name` ASC');
    }

    public static function add($name, $file, $competitionId)
    {
        App::$_UPL->loadUp($file, $competitionId.'/'.self::$image_dir);
        
        App::$_DB->doSQL('INSERT INTO `form` (form_name, form_file, Competition_competition_id)
                          VALUES (
                            "'.App::$_DB->escapeString($name).'",
                            "'.App::$_DB->escapeString($file['name']).'",
                            '.$competitionId.')
                          ');
    }

    public static function nextform()
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
                                    FROM `form`
                                    WHERE `form_id` = ' . App::$_DB->escapeString($id));

        return (boolean)App::$_DB->getRecord($record)->total;
    }

    public static function getFormDir($competitionId)
    {
        return $competitionId.'/'.self::$image_dir;
    }


}
?>
