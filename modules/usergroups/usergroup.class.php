<?php
if (!defined('VALID_ACCESS')) die();

/**
 * This class is the model for a usergroup.
 *
 * @package   pizzaproject
 * @author    MI3TIa
 * @copyright 09-12-2008
 * @version   0.1
 */
class UserGroup
{
    private $result = null;
    private $id = 0;

    // rights of an usergroup foreach component.
    private $rights = array();
    private $changeaccess = array();

    private static $resultList = null;

    /**
     * The constructor for an usergroup.
     *
     * @param int $id
     */
    public function __construct($id)
    {
        $this->id = App::$_DB->escapeString($id);
        $this->result = App::$_DB->doSQL('SELECT *
                                          FROM `usergroup`
                                          WHERE `group_id` = ' . $this->id . ' LIMIT 1;');
        $this->result = App::$_DB->getRecord($this->result);

        $this->getAllRights();
        $this->getAllChangeAccess();
    } // constructor

    /**
     * Destructs the usergroup-object.
     */
    public function __destruct()
    {
        App::$_DB->freeQuery($this->result);
    } //__destruct

    /**
     * Gets the id of the usergroup
     */
    public function getId()
    {
        return $this->id;
    } // getId

    /**
     * Selects all the rights from the database and add them into the rights array.
     */
    private function getAllRights()
    {
        $rightsResult = App::$_DB->doSQL('SELECT `Component_com_id`, `rights` FROM `rights` WHERE UserGroup_group_id = ' .$this->id);

        while($row = App::$_DB->getRecord($rightsResult))
          $this->rights[$row->Component_com_id] = $row->rights;
    } // getAllRights

    /**
     * Selects all the change access rights from the database and add them into the changeaccess array.
     */
    private function getAllChangeAccess()
    {
        $rightsResult = App::$_DB->doSQL('SELECT `Component_com_id`, `rightnochange` FROM `rights` WHERE UserGroup_group_id = ' .$this->id);

        while($row = App::$_DB->getRecord($rightsResult))
          $this->changeaccess[$row->Component_com_id] = $row->rightnochange;
    } // getAllRights

    /**
     * Gets the groupname.
     *
     * @return String groupname
     */
    public function getGroupName()
    {
        return $this->result->group_name;
    } // getGroupName

    /**
     * Sets the groupname of an usergroup.
     *
     * @param String $groupName
     */
    public function setGroupName($groupName)
    {
        $this->result->group_name = $groupName;
    } // setGroupName

    /**
     * Gets the rights of an usergroup on a component.
     *
     * @param int $component
     * @param int $option
     * @return boolean if the usergroup has right for a component return true.
     */
    public function getChangeAccess($component, $option)
    {
        if (!Component::hasDefaultChRights($component, $option))
          return false;

        foreach($this->changeaccess as $com => $right)
          if($component == $com)
            if(($right&$option) == $option)
              return false;
              
        return true;
    } // getChangeAccess

    /**
     * Gets the rights of an usergroup on a component.
     *
     * @param int $component
     * @param int $option
     * @return boolean if the usergroup has right for a component return true.
     */
    public function getRight($component, $option)
    {
        foreach($this->rights as $com => $right)
        {
            if($component == $com)
            {
                if(($right&$option) == $option)
                  return true;
            }
        }
        return false;
    } // getRight

    /**
     * Adds a right in de rights array for specified a component.
     *
     * @param int $component
     * @param int $option
     */
    public function addRight($component, $option)
    {
        @$this->rights[$component] |= $option;
    } // addRight

    /**
     * Deletes a right form the rights array for a specified component.
     *
     * @param int $component
     * @param int $option
     */
    public function delRight($component, $option)
    {
        $this->rights[$component] &= ~$option;
    } // delRight

    /**
     * Clears the right array.
     */
    public function clearRightArray($component)
    {
        $this->rights[$component] = 0;
    } // clearRightArray

    /**
     * Deletes the usergroup from the database.
     */
    public function delete()
    {
        //first check if there are any users present in the database
        if ($this->getMemberCount() > 0)
          throw new Exception(App::$_LANG->getValue('LANG_USERGROUP') . ' ' . 
                              App::$_LANG->getValue('ERROR_HASSTILL') . ' ' .
                              App::$_LANG->getValue('LANG_USERGROUP_MEMBERS'));

        App::$_DB->doSQL('DELETE FROM `rights`
                          WHERE `UserGroup_group_id` = ' . $this->id . '');
        
        App::$_DB->doSQL('DELETE FROM `usergroup` WHERE `group_id` = ' . $this->id . '');

        $this->__destruct();
    } // delete

    /**
     * Updates the user in the database.
     */
    public function save()
    {
        App::$_DB->doSQL('UPDATE `usergroup` SET
                          `group_name` = "'.App::$_DB->escapeString($this->result->group_name).'"
                          WHERE `group_id` = ' . $this->id . ' LIMIT 1;');

        foreach($this->rights as $com => $right)
        {
            if(!$this->rightExists($com, $this->id))
            {
                $this->newRight($com, $this->id, $right);
            }
            else
            {
                App::$_DB->doSQL('UPDATE `rights` SET
                                 `rights` = "'.App::$_DB->escapeString($right).'"
                                 WHERE `Component_com_id` = "'. $com .'" AND `UserGroup_group_id` = ' . $this->id . ' LIMIT 1');
            }
        }
    } // save

    /**
     * Fetches the readOnly parameter from the database
     *
     * @return boolean If the specified group is readOnly
     */
    public function isReadOnly()
    {
        return $this->result->group_readonly;
    } //isReadOnly

    /**
     * Gets how many members this usergroup has
     */
    public function getMemberCount()
    {
        $record = App::$_DB->doSQL('SELECT count( * ) AS total
                                    FROM `user`
                                    WHERE `UserGroup_group_id` = ' . $this->id);

        return (boolean)App::$_DB->getRecord($record)->total;
    } //getMemberCount

    /**
     * Checks if right in the database exists.
     *
     * @param int $componentId
     * @param int $userGroupIp
     */
    private static function rightExists($componentId, $userGroupId)
    {
        $record = App::$_DB->doSQL('SELECT count( * ) AS total
                                    FROM `rights`
                                    WHERE `Component_com_id` = ' . App::$_DB->escapeString($componentId) . '
                                    AND `UserGroup_group_id` = ' . App::$_DB->escapeString($userGroupId));

        return (boolean)App::$_DB->getRecord($record)->total;
    }
    /**
     * Gets all the usergroups form the database.
     */
    public static function getAllUserGroups()
    {
        self::$resultList = App::$_DB->doSQL('SELECT `usergroup`.*, COUNT(`user`.`UserGroup_group_id`) AS `member_count`
                                              FROM `usergroup` LEFT OUTER JOIN `user` ON `user`.`UserGroup_group_id` = `usergroup`.`group_id`
                                              GROUP BY `usergroup`.`group_id` ORDER BY `usergroup`.`group_id` ASC;');
    } //getAllUserGroups

    /**
     * Gets the next usergroup from the resultList.
     *
     * @return $record if there is an next user than return the next user.
     */
    public static function nextUserGroup()
    {
        if (self::$resultList == null)
          return null;

        $record = App::$_DB->getRecord(self::$resultList);
        if ($record == null)
          self::$resultList = null;

        return $record;
    } //nextUserGroup

    /**
     * Creates a new usergroup and adds it to the database.
     *
     * @param String $name
     * @param int $id
     */
    public static function add($name)
    {
       App::$_DB->doSQL('INSERT INTO `usergroup` (group_name)
                            VALUES ("'.App::$_DB->escapeString($name).'")');

       return App::$_DB->getLastId();
    } //add

    /**
     * Adds new rights to the database.
     *
     * @param int $componentId
     * @param int $userGroupId
     * @param int $rights
     */
    public static function newRight($componentId, $userGroupId, $rights)
    {
       App::$_DB->doSQL('INSERT INTO `rights` (Component_com_id, UserGroup_group_id, rights)
                            VALUES (
                                "'.App::$_DB->escapeString($componentId).'",
                                "'.App::$_DB->escapeString($userGroupId).'",
                                "'.App::$_DB->escapeString($rights).'"
                                )');
    } // newRight

    /**
     * Check if usergroup exists
     *
     * @param int $id the ID of the requested usergroup
     * @return boolean true or false if the usergroup exists / not exists
     */
    public static function exists($id)
    {
        if (!$id)
          return false;

        $record = App::$_DB->doSQL('SELECT count( * ) AS total
                                    FROM `usergroup`
                                    WHERE `group_id` = ' . App::$_DB->escapeString($id));
        
        return (boolean)App::$_DB->getRecord($record)->total;
    } //exists

    /**
     * Count the members in this group
     *
     * @param int the id of the usergroup
     */
    public static function getGroupMemberCount($id)
    {
        $record = App::$_DB->doSQL('SELECT COUNT(`UserGroup_group_id`) AS `member_count`
                                    FROM `user`
                                    WHERE `Usergroup_group_id` = '. $id . '
                                    AND `user_enabled` = 1
                                    GROUP BY `Usergroup_group_id`;');

        return (int)App::$_DB->getRecord($record)->member_count;
    } //getMemberCount

    /**
     * Count the disabled members in this group
     *
     * @param int the id of the usergroup
     */
    public static function getGroupEnabledCount($id)
    {
        $record = App::$_DB->doSQL('SELECT COUNT(`UserGroup_group_id`) AS `member_count`
                                    FROM `user`
                                    WHERE `Usergroup_group_id` = '. $id . ' AND `user_enabled` = 1
                                    GROUP BY `Usergroup_group_id`;');

        $r = App::$_DB->getRecord($record);
        if ($r == null)
          return 0;
        else
          return (int)$r->member_count;
    } //getMemberCount

} // UserGroups
?>
