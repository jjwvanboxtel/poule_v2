<?php
if (!defined('VALID_ACCESS')) die();

/**
 * Is the class for a prototype component. All components extend this class.
 *
 * @package   pizzaproject
 * @author    MI3TIa
 * @copyright 02-12-2008
 * @version   0.1
 */
class Component
{
    private static $resultList = null;
    protected $componentId;

    public function __construct($id)
    {
        $this->componentId = $id;

        if(!$this->hasAccess(CRUD_READ))
          throw new Exception('{ERROR_ACCESSDENIED}');
    }

    protected function hasAccess($type)
    {
        $usergroup = UserControl::getCurrentUserGroup();

        if($usergroup->getRight($this->componentId, $type))
          return true;
        else
          return false;
    }
   
    /**
     * Gets all the components form the database.
     */
    public static function getAllComponents($parent=false)
    {
        $query = '';
        if (is_numeric($parent))
          $query = ' WHERE `com_menu_parent` = ' . (int)$parent;

        self::$resultList = App::$_DB->doSQL('SELECT *
                                             FROM `component` 
                                             '.$query.'
                                             ORDER BY `com_friendlyname` ASC;');
                                             
        return self::$resultList; 
    } //getAllComponents

    /**
     * Gets the next component from the resultList.
     *
     * @return $record if there is an next component than return the next component.
     */
    public static function nextComponent()
    {
        if (self::$resultList == null)
          return null;

        $record = App::$_DB->getRecord(self::$resultList);
        if ($record == null)
          self::$resultList = null;

        return $record;
    } //nextComponent



    public function __toString()
    {
        
    }

    public static function getComponentId($name)
    {
        $record = App::$_DB->doSQL('SELECT `com_id` FROM `component` WHERE `com_name` = "'.App::$_DB->escapeString($name).'"');
        $rec = App::$_DB->getRecord($record);

        return $rec->com_id;
    }

    public static function hasDefaultChRights($com_id, $option)
    {
        $record = App::$_DB->doSQL('SELECT `com_defchrights`
                                    FROM `component`
                                    WHERE `com_id` = ' . (int)$com_id . '
                                    LIMIT 1;');
        $rights = (int)App::$_DB->getRecord($record)->com_defchrights;
        if (($rights&$option) == $option)
          return false;

        return true;
    }

    public static function getDefaultRights($com_id, $option)
    {
        $record = App::$_DB->doSQL('SELECT `com_defrights`
                                    FROM `component`
                                    WHERE `com_id` = ' . (int)$com_id . '
                                    LIMIT 1;');
        
        $rights = (int)App::$_DB->getRecord($record)->com_defrights;
        if (($rights&$option) == $option)
          return true;

        return false;
    }

    public static function hasChilds($com_id)
    {
        $record = App::$_DB->doSQL('SELECT count( * ) AS total
                                    FROM `component`
                                    WHERE `com_menu_parent` = ' . (int)$com_id);
        
        return (boolean)App::$_DB->getRecord($record)->total;
    }
}


?>
